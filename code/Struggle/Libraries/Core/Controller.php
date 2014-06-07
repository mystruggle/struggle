<?php
namespace struggle\libraries\core;
use struggle as sle;

class BaseController extends \struggle\libraries\Object{
    private $mView = '';
    private $mSle  = '';
    private $msWidgetPath = '';
    private $mTplData = array();
    private $mCompiledTplFile = '';
    private $mWidgetThemePath = "Widget/";
    private $mWidgetModuleSuffix = '.widget.php';
    private $widgetModule = '';
    private $widgetAction = '';
    
    public function __construct(){
        parent::__construct();
        static $oView='';
        if (!$oView){
            $oView = new View();
        }
        $this->mView = $oView;
        $this->mSle = \struggle\Sle::getInstance();
    }
    
    
    public function display($sPath='', $aTplData = array()){
        $bFlag = true;
        if (!$sPath){
            $sPath = "{$this->mSle->Route->module}/{$this->mSle->Route->action}";
        }else {
            $aControlPart = explode('/',trim($aTmp['path'],'/'));
             if (count($aControlPart) >= 2){
                $sPath = "{$aControlPart[0]}/{$aControlPart[1]}";
            }else{
                $this->debug(__METHOD__."目标不存在或错误，请检查路径{$sPath} line ".__LINE__,E_USER_ERROR,sle\Sle::SLE_SYS);
            }
            //传递的参数
            if (!is_array($aTplData)){
                $this->debug("传递参数不规范，传递给模板的参数不是数组".(is_string($aTplData)?$aTplData:print_r($aTplData,true)).'line '.__LINE__,E_USER_ERROR,sle\Sle::SLE_SYS);
                $aTplData = array();
            }
            $this->mTplData = array_merge($this->mTplData,$aTplData);
        }
        if (sle\Sle::getInstance()->LastError){
            $this->debug(__METHOD__."由于存在致命错误，程序中止执行 line ".__LINE__,E_USER_ERROR,sle\Sle::SLE_SYS);
        }else{
            if($this->mCompiledTplFile = $this->mView->render($sPath)){
                header('Content-type:text/html;charset=utf-8');
                ob_flush();
                flush();
                ob_start();
                extract($this->mTplData);
                include $this->mCompiledTplFile;
                $sTxt=ob_get_clean();
                echo $sTxt;
            }else{
                $this->debug(__METHOD__."模板渲染失败！ line ".__LINE__,E_USER_ERROR,sle\Sle::SLE_SYS);
            }
        }
    }

    /**
     * 挂件处理函数
     * @param    string   $sPath    挂件目标地址，采用URI格式 module/action[?key1=value1&key2=value2]
     * @return   void
     */
    public function _widget_($sPath){
        $aTmp = parse_url($sPath);
        $sWidgetFile = '';
        if (isset($aTmp['path']) && $aTmp['path']){
            $aControlPart = explode('/',trim($aTmp['path'],'/'));
            if(count($aControlPart)>=2){
                $sModuleName = sle\ctop($aControlPart[0]);
                $sActName = sle\ctop($aControlPart[1]);
                $sWidgetFile = APP_CONTROLLER."{$sModuleName}{$this->mWidgetModuleSuffix}";
                if(sle\fexists($sWidgetFile) && is_readable($sWidgetFile)){
					sle\require_cache($sWidgetFile);
                    $sClassName = $this->mSle->Route->namespaceModule.$sModuleName.
						          sle\ctop(dirname(trim(str_replace('.','/',$this->mWidgetModuleSuffix),'/')));
                    $oWidget = new $sClassName();
                    $sMethodName = "action{$sActName}";
                    if(method_exists($oWidget,$sMethodName)){
                        $oWidget->widgetModule = $sModuleName;
                        $oWidget->widgetAction = $sActName;
                        $oWidget->$sMethodName();
                    }else{
                        $this->debug(__METHOD__."该方法不存在{$sClassName}::{$sMethodName} line ".__LINE__,E_USER_ERROR,sle\Sle::SLE_SYS);
                    }
                }else{
                    $this->debug(__METHOD__."文件不存在或不可读{$sWidgetFile} line ".__LINE__,E_USER_ERROR,sle\Sle::SLE_SYS);
                }

            }
        }else{
            $this->debug(__METHOD__."传递的参数有误".(print_r($aTmp,true))." line ".__LINE__,E_USER_ERROR,sle\Sle::SLE_SYS);
            return;
        }
    }
    
    
    public function assgin($sKey,$mValue){
        $this->mTplData[$sKey] = $mValue;
    }
    
    public function output($aData = array()){
        if($this->widgetModule && $this->widgetAction){
            $sPath = "{$this->widgetModule}/{$this->widgetAction}";
        }else{
            $this->debug(__METHOD__."挂件模块方法不能为空 line ".__LINE__,E_USER_ERROR,sle\Sle::SLE_SYS);
        }
        //传递的参数
        if (!is_array($aData)){
            $this->debug("传递参数不规范，传递给模板的参数不是数组".(is_string($aData)?$aData:print_r($aData,true)).'line '.__LINE__,E_USER_ERROR,sle\Sle::SLE_SYS);
            $aData = array();
        }
        $this->mTplData = array_merge($this->mTplData,$aData);

        if (sle\Sle::getInstance()->LastError){
            $this->debug(__METHOD__."由于存在致命错误，程序中止执行 line ".__LINE__,E_USER_ERROR,sle\Sle::SLE_SYS);
        }else{
            $this->mView->WidgetTplPath='Widget/';
            if($this->mCompiledTplFile = $this->mView->render($sPath)){
                extract($this->mTplData);
                include $this->mCompiledTplFile;
            }else{
                $this->debug(__METHOD__."挂件模板渲染失败！ line ".__LINE__,E_USER_ERROR,sle\Sle::SLE_SYS);
            }

        }
    }


    public function _include_tpl_($sFile){
        $aTmp = explode('/',trim($sFile));
        $sIncludeFile = $sFile;
        if(isset($aTmp[0]) && isset($aTmp[1])){
            $sIncludeFile = "{$this->mView->IncludeTplPath}{$aTmp[0]}/{$aTmp[1]}.{$this->mView->TplSuffix}";
        }
        if(!sle\fexists($sIncludeFile)){
            $sIncludeFile = "{$sFile}.{$this->mView->TplSuffix}";
        }
        if(sle\fexists($sIncludeFile) && is_readable($sIncludeFile) && ($this->mCompiledTplFile = $this->mView->render($sIncludeFile)) ){
            ob_start();
            include $this->mCompiledTplFile;
            $sIncludeCon = ob_get_clean();
            return $sIncludeCon;
        }else{
            $this->debug(__METHOD__."文件不存在或不可读 {$sIncludeFile} line ".__LINE__, E_USER_ERROR,sle\Sle::SLE_SYS);
        }
    }





}



namespace struggle\controller;
class Controller extends \struggle\libraries\core\BaseController{}




