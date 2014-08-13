<?php
namespace struggle;

defined('SLE_PATH') or die('Access Forbidden');

header('Content-type:text/html;charset=utf-8');
error_reporting(E_ALL | E_STRICT| E_NOTICE);
//error_reporting(0);
version_compare('5.3.0', PHP_VERSION, '<=') or die('PHP version require >= 5.3.0');
date_default_timezone_set('PRC');
//系统运行时间
define('BEGIN_TIME', microtime(true));

//如果是后台必须设置该常量SLE_FRONTEND
$sFrontend = '';
if (defined('SLE_FRONTEND')){
    $sFrontend = SLE_FRONTEND;
}

defined('APP_NAME') or define('APP_NAME', basename(dirname($_SERVER['SCRIPT_NAME'])));
defined('APP_ROOT') or define('APP_ROOT',rtrim(dirname($_SERVER['SCRIPT_FILENAME']),'/').'/');



defined('APP_PATH')      or define('APP_PATH','./');
defined('APP_CACHE')     or define('APP_CACHE', $sFrontend.'Caches/');
defined('APP_RUNTIME')   or define('APP_RUNTIME', $sFrontend.'Caches/Runtime/');
defined('APP_CONTROLLER') or define('APP_CONTROLLER', 'Controller/');
defined('APP_MODEL')     or define('APP_MODEL', $sFrontend.'Model/');
defined('APP_THEME')     or define('APP_THEME','Themes/');
defined('APP_PUBLIC')    or define('APP_PUBLIC',$sFrontend.'Public/');
defined('APP_LIB')       or define('APP_LIB','AddOnes/');
defined('APP_CONF')      or define('APP_CONF','Config/');


defined('LIB_PATH')       or define('LIB_PATH',SLE_PATH.'Libraries/');
defined('CONF_PATH')      or define('CONF_PATH',SLE_PATH.'Config/');
defined('PUBLIC_PATH')    or define('PUBLIC_PATH',SLE_PATH.'Public/');



define('IS_WIN',PHP_OS == 'WINNT'?true:false);
/*
 * APP_DEBUG有三种值，
 *   rescue  打开debug,debug信息打印到浏览器(用于由于错误打断无法写日志的情况)
 *   true    打开debug,debug信息由Debug类接管
 *   false   关闭debug
 */
defined('APP_DEBUG') or define('APP_DEBUG', false); 




/**
 * 关于错误的相关信息
 * @param integer $iCode 错误代码
 * @return mixed 返回关于该错误代码的相关信息；1错误、2警告、3提醒、0未知
 */

function getErrLevel($iCode){
    static $aRlt=array();
    if (empty($aRlt)){
        $aRlt=array(
        //第一个元素自定义错误等级1错误、2警告、3通知或其他;第二字符标示;第三该常量对应的值
            E_ERROR         => array(1,'E_ERROR',1),
            E_WARNING       => array(2,'E_WARNING',2),                //'运行时警告，非致命错误'
            E_PARSE         => array(1,'E_PARSE',4),                 //'编译时解析错误',
            E_NOTICE        => array(3,'E_NOTICE',8),                //'运行时通知',
            E_CORE_ERROR    => array(1,'E_CORE_ERROR',16),           //'致命错误，php核心触发',  
            E_CORE_WARNING  => array(2,'E_CORE_WARNING',32),         //'警告，php核心触发', 
            E_COMPILE_ERROR => array(1,'E_COMPILE_ERROR',64),        //'致命编译时错误，zend脚本引擎触发',
            E_COMPILE_WARNING =>array(2,'E_COMPILE_WARNING',128), 	 	 
            E_USER_ERROR    => array(1,'E_USER_ERROR',256), 	 	 
     	    E_USER_WARNING  => array(2,'E_USER_WARNING',512), 	 	 
     	    E_USER_NOTICE   => array(3,'E_USER_NOTICE',1024), 	 	 
     	    E_STRICT        => array(3,'E_STRICT',2048), 	
     	    E_RECOVERABLE_ERROR => array(1,'E_RECOVERABLE_ERROR',4096), 	
     	    E_DEPRECATED    => array(0,'E_DEPRECATED',8192), 	
     	    E_USER_DEPRECATED => array(0,'E_USER_DEPRECATED',16384),
     	    E_ALL           => array(0,'E_ALL',32767),
       );
    }
   return isset($aRlt[$iCode])?$aRlt[$iCode]:false;
}









class Sle{
    private static $moHandle = null;//isset判断null返回false
    private static $maAttr = array();
    private $maInfo  = array();
    private $mLastError = array();
    private $mDebug     = '';
    private $mLog       = '';
    private $mRoute     = '';
    const   SLE_NONE = 0;
    const   SLE_ALL  = 1;
    const   SLE_SYS  = 2;
    const   SLE_APP  = 3;
    
    
    public static function getInstance(){
        if (is_null(self::$moHandle)){
            self::$moHandle = new \struggle\Sle();
        }
        return self::$moHandle;
    }
    
    /*
    public function __set($sName,$mVal){
        if (in_array($sName,self::$maAttr))
            self::$moHandle->$sName = $mVal;
    }
    */
    
    public function __get($sName){
        $sAttrName = "m{$sName}";
        if (isset($this->$sAttrName)){
            if ($this->$sAttrName){
                return $this->$sAttrName;
            }elseif (method_exists($this, $sName)){
                return $this->$sName();
            }
        }else{
            $this->hasInfo("访问一个不存在的属性{$sName}", E_USER_ERROR, Sle::SLE_SYS);
        }
        return false;
    }
    
    private function Route(){
        static $oRoute = null;
        if(is_null($oRoute)){
            $this->hasInfo("初始化类".__FUNCTION__, E_USER_NOTICE, Sle::SLE_SYS);
            $this->Route = $oRoute = new libraries\Route($_SERVER['REQUEST_URI']);
        }
        return $this->Route;
    }

    private function Debug(){
        static $oDebug = null;
        if(is_null($oDebug)){
            $this->hasInfo("初始化类".__FUNCTION__, E_USER_NOTICE, Sle::SLE_SYS);
            $this->Debug = $oDebug = new libraries\Debug();
            foreach ($this->maInfo as $info){
                $oDebug->save($info[0],$info[1],$info[2],$info[3]);
            }
        }
        return $this->Debug;
    }
    
    private function Log(){
        static $oLog = null;
        if(is_null($oLog)){
            $this->hasInfo("初始化类".__FUNCTION__, E_USER_NOTICE, Sle::SLE_SYS);
            $this->Log = $oLog = new libraries\Log();
        }
        return $this->Log;
    }
    
    /**
     * 记录程序执行信息
     * @param string       $sInfo    信息内容
     * @param integer      $iType    错误类型，沿用php内置错误类型，如E_USER_ERROR
     * @param integer      $iFrom    信息类型，默认SLE_SYS,说明是系统日志还是用户日志
     * @param integer      $iRunTime 程序执行当前时间戳
     */
    public function hasInfo($sInfo,$iType,$iFrom = Sle::SLE_APP, $iRunTime = 0){
        if (APP_DEBUG){
            empty($iRunTime) && $iRunTime = microtime(true);
            $aInfo = array($sInfo ,$iType, $iFrom, $iRunTime);
            $this->maInfo[] = $aInfo;
            if ($iType == E_USER_ERROR)
                $this->mLastError = $aInfo;
            //救援模式
            if (strtolower(APP_DEBUG) == 'rescue'){
                $aErr = getErrLevel($iType);
                $shtml = "";
                if (is_object($sInfo)){
                    $sInfo = var_export($sInfo,true);
                }elseif (is_array($sInfo)){
                    $sInfo = print_r($sInfo,true);
                }
                $sInfo = "=> {$sInfo}";
                if ($aErr[0] == 1){
                    $shtml = "<p style='color:red;font-size:13px;'>{$sInfo}</p>";
                }elseif ($aErr[0] == 2){
                    $shtml = "<p style='color:blue;font-size:13px;'>{$sInfo}</p>";
                }else {
                    $shtml = "<p style='color:#009900;font-size:13px;'>{$sInfo}</p>";
                }
                count($this->maInfo) == 1 && $shtml = "<div style='border:1px solid #cccccc;padding:5px;width:auto;'>{$shtml}";
                echo "{$shtml}";
            }
        }
    }
    

    
    public function run(){
        static $bInit = false;
        if(!$bInit) {
            //加载核心函数文件
            $sFuncFile = CONF_PATH.'Functions.php';
            if (IS_WIN){
                if (file_exists($sFuncFile) && basename($sFuncFile) == basename(realpath($sFuncFile)) && is_readable($sFuncFile)){
                    $this->hasInfo("加载核心函数文件{$sFuncFile}", E_USER_NOTICE, Sle::SLE_SYS);
                    require_once $sFuncFile;
                }else{
                    $this->hasInfo("文件不存在或该文件不可读{$sFuncFile}，请检查！",E_USER_ERROR, Sle::SLE_SYS);
                }
            }else{
                if (file_exists($sFuncFile) && is_readable($sFuncFile)){
                    $this->hasInfo("加载核心函数文件{$sFuncFile}", E_USER_NOTICE, Sle::SLE_SYS);
                    require_once $sFuncFile;
                }else{
                    $this->hasInfo("文件不存在或该文件不可读{$sFuncFile}，请检查！",E_USER_ERROR, Sle::SLE_SYS);
                }
            }
            
            //建立目录
            $aBuildAppDir = array(APP_ROOT, APP_CACHE, APP_RUNTIME, APP_CONTROLLER, APP_MODEL, APP_CONF, APP_LIB, APP_THEME, APP_PUBLIC,APP_PUBLIC.'Default/',APP_PUBLIC.'Default/html/',APP_PUBLIC.'Default/js/',APP_PUBLIC.'Default/css/',APP_PUBLIC.'Default/images/', APP_THEME.'Default/');
            foreach ($aBuildAppDir as $sDir){
                if (!is_dir($sDir)){
                    if (buildDir($sDir)){
                        $this->hasInfo("建立目录{$sDir}", E_USER_NOTICE, Sle::SLE_SYS);
                    }else{
                        $this->hasInfo("建立目录{$sDir}不成功",E_USER_ERROR, Sle::SLE_SYS);
                    }
                }
            }
            
            //加载配置文件
            $sConfFile = CONF_PATH.'Config.php';
            $aConfig = array();
            if (file_exists($sConfFile) && basename($sConfFile) == basename(realpath($sConfFile))){
                if (is_readable($sConfFile)){
                    $this->hasInfo("加载核心配置文件{$sConfFile}",E_USER_NOTICE, Sle::SLE_SYS);
                    $aConfig = include $sConfFile;
                }else{
                    $this->hasInfo("文件不可读{$sConfFile}",E_USER_ERROR, Sle::SLE_SYS);
                }
                
                
            }else {
                $this->hasInfo("文件不存在{$sConfFile},区分大小写",E_USER_ERROR, Sle::SLE_SYS);
            }
            
            $sAppConfFile = APP_CONF.'Config.php';
            if (file_exists($sAppConfFile) && basename($sAppConfFile) == basename(realpath($sAppConfFile)) ){
                if (is_readable($sAppConfFile)){
                        $this->hasInfo("加载项目配置文件{$sAppConfFile}",E_USER_NOTICE, Sle::SLE_SYS);
                        $aConfig = array_merge($aConfig,include $sAppConfFile);
                }else{
                    $this->hasInfo("文件不可读{$sAppConfFile}",E_USER_ERROR, Sle::SLE_SYS);
                }
            }else{
                $sAppConfDir = dirname($sAppConfFile);
                if (is_writeable($sAppConfDir)){
                    $hdFile = fopen($sAppConfFile, 'wb+');
                    fwrite($hdFile, "<?php\r\n//项目配置文件\r\nreturn array(\r\n);");
                    fclose($hdFile);
                    $this->hasInfo("自动创建用户项目配置文件{$sAppConfFile}",E_USER_NOTICE, Sle::SLE_SYS);
                }else{
                    $this->hasInfo("当前目录不可写{$sAppConfDir}，请检查权限",E_USER_ERROR, Sle::SLE_SYS);
                }
            }
            if (!$this->mLastError && is_array($aConfig) && $aConfig){
                $this->hasInfo("所有配置参数值".print_r($aConfig,true),E_USER_NOTICE, Sle::SLE_SYS);
                foreach ($aConfig as $sKey=>$mVal){
                    C($sKey,$mVal);
                }
            }
            
            
            //加载语言配置文件
            $sLangFile = CONF_PATH.'zh-cn.php';
            $aLang = array();
            if (file_exists($sLangFile) && basename($sLangFile) == basename(realpath($sLangFile)) && is_readable($sLangFile)){
                    $this->hasInfo("语言配置文件{$sLangFile}处理",E_USER_NOTICE, Sle::SLE_SYS);
                    $aLang = include_once $sLangFile;
            }else{
                $this->hasInfo("语言文件不存在{$sLangFile},文件名区分大小写",E_USER_ERROR, Sle::SLE_SYS);
            }
            if (!$this->mLastError){
                $sAppLangName = C('LANG_NAME');
                $sAppLangFile = APP_CONF.$sAppLangName.'.php';
                if (file_exists($sAppLangFile) && basename($sAppLangFile) == basename(realpath($sAppLangFile)) && is_readable($sAppLangFile)){
                    $this->hasInfo("用户语言配置文件{$sAppLangFile}处理",E_USER_NOTICE, Sle::SLE_SYS);
                    $aLang = array_merge($aLang,include_once $sAppLangFile);
                }else{
                    $this->hasInfo("语言文件不存在{$sAppLangFile},文件名区分大小写",E_USER_WARNING, Sle::SLE_SYS);
                }
            }
            
            if (!empty($aLang) && !$this->mLastError){
                foreach ($aLang as $key=>$val){
                    L($key, $val);
                }
            }
            
            
            
            //加载核心文件
            if (!$this->mLastError){
                $aCoreFile = array(
                    LIB_PATH.'Object.php',
                    // LIB_PATH.'Debug.php',
                    LIB_PATH.'Exception.php',
                    // LIB_PATH.'Log.php',
                    LIB_PATH.'Core/Route.php',
                    LIB_PATH.'Core/Controller.php',
                    LIB_PATH.'Core/Model.php',
                    LIB_PATH.'Db/Db.php',
                    LIB_PATH.'Core/View.php',
                );
                foreach ($aCoreFile as $sFile){
                    if (require_cache($sFile)){
                        $this->hasInfo("加载核心文件{$sFile}", E_USER_NOTICE, Sle::SLE_SYS);
                    }else{
                        $this->hasInfo("文件不存在或不可读{$sFile},请检查文件", E_USER_ERROR, Sle::SLE_SYS);
                    }
                }
            }
            
            //设置自动包含路径
            if (!$this->mLastError){
                $sDir = C('AUTOLOAD_DIR');
                $sPath = '';
                if (strpos($sDir, ',') !== false){
                    $aDir = explode(',', $sDir);
                    foreach ($aDir as $sDir){
                        $sDir = APP_ROOT.$sDir;
                        if (is_dir($sDir)){
                            $sPath .= $sDir.PATH_SEPARATOR;
                        }else{
                            $this->hasInfo("{$sDir}不是目录，请检查",E_USER_ERROR, Sle::SLE_SYS);
                        }
                    }
                }else{
                    $sDir = APP_ROOT.$sDir;
                    if (is_dir($sDir)){
                        $sPath .= $sDir.PATH_SEPARATOR;
                    }else{
                        $this->hasInfo("{$sDir}不是目录，请检查",E_USER_ERROR, Sle::SLE_SYS);
                    }
                }
            }

            //分开写，以判断是否进行自动包含
            if (!$this->mLastError){
                //$sPath .= get_include_path();
                if (set_include_path($sPath)){
                    $this->hasInfo("设置{$sPath}自动包含目录",E_USER_NOTICE, Sle::SLE_SYS);
                }else{
                    $this->hasInfo("设置{$sPath}自动包含目录失败",E_USER_ERROR, Sle::SLE_SYS);
                }
            }else{
                $this->hasInfo("由于程序错误，设置自动包含目录失败",E_USER_ERROR, Sle::SLE_SYS);
            }
            
            
            //自定义自动包含句柄
            if (!$this->mLastError){
                $sFuncName = '\struggle\autoLoad';
                if (spl_autoload_register($sFuncName)){
                    $this->hasInfo("自定义自动包含处理函数{$sFuncName}",E_USER_NOTICE, Sle::SLE_SYS);
                }else{
                    $this->hasInfo("自定义自动包含处理函数{$sFuncName}失败",E_USER_ERROR, Sle::SLE_SYS);
                }
            }
            
            //分开写，以判断是否进行自动包含
            if(!$this->mLastError){
                //自定义句柄
                $sClassName = '\struggle\libraries\Exception';
                $oException = new $sClassName();
                //自定义脚本停止执行前执行的函数
                $sFuncName = 'shutdownHandle';
                $this->hasInfo("自定义shutdown处理句柄{$sClassName}::{$sFuncName}",E_USER_NOTICE, Sle::SLE_SYS);
                register_shutdown_function(array($oException,$sFuncName));
                
                //自定义异常处理句柄
                $sFuncName = 'exceptionHandle';
                $this->hasInfo("自定义异常处理句柄{$sClassName}::{$sFuncName}",E_USER_NOTICE, Sle::SLE_SYS);
                set_exception_handler(array($oException,$sFuncName));
                
                //自定义错误处理句柄
                $sFuncName = 'errorHandle';
                $this->hasInfo("自定义错误处理句柄{$sClassName}::{$sFuncName}",E_USER_NOTICE, Sle::SLE_SYS);
                set_error_handler(array($oException,$sFuncName),E_ALL | E_STRICT);
            }

        }
        //实例化类
        if (!self::$moHandle->mLastError){
            //执行路由
            $this->Route->exec();
        }

    }
    
        
 

    
}

//系统开始运行
Sle::getInstance()->run();
if (APP_DEBUG && APP_DEBUG !== 'rescue')
    Sle::getInstance()->Debug->show();



function dump(){
    $aParam = func_get_args();
    foreach ($aParam as $mVal){
        var_dump($mVal);
        echo "<br>-------------".date('Y-m-d H:i:s')."--------------------<br />";
    }
}





/*

function errorLevelName($sName){
    static $aLevelMap = array(
        E_ERROR         => '致命运行时错误',                           // 1
        E_WARNING       => '运行时警告，非致命错误',                   // 2
        E_PARSE         => '编译时解析错误',                          // 4
        E_NOTICE        => '运行时通知',                              // 8
        E_CORE_ERROR    => '致命错误，php核心触发',                   // 16 
        E_CORE_WARNING  => '警告，php核心触发',                      // 32 
        E_COMPILE_ERROR => '致命编译时错误，zend脚本引擎触发',          // 64
        128 	E_COMPILE_WARNING (integer) 	 	 
256 	E_USER_ERROR (integer) 	 	 
512 	E_USER_WARNING (integer) 	 	 
1024 	E_USER_NOTICE (integer) 	 	 
2048 	E_STRICT (integer) 	
4096 	E_RECOVERABLE_ERROR (integer) 	
8192 	E_DEPRECATED (integer) 	
16384 	E_USER_DEPRECATED (integer)
32767 	E_ALL (integer) 
    );
}
*/




