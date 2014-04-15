<?php
namespace struggle\libraries;

class Log{
    private static $itsStorageHandle = null;
    private $itsLogName       = 'application';
    private $itsLogDir        = 'runtime';
    private $itsLogExt        = 'log';
    
    public function __construct($sType, $aOpt = array()){
        if($sType == 'file'){
            if(empty($aOpt)){
                $aOpt = array('name'=>$this->itsLogName,'dir'=>$this->itsLogDir,'ext'=>$this->itsLogExt);
            }
        }
        $this->itsStorageHandle = cache\Cache::getInstance($sType, $aOpt);
    }
    
    public static function getInstance($sType = 'file', $aOpt = array()){
        static $aLog = array();
        if (!isset($aLog[$sType])){
            $aLog[$sType] = new Log($sType, $aOpt);
        } 
        return $aLog[$sType];
    }

    private static function initStorageHandle(){
        if(empty(self::$itsStorageHandle)){
            $aOpt  = array();
            if(!\struggle\C('log_type'))
                throw new Exception(\struggle\L('param_invalid'));
            $sType = \struggle\C('log_type');
            if($sType == 'file'){
                \struggle\C('log_name') && $aOpt['fileName'] = \struggle\C('log_name');
                \struggle\C('log_path') && $aOpt['savePath'] = \struggle\C('log_path');
                \struggle\C('log_ext')  && $aOpt['fileExt']  = \struggle\C('log_ext');
                \struggle\C('log_max_size')  && $aOpt['fileMaxSize']  = \struggle\C('log_max_size');
            }
            if(!class_exists('struggle\\libraries\\cache\\Cache')){
                include LIB_PATH.'cache/cache.php';
            }
            self::$itsStorageHandle = cache\Cache::getInstance($sType, $aOpt);
        }
    }
    
    
    
    public static function save($msg){
        if(!self::$itsStorageHandle){
            self::initStorageHandle();
        }
        if(!self::$itsStorageHandle)
            throw new Exception(\struggle\L('res_invalid'));
        self::$itsStorageHandle -> write($msg);
    }
    



}





