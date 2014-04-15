<?php 
namespace struggle\libraries;

class Object{
    static $itsErrors=array();
    
    public function halt(){
    }
    
    public function doLog($sMessage,$iNum){
        \struggle\Sle::getInstance()->moBug->log($sMessage,$iNum);
    }
}