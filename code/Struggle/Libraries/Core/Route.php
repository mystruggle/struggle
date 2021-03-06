<?php
namespace struggle\libraries;
use struggle\libraries\Debug;
use struggle\Sle;

class Route extends Object{
    public  $url  = '';
    public  $mode = '';
    public  $module = '';
    public  $action = '';
    public  $defaultModule = '';
    public  $defaultAction = '';
    public  $moduleTag     = '';
    public  $actionTag     = '';
	//控制器
	private $mCtl          = null;
	//类内部错误存储变量
	private  $mError       = '';
	//类内部错误代码存储变量
	private  $mCode        = '';
	//类内部调试开关
	private  $mDebug       = false;
	//类内部跟踪信息存储变量
	private  $mTrace       = array();
	public  $host         = '';
	public  $port         = '';
	public  $scheme       = '';
	public  $baseUrl      = '';

	
    public function __construct(){
        $this->mode = \struggle\C('ROUTE_MODE');
        $this->url    = $_SERVER['REQUEST_URI'];
		$this->mCtl = Sle::app()->controller;
		$this->host = $_SERVER['HTTP_HOST'];
		$this->Port = 80;
		$this->scheme = 'http://';
		if (($iPos = strpos($this->host, ':')) !== false){
    		$this->port = substr($this->host, $iPos+1);
    		$this->host = substr($this->host, 0,$iPos);
		}
		intval($this->port) == 443 && $this->scheme = 'https://';
		$this->baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']),'/').'/';
		
        Debug::trace("路由模式{$this->mode};url=>{$this->url}", Debug::SYS_NOTICE);
    }
    
    /**
	 * 应用开始执行
	 */
    public function exec(){
		//普通模式
        if ($this->mode == self::ROUTE_NORMAL){
			//执行的模块、动作
            $this->moduleTag = \struggle\C('ROUTE_MODULE_TAG')?\struggle\C('ROUTE_MODULE_TAG'):'m';
            $this->actionTag = \struggle\C('ROUTE_ACTION_TAG')?\struggle\C('ROUTE_ACTION_TAG'):'a';
            $this->defaultModule = \struggle\C('ROUTE_DEFAULT_MODULE')?\struggle\C('ROUTE_DEFAULT_MODULE'):'index';
            $this->defaultAction = \struggle\C('ROUTE_DEFAULT_ACTION')?\struggle\C('ROUTE_DEFAULT_ACTION'):'index';
            if (!isset($_GET[$this->moduleTag]))
                $this->module = \struggle\ctop($this->defaultModule);
            else 
                $this->module = \struggle\ctop($_GET[$this->moduleTag]);
                
            if (!isset($_GET[$this->actionTag]))
                $this->action = \struggle\ctop($this->defaultAction);
            else 
                $this->action = \struggle\ctop($_GET[$this->actionTag]);
            Debug::trace("模块标签=>{$this->moduleTag};方法标签=>{$this->actionTag};模块=>{$this->module};方法=>{$this->action}",Debug::SYS_NOTICE);
			//模块文件
            $sControlFile = APP_CONTROLLER."{$this->module}".$this->mCtl->fileSuffix;
			Debug::trace("加载控制器文件{$sControlFile}", Debug::SYS_NOTICE);
			if(!\struggle\isFile($sControlFile)){
				throw new \Exception("控制器文件不存在{$sControlFile}");
			}
			//加载控制器
			\struggle\require_cache($sControlFile);
			$sClassName = rtrim(\struggle\fetchNamespace($sControlFile),'\\').'\\'.$this->module.$this->mCtl->moduleSuffix;
			$sMethod = $this->mCtl->methodPrefix.$this->action;
			$oController = new $sClassName();
			Sle::app()->controller = $oController;
			Debug::trace("调用模块、方法{$sClassName}::{$sMethod}",Debug::SYS_NOTICE);
			if(!method_exists($oController,$sMethod)){
				throw new \Exception("调用模块、方法{$sClassName}::{$sMethod}不存在！");
			}
		    defined('__MODULE__') || define('__MODULE__',$this->module);
		    defined('__ACTION__') || define('__ACTION__',$this->action);
			$oController->_beforeAction();
			$oController->$sMethod();
			$oController->_afterAction();
        }
    }


    
    /**
     * 把uri地址问号后面的参数注册成$_GET和$_REQUEST
     * @param string $querystring
     * @return  null
     * @author luguo@139.com
     */
    public function registerGlobalVar($querystring){
        $aQuery = explode('&', $querystring);
        foreach ($aQuery as $pair){
            $aPair = explode('=', $pair);
            if (isset($aPair[1]) && $aPair[1]){
                $_GET[trim($aPair[0])] = $_REQUEST[trim($aPair[0])] = $aPair[1];
            }else {
                $_GET[trim($aPair[0])] = $_REQUEST[trim($aPair[0])] = '';
            }
        }
    }


    /**
	 * 动态生成链接url
     * @param  string $path
     * @return string
	 */
	public function genUrl($path){
		$xRlt = array('status'=>true,'msg'=>'执行'.__METHOD__);
        $sUrl = '';
		if(!$path){
			$path = 'index/index';
		}
		$path = Sle::app()->view->replaceGlobalConst($path);
        $aPath = parse_url($path);
		$xRlt['msg'] = 'url参数'.$path.',参数解析后'.print_r($aPath,true).' line '.__LINE__;
        $sUrlModule = '';
        $sUrlAction = '';
        $sQuery     = '';
        $aQuery     = array();
		$aTmpUrlPath = explode('/',$aPath['path']);
		if(count($aTmpUrlPath) === 1){
			$sUrlModule = $this->defaultModule;
			$sUrlAction = $aTmpUrlPath[0];
		}else{
			$sUrlModule = $aTmpUrlPath[0];
			$sUrlAction = $aTmpUrlPath[1];
		}
        //分析url中是否存在变量，如果存在则用{}包括起来，且把双引号替换成单引号
		if($sUrlModule[0] =='$'){
			$sUrlModule = '{'.str_replace('"',"'",$sUrlModule).'}';
		}

		if($sUrlAction[0] =='$'){
			$sUrlAction = '{'.str_replace('"',"'",$sUrlAction).'}';
		}

		//解析参数
		if(isset($aPath['query']) && !empty($aPath['query'])){
			$aQuery = explode('&',trim($aPath['query'],'&'));
			$aTmpQuery = array();
			foreach($aQuery as $index=>$pair){
				$aPair = explode('=',$pair);
				if(count($aPair) == 2){
					$sParamKey = $aPair[0];
					$sParamVal = $aPair[1];
					//分析url的query部分中是否存在变量，如果存在则用{}包括起来，且把双引号替换成单引号
					if($sParamKey[0] =='$'){
						$sParamKey = '{'.str_replace('"',"'",$sParamKey).'}';
					}
					if($sParamVal[0] =='$'){
						$sParamVal = '{'.str_replace('"',"'",$sParamVal).'}';
					}
					$aTmpQuery[$sParamKey] = $sParamVal;
				}else{
					$xRlt['status'] = false;
					$xRlt['msg']    = 'url参数不正确'.$path.' '.__METHOD__.' line '.__LINE__;
					break;
				}

			}
			$aQuery = $aTmpQuery;
		}
		//生成对应模式的url
        if($xRlt['status'] && $this->mode === self::ROUTE_NORMAL){
			if($aQuery){
				$sQuery = http_build_query($aQuery);
			}
            $sUrl = "?{$this->moduleTag}={$sUrlModule}&{$this->actionTag}={$sUrlAction}".($sQuery?'&'.$sQuery:'');
        }
        Debug::trace($xRlt['msg'],$xRlt['status']?Debug::SYS_NOTICE:Debug::SYS_ERROR);
		return $sUrl;
	}









}








