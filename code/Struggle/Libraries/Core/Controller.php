<?php
namespace struggle\libraries\core;
use struggle as sle;

class Controller extends \struggle\libraries\Object{
    private $mView = '';
    private $mSle  = '';
    private $msWidgetPath = '';
    private $mTplData = array();
    private $mCompiledTplFile = '';
    private $mWidgetThemePath = "Widget/";
    private $mWidgetModuleSuffix = '.widget.php';
    
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
                $this->debug("传递参数不规范，传递给模板的参数不是数组".(is_string($aTplData)?$aTplData:var_export($aTplData,true)).'line '.__LINE__,E_USER_ERROR,sle\Sle::SLE_SYS);
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
    public function widget($sPath){
        $aTmp = parse_url($sPath);
        $sWidgetFile = '';
        if (isset($aTmp['path']) && $aTmp['path']){
            $aControlPart = explode('/',trim($aTmp['path'],'/'));
            if(count($aControlPart)>=2){
                $sModuleName = sle\ctop($aControlPart[0]);
                $sActName = sle\ctop($aControlPart[1]);
                $sWidgetFile = APP_CONTROLLER."{$sModuleName}{$this->mWidgetModuleSuffix}";
                if(sle\fexists($sWidgetFile) && is_readable($sWidgetFile)){
                    $sClassName = $sModuleName.sle\ctop(dirname(trim(str_replace('.','/',$this->mWidgetModuleSuffix),'/')));
                    $oWidget = new $sClassName();
                    $sMethodName = "action{$sActName}";
                    if(method_exists($oWidget,$sMethodName)){
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
    
    
    public function assgin(){
        //
    }
    
    public function output(){
        echo 'output';
    }





}






