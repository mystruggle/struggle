<?php
namespace struggle\libraries\core;
use struggle as sle;

class Controller extends \struggle\libraries\Object{
    private $mView = '';
    private $mSle  = '';
    private $mTplData = array();
    
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
        if (!$sPath){
            $sPath = "{$this->mSle->Route->module}/{$this->mSle->Route->action}";
        }else {
            $aTmp = parse_url($sPath);
            if(isset($aTmp['path']) && $aTmp['path']){
                $aControlPart = explode('/',$aTmp['path']);
                if (count($aControlPart) == 2){
                    $sPath = "{$aControlPart[0]}/{$aControlPart[1]}";
                }
                //传递的参数
                if (!is_array($aTplData)){
                    $this->debug("传递参数不规范，传递给模板的参数不是数组".(is_string($aTplData)?$aTplData:var_export($aTplData,true)),E_USER_ERROR,sle\Sle::SLE_SYS);
                    $aTplData = array();
                }
                if(isset($aTmp['query']) && $aTmp['query']){
                    $aTplData = array_merge($aTplData,explode('&',$aTmp['query']));
                }
                $this->mTplData = $aTplData;
            }
        }
        if (!$sPath)
            $this->debug("display目标不存在，请检查路径".var_export($sPath,true),E_USER_ERROR,sle\Sle::SLE_SYS);
        echo $this->mView->render($sPath);
    }
    
    
    public function assgin(){
        //
    }
    
    public function printOut(){
        //
    }





}






