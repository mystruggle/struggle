<?php 
namespace struggle\libraries;

class Object{
    const ROUTE_NORMAL    = 0;
    const ROUTE_PATHINFO  = 1;
    const ROUTE_REWRITE   = 2;
    const ROUTE_COMPAT    = 3;



    public function __construct(){
		$this->_init();
    }

	protected function _init(){
	}



	public function __get($sName){
		$sAttr = "m{$sName}";
		if(property_exists($this,$sAttr)){
			$sMethod = "_{$sName}";
			if(method_exists($this,$sMethod)){
				return $this->$sMethod();
			}else{
			    return $this->$sAttr;
			}
		}
		return false;
	}

	public function __set($sName,$mVal){
		$sName = "m{$sName}";
		$sMethod = "_".ucfirst($sName);
		if(property_exists($this,$sName)){
			$this->$sName = $mVal;
		}elseif(method_exists($this,$sMethod)){
			$this->$sMethod($mVal);
		}
		return false;
	}

    
    
    
    
    public function halt(){
    }
    
    
    
    public function debug($sMessage,$iMsgType,$iMsgSource = \struggle\Sle::SLE_APP,$iRunTime = 0){
        $iRunTime || $iRunTime = microtime(true);
        \struggle\Sle::app()->Debug->trace($sMessage,$iMsgType,$iMsgSource,$iRunTime);
    }
}