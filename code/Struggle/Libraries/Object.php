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


    public function __set($name, $value){
        //
    }

    public function __get($name){
        //
    }
    
    
    
    public function halt(){
    }


    /**
     * 再封装eval
     * @param string code php代码
     * @return mixed 失败返回false;成功取决于字符串开始是否包含return,如果包含返回结果取决于代码表达式执行结果，否则返回true
     */
    protected function doEval($code){
        //eval 如果$code包含有return则返回$code中return值；否则$code被成功执行返回null，代码有错则返回false
        $retval = eval($code);
        if(strtolower(substr(ltrim($code),0,6)) != 'return'){
            return is_null($retval)?true:false;
        }
        return $retval;
    }
    
    
    /*
    public function debug($sMessage,$iMsgType,$iMsgSource = \struggle\Sle::SLE_APP,$iRunTime = 0){
        $iRunTime || $iRunTime = microtime(true);
        \struggle\Sle::app()->Debug->trace($sMessage,$iMsgType,$iMsgSource,$iRunTime);
    }*/
}