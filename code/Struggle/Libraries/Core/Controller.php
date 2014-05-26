<?php
namespace struggle\libraries\core;
use struggle as sle;

class Controller extends \struggle\libraries\Object{
    private $mView = '';
    private $mSle  = '';
    private $mTplData = array();
    private $mCompiledTplFile = '';
    
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
            $aTmp = parse_url($sPath);
            if(isset($aTmp['path']) && $aTmp['path']){
                $aControlPart = explode('/',trim($aTmp['path'],'/'));
                if (count($aControlPart) == 2){
                    $sPath = "{$aControlPart[0]}/{$aControlPart[1]}";
                }else{
                    $this->debug(__METHOD__."目标不存在或错误，请检查路径{$sPath} line ".__LINE__,E_USER_ERROR,sle\Sle::SLE_SYS);
                }
                //传递的参数
                if (!is_array($aTplData)){
                    $this->debug("传递参数不规范，传递给模板的参数不是数组".(is_string($aTplData)?$aTplData:var_export($aTplData,true)).'line '.__LINE__,E_USER_ERROR,sle\Sle::SLE_SYS);
                    $aTplData = array();
                }
                if(isset($aTmp['query']) && $aTmp['query']){
                    $aTplData = array_merge($aTplData,explode('&',$aTmp['query']));
                }
                $this->mTplData = $aTplData;
            }
        }
        if (sle\Sle::getInstance()->LastError){
            $this->debug(__METHOD__."由于存在致命错误，程序中止执行 line ".__LINE__,E_USER_ERROR,sle\Sle::SLE_SYS);
        }else{
            if($this->mCompiledTplFile = $this->mView->render($sPath)){
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

    public function widget($sPath){
        $aTmp = explode('/',trim($sPath,'/'));
        if(count($aTmp)==2){
            echo 'end';
        }
    }
    
    
    public function assgin(){
        //
    }
    
    public function printOut(){
        //
    }





}






