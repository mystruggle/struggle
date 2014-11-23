<?php
namespace struggle\libraries;

use struggle\Sle;
class Client extends Object{
    protected $mJs = array();
    private $mJsBasePath = '';
    private $mCssBasePath = '';
    //存放动态建立的属性
    private $mDynAttr = array();
    const POS_HEAD_TOP = 1;
    const POS_HEAD_BOTTOM = 2;
    const POS_BODY_BOTTOM = 3;
    const POS_BODY_AFTER  = 4;
    const TYPE_CSS        = 5;
    const TYPE_JS         = 6;
    
    public function __construct(){
        parent::__construct();
        $sTheme = Sle::app()->view->Theme;
        $this->mJsBasePath = APP_PUBLIC.$sTheme.'/js/';
        $this->mCssBasePath = APP_PUBLIC.$sTheme.'/css/';
        
		//检查加载前端页面配置
        $this->_chkPagerConfig();
    }
    
    
    public function __get($name){
        return isset($this->mDynAttr[$name])?$this->mDynAttr[$name]:null;
    }
    
    /**
     * 注册js
     * @param string|array $file    js文件，相对或绝对路径，或js代码块
     * @param integer      $pos     插入位置
     * @return Boolean     
     */
    public function registerClient($file,$pos = self::POS_HEAD_BOTTOM,$type = self::TYPE_JS){
        $aFile = is_array($file)?$file:array($file);
        $xRlt = array('status'=>true,'msg'=>'执行'.__METHOD__);
        foreach ($aFile as $sFile){
            if ($type == self::TYPE_JS)
                $this->_getJsExpr($sFile, $pos);
            else
                $this->_getCssExpr($sFile, $pos);
        }
        Debug::trace($xRlt['msg'], $xRlt['status']?Debug::SYS_NOTICE:Debug::SYS_ERROR);
        return $xRlt['status'];
    }
    
    /**
     * 导入配置文件
     * @param string $config  配置文件
     * @return boolean
     */
    public function load($config){
        if (!\struggle\isFile($config)){
            Debug::trace("配置文件不存在或不可读{$config}.\t".__METHOD__."\tline\t".__LINE__);
            return false;
        }
        $aConfig = include $config;
        if (!is_array($aConfig)){
            Debug::trace("配置文件返回数据非数组，{$config}.\t".__METHOD__."\tline\t".__LINE__);
            return false;
        }
        foreach ($aConfig as $key=>$config){
            $this->mDynAttr[$key] = $config;
        }
        return true;
    }
    
    
    private function _getJsExpr($js,$pos){
		$js = str_replace('//.js','',$js);
		$sQuesAfter = '';
		if(($iQuesPos = strrpos($js,'?'))!==false){
			$sQuesAfter = substr($js,$iQuesPos);
		    Debug::trace("js文件{$js},问号后面字符串{$sQuesAfter}");
			$js = substr($js,0,$iQuesPos);
			$sQuesAfter = Sle::app()->view->replaceGlobalConst($sQuesAfter);
		}
        $sFile = $js;
        $xRlt = array('status'=>true,'msg'=>'执行'.__METHOD__);
        !file_exists($sFile) && $sFile = $this->mJsBasePath.$sFile;
        if (file_exists($sFile) && is_string($sFile)){
            $this->mDynAttr['pager'][$pos][] = "<script type='text/javascript' src='{$sFile}{$sQuesAfter}'></script>";
        }elseif ($js && is_string($js)){
            $this->mDynAttr['pager'][$pos][] = "<script type='text/javascript'>\n{$js}\n</script>";
        }else{
            $xRlt['status'] = false;
            $xRlt['msg']    = '传递参数有误'.var_export($sFile,true).' '.__METHOD__.' line '.__LINE__;
        }
        Debug::trace($xRlt['msg'], $xRlt['status']?Debug::SYS_NOTICE:Debug::SYS_ERROR);
        return $xRlt['status'];
    }
    
    private function _getCssExpr($css,$pos){
        $sFile = $css;
        $xRlt = array('status'=>true,'msg'=>'执行'.__METHOD__);
        !file_exists($sFile) && $sFile = $this->mCssBasePath.$sFile;
        if (file_exists($sFile) && is_string($sFile)){
            $this->mDynAttr['pager'][$pos][] = "<link type='text/css' rel='stylesheet' href='{$sFile}' />";
        }elseif ($css && is_string($css)){
            $this->mDynAttr['pager'][$pos][] = "<style type='text/css'>\n{$css}\n</style>";
        }else{
            $xRlt['status'] = false;
            $xRlt['msg']    = '传递参数有误'.var_export($sFile,true).' '.__METHOD__.' line '.__LINE__;
        }
        Debug::trace("加载css文件{$sFile}", $xRlt['status']?Debug::SYS_NOTICE:Debug::SYS_ERROR);
        return $xRlt['status'];
    }




    private function _chkPagerConfig(){
        if($this->load(APP_CONF.'pager.inc.php')){
            //所有的模块的全局配置
            $aGlobal = $this->global?$this->global:array();
            $aGlobalPhb = isset($aGlobal[Client::POS_HEAD_BOTTOM])?$aGlobal[Client::POS_HEAD_BOTTOM]:array();
            $aGlobalPbb = isset($aGlobal[Client::POS_BODY_BOTTOM])?$aGlobal[Client::POS_BODY_BOTTOM]:array();
            $sModule = Sle::app()->route->module;
            $sModule = strtolower($sModule[0]).substr($sModule, 1);
            $sAction = Sle::app()->route->action;
            $sAction = strtolower($sAction[0]).substr($sAction, 1);
            $aPager = $this->$sModule;
            $aModuleGlobalPhb = isset($aPager['global'][Client::POS_HEAD_BOTTOM])?$aPager['global'][Client::POS_HEAD_BOTTOM]:array();
            $aModuleGlobalPbb = isset($aPager['global'][Client::POS_BODY_BOTTOM])?$aPager['global'][Client::POS_BODY_BOTTOM]:array();
            //模块下的全局配置
            $aGlobalPhb = array_merge($aGlobalPhb,$aModuleGlobalPhb);
            $aGlobalPbb = array_merge($aGlobalPbb,$aModuleGlobalPbb);

            
            $aPager = isset($aPager[$sAction])?$aPager[$sAction]:array();
            $aActionPhb = isset($aPager[Client::POS_HEAD_BOTTOM])?$aPager[Client::POS_HEAD_BOTTOM]:array();
            $aActionPbb = isset($aPager[Client::POS_BODY_BOTTOM])?$aPager[Client::POS_BODY_BOTTOM]:array();
                       
            $aPager[Client::POS_HEAD_BOTTOM] = array_merge($aGlobalPhb,$aActionPhb);
            $aPager[Client::POS_BODY_BOTTOM] = array_merge($aGlobalPbb,$aActionPbb);
            //print_r($aPager);die; 
            foreach ($aPager as $pos=>$val){
                foreach ($val as $file){
                    $sType = substr($file,strrpos($file, '.')+1);
                    if (strtolower($sType) == 'css'){
                        $this->registerClient($file,$pos,Client::TYPE_CSS);
                    }else{
                        $this->registerClient($file,$pos);
                    }
                }
            }
        }
    }





}