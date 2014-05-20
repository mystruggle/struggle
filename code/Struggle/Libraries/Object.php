<?php 
namespace struggle\libraries;

class Object{
    static $itsErrors=array();
    
    public function halt(){
    }
    
    public function debug($sMessage,$iMsgType,$iMsgSource = \struggle\Sle::SLE_APP,$iRunTime = 0){
        $iRunTime || $iRunTime = microtime(true);
        \struggle\Sle::getInstance()->Debug->trace($sMessage,$iMsgType,$iMsgSource,$iRunTime);
    }
}