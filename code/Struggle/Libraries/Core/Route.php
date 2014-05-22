<?php
namespace struggle\libraries;
class Route extends Object{
    public  $url  = '';
    public  $mode = '';
    public  $module = '';
    public  $action = '';
    private $moduleSuffix = 'Controller';
    private $actionPrefix = 'action';
	
    public function __construct($sUrl){
        $this->mode = \C('ROUTE_MODE');
        $this->url    = $sUrl;
        $this->debug("路由模式{$this->mode};url=>{$this->url}", E_USER_NOTICE);
    }
    
    
    public function exec(){
        if ($this->mode == 'normal'){
            $sModuleTag = \C('DISPATCHER_MODULE_TAG');
            $sActionTag = \C('DISPATCHER_ACTION_TAG');
            if (!isset($_GET[$sModuleTag]))
                $this->module = ctop(\C('DISPATCHER_DEFAULT_MODULE'));
            else 
                $this->module = ctop($_GET[$sModuleTag]);
                
            if (!isset($_GET[$sActionTag]))
                $this->action = ctop(\C('DISPATCHER_DEFAULT_ACTION'));
            else 
                $this->action = ctop($_GET[$sActionTag]);
            $this->debug("模块标签=>{$sModuleTag};动作标签=>{$sActionTag};模块=>{$this->module};动作=>{$this->action}",E_USER_NOTICE);
            $sControlFile = APP_CONTROLLER."{$this->module}.php";
            if(file_exists($sControlFile)){
                \require_cache($sControlFile);
                $sClassName = $this->module.$this->moduleSuffix;
                $sMethod = "{$this->actionPrefix}{$this->action}";
                $oController = new $sClassName();
                if(method_exists($oController,$sMethod)){
                    $oController->$sMethod();
                }else{
                    $this->debug("方法不存在{$sClassName}::{$sMethod}",E_USER_ERROR);
                }
            }else{
                $this->debug("controller文件不存在{$sControlFile}", E_USER_ERROR);
            }
        }
    }
}