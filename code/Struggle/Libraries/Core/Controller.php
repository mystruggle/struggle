<?php
namespace struggle\libraries\core;
class Controller extends \struggle\libraries\Object{
    private $mView = '';
    private $mSle  = '';
    
    public function __construct(){
        parent::__construct();
        static $oView='';
        if (!$oView){
            $oView = new View();
        }
        $this->mView = $oView;
        $this->mSle = \struggle\Sle::getInstance();
    }
    
    
    public function display($sPath=''){
        if (!$sPath){
            $sPath = "{$this->mSle->Route->module}/{$this->mSle->Route->action}";
        }
        $this->mView->render($sPath);
    }
    
    
    public function assgin(){
        //
    }
    
    public function printOut(){
        //
    }





}






