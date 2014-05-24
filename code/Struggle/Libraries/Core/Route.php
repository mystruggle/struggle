<?php
namespace struggle\libraries;
use struggle as sle;
class Route extends Object{
    public  $url  = '';
    public  $mode = '';
    public  $module = '';
    public  $action = '';
    public  $moduleSuffix = 'Controller';
    public  $moduleFileSuffix = '.controller.php';
    public  $methodPrefix = 'action';

	
    public function __construct($sUrl){
        $this->mode = sle\C('ROUTE_MODE');
        $this->url    = $sUrl;
        $this->debug("路由模式{$this->mode};url=>{$this->url}", E_USER_NOTICE);
    }
    
    
    public function exec(){
        if ($this->mode == 'normal'){
            $sModuleTag = sle\C('DISPATCHER_MODULE_TAG');
            $sActionTag = sle\C('DISPATCHER_ACTION_TAG');
            if (!isset($_GET[$sModuleTag]))
                $this->module = sle\ctop(sle\C('DISPATCHER_DEFAULT_MODULE'));
            else 
                $this->module = sle\ctop($_GET[$sModuleTag]);
                
            if (!isset($_GET[$sActionTag]))
                $this->action = sle\ctop(sle\C('DISPATCHER_DEFAULT_ACTION'));
            else 
                $this->action = sle\ctop($_GET[$sActionTag]);
            $this->debug("模块标签=>{$sModuleTag};动作标签=>{$sActionTag};模块=>{$this->module};动作=>{$this->action}",E_USER_NOTICE);
            $sControlFile = APP_CONTROLLER."{$this->module}{$this->moduleFileSuffix}";
            if(file_exists($sControlFile) && is_readable($sControlFile)){
                sle\require_cache($sControlFile);
                $sClassName = $this->module.$this->moduleSuffix;
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