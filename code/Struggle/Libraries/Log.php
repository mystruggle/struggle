<?php
namespace Struggle\Libraries;
use Struggle as sle;

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
            if(!sle\C('log_type'))
                throw new Exception(sle\L('param_invalid'));
            $sType = sle\C('log_type');
            if($sType == 'file'){
                sle\C('log_name') && $aOpt['fileName'] = sle\C('log_name');
                sle\C('log_path') && $aOpt['savePath'] = sle\C('log_path');
                sle\C('log_ext')  && $aOpt['fileExt']  = sle\C('log_ext');
                sle\C('log_max_size')  && $aOpt['fileMaxSize']  = sle\C('log_max_size');
            }
            if(!class_exists('Struggle\\Libraries\\Cache\\Cache')){
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
            throw new Exception(sle\L('res_invalid'));
        self::$itsStorageHandle -> write($msg);
    }
    



}





