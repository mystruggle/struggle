<?php
namespace struggle;
defined('CORE_PATH') or die('Access Forbidden');

header('Content-type:text/html;charset=utf-8');
error_reporting(E_ALL | E_STRICT| E_NOTICE);
//error_reporting(0);
version_compare('5.3.0', PHP_VERSION, '<=') or die('PHP version require >= 5.3.0');
date_default_timezone_set('PRC');
//系统运行时间
define('BEGIN_TIME', microtime(true));


defined('APP_NAME') or define('APP_NAME', basename(dirname($_SERVER['SCRIPT_NAME'])));
defined('APP_ROOT') or define('APP_ROOT',rtrim(dirname($_SERVER['SCRIPT_FILENAME']),'/').'/');

defined('APP_PATH')      or define('APP_PATH','./');
defined('APP_CACHE')     or define('APP_CACHE', APP_ROOT.'Caches/');
defined('APP_RUNTIME')   or define('APP_RUNTIME', APP_ROOT.'Caches/Runtime/');
defined('APP_BACKEND')   or define('APP_BACKEND', APP_ROOT.'Admin/');
defined('APP_THEME')     or define('APP_THEME',APP_ROOT.'Themes/');
defined('APP_LIB')       or define('APP_LIB',APP_ROOT.'AddOnes/');
defined('APP_CONF')      or define('APP_CONF',APP_ROOT.'Config/');


defined('LIB_PATH')       or define('LIB_PATH',CORE_PATH.'Libraries/');
defined('CONF_PATH')      or define('CONF_PATH',CORE_PATH.'Config/');
defined('PUBLIC_PATH')    or define('PUBLIC_PATH',CORE_PATH.'Public/');


define('IS_WIN',PHP_OS == 'WINNT'?true:false);
defined('APP_DEBUG') or define('APP_DEBUG', false);

$sFuncFile = CONF_PATH.'functions.php';
if (file_exists($sFuncFile)){
    include_once $sFuncFile;
}else{
    sysHalt("{$sFuncFile}文件不存在");
}



//创建应用目录
buildAppDir();

//导入配置文件
importConf();

//导入核心文件
importCoreFile();

//开始运行
Sle::run();



















function buildAppDir(){
    $aDir = array(APP_ROOT,
                  APP_PATH,
                  APP_BACKEND,
                  APP_CACHE,
                  APP_CACHE.'Runtime/',
                  APP_CONF,
                  APP_LIB,
                  APP_THEME,
                  APP_THEME.'Default/'
            );
    foreach ($aDir as $sDir){
        if (!is_dir($sDir)){
            if(!@mkdir($sDir,0755,true)){
                sysHalt("没有权限创建目录{$sDir}");
            }
        }
    }
}

/**
 * 加载配置文件，导入配置值
 */
function importConf(){
    static $aConf = array();
    if (empty($aConf)){
        $sConfFile=CONF_PATH.'config.php';
        $sAppConfigFile = APP_CONF.'config.php';
        if (file_exists($sConfFile) && is_readable($sConfFile)){
            $aConf = include $sConfFile;
            if (file_exists($sAppConfigFile) && is_readable($sAppConfigFile)){
                $aConf = array_merge($aConf,include_once $sAppConfigFile);
            }else{
                trace("用户配置文件不存在或不可读，{$sAppConfigFile}", E_USER_ERROR);
            }
            //dump($aConf);//TODO  输出配置文件值
            foreach ($aConf as $sKey=>$sVal){
                C($sKey,$sVal);
            }
        }else{
            sysHalt("配置文件({$sConfFile})不存在或不可读，请检查");
        }
    }
}


/**
 * 加载语言文件
 */
function importLangConf(){
    static $aLang=array();
    if (empty($aLang)){
        //加载框架语言文件
        $sLangName=CONF_PATH.'zh_cn.php';
        
        $sLangName=C('LANG_NAME');
    }
}


/**
 * 导入核心文件
 */
function importCoreFile(){
	$aFile = array(
	    LIB_PATH.'Object.php',
	    LIB_PATH.'Debug.php',
	    LIB_PATH.'Exception.php',
	    LIB_PATH.'ErrorMange.php',
	    LIB_PATH.'Core/Controll.php',
	    LIB_PATH.'Core/View.php',
	    LIB_PATH.'Core/Dispatcher.php',
	);
	foreach ($aFile as $sFile){
	    trace("加载文件{$sFile}", E_USER_NOTICE);
	    require_cache($sFile);
	}
}



/*
 * 应用于本页面（即初始化工作部分）发生错误脚本终止；
 * 其他应调用Object类中halt
 */
function sysHalt($sMsg){
    trace($sMsg, E_USER_ERROR);
    die($sMsg);
}

/**
 * 关于错误的相关信息
 * @param integer $iCode 错误代码
 * @return mixed 返回关于该错误代码的相关信息；1错误、2警告、3提醒、0未知
 */

function getErrLevel($iCode){
    static $aRlt=array();
    if (empty($aRlt)){
        $aRlt=array(
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
    private $moDebug = null;
    private $moRoute = null;
    public  $aInfo   = array();
    const   SLE_ALL  = 1;
    const   SLE_SYS  = 2;
    const   SLE_APP  = 3;
    
    
    public static function getInstance(){
        if (!self::$maAttr){
            self::$maAttr = array_keys(get_class_vars(__CLASS__));
        }
        if (is_null(self::$moHandle))
            self::$moHandle = new Sle();
        return self::$moHandle;
    }
    
    public function __set($sName,$mVal){
        if (in_array($sName,self::$maAttr))
            self::$moHandle->$sName = $mVal;
    }
    
    public function __get($sName){
        if (in_array($sName,self::$maAttr))
            return self::$moHandle->$sName;
    }
    
    /**
     * 记录程序执行信息
     * @param string       $sInfo    信息内容
     * @param integer      $iType    信息类型，沿用php内置错误类型，如E_USER_ERROR
     * @param integer      $iLevel   信息等级，默认SLE_SYS
     * @param integer      $iRunTime 程序执行当前时间戳
     */
    public function hasInfo($sInfo,$iType,$iLevel = sle::SLE_SYS, $iRunTime = 0){
        //
    }

    
    public static function run(){
        //设置include路径
        setIncludePath();
        spl_autoload_register('\autoLoad');
        register_shutdown_function(array(new libraries\Exception,'shutdownHandle'));
        set_exception_handler(array(new libraries\Exception,'exceptionHandle'));
        set_error_handler(array(new libraries\Exception,'errorHandle'),E_ALL | E_STRICT);
        
        //生成路由
        self::$moHandle->moRoute = new libraries\Route($_SERVER['REQUEST_URI']);
        self::$moHandle->moRoute->exec();
        
        //显示页面调试信息
        self::$moHandle->moBug->show();
        //$oMonit=new libraries\Core\Dispatcher();

    }
    
    

    
 

    
}




function dump(){
    $aParam = func_get_args();
    foreach ($aParam as $mVal){
        var_dump($mVal);
        echo "<br>-------------".date('Y-m-d H:i:s')."--------------------<br />";
    }
}


function trace($sTraceInfo,$iCode){
    //$aArgs=func_get_args();
    static $aBackBug=array();
    $iNoteTime=microtime(true);
    if (APP_DEBUG){
        if (($sClassName = C('DEBUG_CLASS')) && ($sBugMethod = C('DEBUG_RECORD_METHOD'))){
            if(class_exists($sClassName) && is_null(Sle::getInstance()->moBug)){
                Sle::getInstance()->moBug = new $sClassName();
                if ($aBackBug){
                    foreach ($aBackBug as $aInfo)
                        Sle::getInstance()->moBug->$sBugMethod($aInfo[0], $aInfo[1], $aInfo[2],false);
                    $aBackBug = array();
                }
            }
        }
        if (is_null(Sle::getInstance()->moBug)){
            $aBackBug[] = array($sTraceInfo,$iCode,$iNoteTime);
        }else{
            Sle::getInstance()->moBug->$sBugMethod($sTraceInfo, $iCode,$iNoteTime,false);
        }
        sysNote($sTraceInfo, $iCode, $iNoteTime);
    }
}




/**
 * 系统日志记录函数
 * @param string $sMsg   错误信息
 * @param int    $iErrno 错误代码
 * @param float  $iNoteTime 系统执行到该代码所花费的时间戳，小数点后为微秒
 */
function sysNote($sMsg,$iErrno, $iNoteTime){
    $sLogBasePath='';
    $sLogPath='';
    $sLogName='';
    $sLogExt='';
    $sLogMaxSize='';
    $iMaxNum  = 5;//最大重命名个数
    if (function_exists('struggle\C')){
        C('LOG_PATH') && $sLogPath = C('LOG_PATH');
        C('LOG_NAME') && $sLogName = C('LOG_NAME');
        C('LOG_EXT') && $sLogExt = C('LOG_EXT');
        C('LOG_MAX_SIZE') && $sLogMaxSize = C('LOG_MAX_SIZE');
        C('LOG_BASE_PATH') && $sLogBasePath = C('LOG_BASE_PATH');
        
    }
    //dump($sLogExt ,$sLogMaxSize ,$sLogName ,$sLogPath ,$sLogBasePath);  TODO 调试
    //echo "<br>||||||||||||<br>";
    if (!$sLogExt || !$sLogMaxSize || !$sLogName || !$sLogPath || !$sLogBasePath){
        $sLogBasePath = APP_CACHE;
        $sLogPath = 'Runtime/';
        $sLogName = 'sys';
        $sLogExt  = 'log';
        $sLogMaxSize  = 2000;  //kb
    }
    $sLogFilePath=$sLogBasePath.$sLogPath;
    if (!is_dir($sLogFilePath)){
        if (!mkdir($sLogFilePath, 755,true)){
            die("目录({$sLogFilePath})创建失败，请检查权限");
        }
    }
    $sLogFile=$sLogFilePath.$sLogName.'.'.$sLogExt;
    if (is_file($sLogFile) && (filesize($sLogFile))/1024 >= $sLogMaxSize){
        for($i=$iMaxNum; $i>0 ;$i--){
            $sCurReFile=$sLogFile.".{$i}";
            if (is_file($sCurReFile)){
                if ($i == $iMaxNum){
                    @unlink($sCurReFile);
                }else {
                    if(!rename($sCurReFile, $sLogFile.'.'.($i+1)))
                        die("无法重命名文件,{$sCurReFile}");
                }
            }
        }
        if(is_file($sLogFile) && !rename($sLogFile, $sLogFile.'.1'))
            die("无法重命名文件,{$sLogFile}");        
    }
    if (!($hdFile=fopen($sLogFile,'ab+')))
        die("文件({$sLogFile})无法创建，请检查权限");
    $aErrInfo = getErrLevel($iErrno);
    $sMsg =date('Y-m-d H:i:s',$iNoteTime)."/".($iNoteTime-BEGIN_TIME)." [SYSTEM {$aErrInfo[1]} {$iErrno}] {$sMsg}".PHP_EOL;

    flock($hdFile, LOCK_EX);
    fwrite($hdFile, $sMsg);
    flock($hdFile, LOCK_UN);
    //static与fclose不能同时使用
    fclose($hdFile);
}



function load_config($sName){
    static $aConfig = array();
    $sFileName = $sName.'.php';
    if (!isset($aConfig[$sName])){
        $aConfig[$sName] = include CONF_PATH.$sFileName;
        if (file_exists(APP_CONF.$sFileName)){
            $aConfig[$sName] = array_merge($aConfig[$sName], include APP_CONF.$sFileName);
        }
    }
    foreach ($aConfig[$sName] as $sKey => $mVal){
        $sName == 'config' && C($sKey,$mVal);
        $sName == 'zh-cn'  && L($sKey,$mVal);
    }
}

/**
 * 读入配置函数
 * @param string $sName  配置名称
 * @param mix    $mVal    配置值
 */
function C($sName, $mVal = null){
    static $aConfig=array();
    if (is_string($sName)){
        if (!strpos($sName, '.')){
            $sName = strtolower($sName);
            if (is_null($mVal)) //不能用empty,否则不能把值设为空
                return isset($aConfig[$sName])?$aConfig[$sName]:null;
            $aConfig[$sName] = $mVal;
        }
    }
}



function L($sName, $mVal = null){
    static $aLang=array();
    if (is_string($sName)){
        if (!strpos($sName, '.')){
            $sName = strtolower($sName);
            if (is_null($mVal)) //不能用empty,否则不能把值设为空
                return isset($aLang[$sName])?$aLang[$sName]:null;
            $aLang[$sName] = $mVal;
        }
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



