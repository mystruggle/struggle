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


//加载定义常量文件
require_once SLE_PATH.'Libraries/Define.inc.php';

//加载全局核心函数
require_once SLE_PATH.'Config/Sle.func.php';

//加载Object类文件
require_cache('');

//部署项目目录
$aBuildAppDir = array(APP_ROOT, APP_CACHE, APP_RUNTIME, APP_CONTROLLER,
                      APP_MODEL, APP_CONF, APP_LIB, APP_THEME, APP_PUBLIC,
                      APP_PUBLIC.'Default/',APP_PUBLIC.'Default/html/',
                      APP_PUBLIC.'Default/js/',APP_PUBLIC.'Default/css/',
                      APP_PUBLIC.'Default/images/', APP_THEME.'Default/');
buildAppDir($aBuildAppDir);




//加载处理配置文件
$sConfFile = CONF_PATH.'Config.php';
try {
    if(!setConfig($sConfFile)){
        throw new \Exception("文件不存在或不可读，文件名区分大小{$sConfFile}");
    }
}catch (\Exception $e){
    halt("异常错误: {$e->getMessage()}  {$e->getFile()} 第{$e->getLine()}行");
}

//项目配置文件
$sAppConfFile = APP_CONF.'Config.php';
try {
    if(!setConfig($sAppConfFile,true)){
        throw new \Exception("文件不存在或不可读，文件名区分大小，检查目录是否可写{$sAppConfFile}");
    }
}catch (\Exception $e){
    halt("异常错误: {$e->getMessage()}  {$e->getFile()} 第{$e->getLine()}行");
}




//加载语言配置文件
$sLangFile = CONF_PATH.'zh-cn.php';
try {
    if(!setLangConfig($sLangFile)){
        throw new \Exception("文件不存在或不可读，文件名区分大小{$sLangFile}");
    }
}catch (\Exception $e){
    halt("异常错误: {$e->getMessage()}  {$e->getFile()} 第{$e->getLine()}行");
}


//加载项目语言文件
$sAppLangName = C('LANG_NAME');
$sAppLangFile = APP_CONF.$sAppLangName.'.php';
try {
    if(!setLangConfig($sAppLangFile,true)){
        throw new \Exception("文件不存在或不可读，文件名区分大小，检查目录是否可写{$sAppLangFile}");
    }
}catch (\Exception $e){
    halt("异常错误: {$e->getMessage()}  {$e->getFile()} 第{$e->getLine()}行");
}

//加载全局基类文件
require_cache(LIB_PATH.'Object.php');

//加载调试类文件
require_once SLE_PATH.'Libraries/Debug.php';


define('IS_WIN',PHP_OS == 'WINNT'?true:false);
/*
 * APP_DEBUG有三种值，
 *   rescue  打开debug,debug信息打印到浏览器(用于由于错误打断无法写日志的情况)
 *   true    打开debug,debug信息由Debug类接管
 *   false   关闭debug
 */
defined('APP_DEBUG') or define('APP_DEBUG', false); 





class Sle{
    private static $moHandle = null;//isset判断null返回false
    private static $maAttr = array();
    private $maInfo  = array();
    private $mLastError = array();
    private $mDebug     = '';
    private $mLog       = '';
    private $mRoute     = '';
    private $mController = '';
    private $mClient = null;
    private $mView   = null;
	/* 把注册类存放于该数组 */
	private $mRegClass = array();
    
    
    public static function app(){
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
    
    public function __get($name){
        if (isset($this->mRegClass[$name])){
            if (is_string($this->mRegClass[$name])){
                $sClassName = $this->mRegClass[$name];
                $this->mRegClass[$name] = new $sClassName;
            }
            return $this->mRegClass[$name];
        }else{
            //debug_print_backtrace();
            try {
                throw new \Exception("访问一个不存在的属性{$name}");
            }catch (\Exception $e){
                halt("异常错误: {$e->getMessage()}  {$e->getFile()} 第{$e->getLine()}行");
            }
        }
    }


    /**
	 * 在该全局类注册一个类，方便调用
	 * @param string $file    类文件或类名
	 * @param string $ident  类的身份标识，唯一。用于调用，为空时默认用文件名代替
	 * @example
	 *    如，类文件example.class.php,ident 为空是则ident等于example,调用该类方法为Sle::app()->example
	 */
	public function registerClass($file,$ident = ''){
		//把该文件注册到全局类Sle
		if (!$ident){
    		$sIdent = fetchClassName($file,false);
    		$ident = strtolower($sIdent[0]).substr($sIdent, 1);
		}
		$sClassName = '\\'.fetchNamespace($file).'\\'.fetchClassName($file);
		try{
		    if (!class_exists($sClassName)){
		        if (!isFile($file))throw new \Exception("文件不存在或不可读，文件名区分大小写\t{$file}");
		        require_cache($file);
		    }
			if(!class_exists($sClassName))throw new \Exception("该类{$sClassName}在类文件{$file}不存在\t");
		}catch(\Exception $e){
			 halt("异常错误: {$e->getMessage()}  {$e->getFile()} 第{$e->getLine()}行");
		}
	   $this->mRegClass[$ident] = $sClassName;
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
    
    private  function Client(){
        static $oClient = null;
        if (is_null($oClient)){
            $this->hasInfo("初始化类".__FUNCTION__, E_USER_NOTICE, Sle::SLE_SYS);
            $this->Client = $oClient = new libraries\Client();
        }
        return $this->Client;
    }
    
    private function View(){
        static $oView = null;
        if (is_null($oView)){
            $this->hasInfo("初始化类".__FUNCTION__, E_USER_NOTICE, Sle::SLE_SYS);
            $this->View = $oView = new libraries\Core\View();
        }
        return $this->View;
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
		//加载核心文件
		$aCoreFile = array(
			//LIB_PATH.'Object.php',
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
		    if (!isFile($sFile)){
		        halt("文件不存在或不可读{$sFile}\t".__FILE__."\tline\t".__LINE__);
		    }
		    if (strpos($sFile, 'Exception') ===false && strpos($sFile, 'Db') ===false)
		        $this->registerClass($sFile);
		    else 
		        require_cache($sFile);
		    Debug::trace('加载核心文件'.$sFile,Debug::SYS_NOTICE);
		}
		print_r(Sle::app()->route);
		Debug::show();
		die;
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
        //实例化类
        if (!self::$moHandle->mLastError){
            //执行路由
            $this->Route->exec();
        }

    }
    
        
 

    
}

//系统开始运行
Sle::app()->run();
//Sle::app()->Debug->show();










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




