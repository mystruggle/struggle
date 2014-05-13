<?php 
namespace struggle\libraries;

class Object{
    static $itsErrors=array();
    //调试类是否已打开
    protected $bInitDebug = false;
    
    public function halt(){
    }
    
    public function doLog($sMessage,$iNum){
        \struggle\Sle::getInstance()->moDebug->log($sMessage,$iNum);
    }
}