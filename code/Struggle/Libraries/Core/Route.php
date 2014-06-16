<?php
namespace struggle\libraries;
use struggle as sle;
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

	
    public function __construct($sUrl){
        $this->mode = sle\C('ROUTE_MODE');
        $this->url    = $sUrl;
        $this->debug("路由模式{$this->mode};url=>{$this->url}", E_USER_NOTICE);
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
}