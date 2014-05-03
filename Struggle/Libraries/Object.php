<?php 
namespace struggle\libraries;

class Object{
    static $itsErrors=array();
    
    public function halt(){
    }
    
    public function doLog($sMessage,$iNum){
        \struggle\Sle::getInstance()->moDebug->log($sMessage,$iNum);
    }
}