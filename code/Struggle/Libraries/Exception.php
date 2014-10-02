<?php
namespace struggle\libraries;
use struggle\libraries\Debug;

class Exception extends \Exception{
    
    public function errorHandle($errno, $errstr, $errfile, $errline, $errcontext){
        $sErrInfo = "{$errstr}\t{$errfile}\t第{$errline}行";
        Debug::trace($sErrInfo,$errno);    
    }
    
    
    
    public function shutdownHandle(){
        /**
         * 调用register_shutdown_function 中的回调函数后，当前工作目录的相对路径起始根目录变成'/'
         * 如，调用前为/app/www/htdocs/，调用后为'/'
         */ 
        if($aError = error_get_last()){
            $sErrInfo = "fatal error:{$aError['message']}{$aError['file']} 第{$aError['line']}行";
			Debug::trace($sErrInfo, $aError['type']);
			\struggle\halt($sErrInfo);
        }
    }
    
    
    public function exceptionHandle($e){
        $iCode = $e->getCode()?$e->getCode():E_USER_ERROR;
        $sMsg="异常错误信息: {$e->getMessage()}  {$e->getFile()} 第{$e->getLine()}行";
		Debug::trace($sMsg, $iCode);
        \struggle\halt($sMsg);
    } 
    
    
    
    
}