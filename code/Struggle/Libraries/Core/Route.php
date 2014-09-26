<?php
namespace struggle\libraries;
use struggle\Debug;
class Route extends Object{
    public  $url  = '';
    public  $mode = '';
    public  $module = '';
    public  $action = '';
    public  $defaultModule = '';
    public  $defaultAction = '';
    public  $moduleTag     = '';
    public  $actionTag     = '';
    public  $moduleSuffix = 'Controller';
    public  $moduleFileSuffix = '.controller.php';
    public  $methodPrefix = 'action';
	public  $namespaceModule = '\struggle\controller\\';

	
    public function __construct(){
        $this->mode = \struggle\C('ROUTE_MODE');
        $this->url    = '$sUrl';
        Debug::trace("路由模式{$this->mode};url=>{$this->url}", Debug::SYS_NOTICE);
    }
    
    
    public function exec(){
        if ($this->mode == self::ROUTE_NORMAL){
            $this->moduleTag = sle\C('ROUTE_MODULE_TAG')?sle\C('ROUTE_MODULE_TAG'):'m';
            $this->actionTag = sle\C('ROUTE_ACTION_TAG')?sle\C('ROUTE_ACTION_TAG'):'a';
            $this->defaultModule = sle\C('ROUTE_DEFAULT_MODULE')?sle\C('ROUTE_DEFAULT_MODULE'):'index';
            $this->defaultAction = sle\C('ROUTE_DEFAULT_ACTION')?sle\C('ROUTE_DEFAULT_ACTION'):'index';
            if (!isset($_GET[$this->moduleTag]))
                $this->module = sle\ctop($this->defaultModule);
            else 
                $this->module = sle\ctop($_GET[$this->moduleTag]);
                
            if (!isset($_GET[$this->actionTag]))
                $this->action = sle\ctop($this->defaultAction);
            else 
                $this->action = sle\ctop($_GET[$this->actionTag]);
            $this->debug("模块标签=>{$this->moduleTag};方法标签=>{$this->actionTag};模块=>{$this->module};方法=>{$this->action}",E_USER_NOTICE);
            $sControlFile = APP_CONTROLLER."{$this->module}{$this->moduleFileSuffix}";
            if(file_exists($sControlFile) && is_readable($sControlFile)){
                sle\require_cache($sControlFile);
                $sClassName = $this->namespaceModule.$this->module.$this->moduleSuffix;
                $sMethod = "{$this->methodPrefix}{$this->action}";
                $oController = new $sClassName();
                sle\Sle::app()->Controller = $oController;
                if(method_exists($oController,$sMethod)){
                    $oController->$sMethod();
                }else{
                    $this->debug("方法不存在{$sClassName}::{$sMethod}",E_USER_ERROR);
                }
            }else{
                $this->debug("controller文件不存在或不可读{$sControlFile}", E_USER_ERROR);
            }
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
        $aPath = parse_url($path);
		$xRlt['msg'] = 'url参数'.$path.',参数解析后'.print_r($aPath,true).' line '.__LINE__;
        $sUrlModule = '';
        $sUrlAction = '';
        $sQuery     = '';
        $aQuery     = array();
		$aTmpUrlPath = explode('/',$aPath['path']);
		if(count($aTmpUrlPath) === 1){
			$sUrlModule = \struggle\Sle::app()->Route->defaultModule;
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
			foreach($aQuery as $pair){
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
					$aQuery[$sParamKey] = $sParamVal;
				}else{
					$xRlt['status'] = false;
					$xRlt['msg']    = 'url参数不正确'.$path.' '.__METHOD__.' line '.__LINE__;
					break;
				}

			}
		}

		//生成对应模式的url
        if($xRlt['status'] && $this->mode === self::ROUTE_NORMAL){
			if($aQuery){
				$sQuery = http_build_query($aQuery);
			}
            $sUrl = "?{$this->moduleTag}={$sUrlModule}&{$this->actionTag}={$sUrlAction}".($sQuery?'&'.$sQuery:'');
        }
        
        $this->debug($xRlt['msg'],$xRlt['status']?E_USER_NOTICE:E_USER_ERROR,sle\Sle::SLE_SYS);
		return $sUrl;
	}









}








