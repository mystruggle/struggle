<?php
/**
 * 配置变量debug_enabled设置调试是否开始
 * 1、页面展示、后台保存(默认)
 * 2、只页面展示
 * 3、只后台记录
 * 4、保存等级 sys_notice sys_warning sys_error notice warning error
 * 5、是否记录消耗时间，默认记录
 * @author luguo<luguo@139.com>
 *
 */
namespace struggle;
use struggle\libraries\cache\driver\File;

class Debug{
    private static $mTraceInfo;
    //调试信息存储方式
    private static $mLogType = 'file';
    //存储方式为file时，存储文件名
    private static $mLogFileName = 'application';
    //存储方式为file时，存储文件扩展名
    private static $mLogFileExt = 'log';
    //存储方式为file时，存储文件目录
    private static $mLogFileDir = APP_RUNTIME;
    //存储方式为file时，存储文件路径
    private static $mLogFilePath = '';
    //存储方式为file时，打开文件模式
    private static $mLogFileMode = 'ab';
    //存储方式为file时，存储文件最大大小，kb
    private static $mLogFileMaxSize = 2000;
    //存储方式为file时，存储文件个数
    private static $mLogFileNum = 3;
    const SYS_NOTICE  = 1;
    const SYS_WARNING = 2;
    const SYS_ERROR   = 3;
    const NOTICE  = 4;
    const WARNING = 5;
    const ERROR   = 6;
    
    public static function init(){
        \struggle\C('DEBUG_LOG_TYPE') && self::$mLogType = \struggle\C('DEBUG_LOG_TYPE');
        if (self::$mLogType == 'file'){
            self::initFile();
        }
	}
	
	private static function initFile(){
        \struggle\C('DEBUG_LOG_FILE_NAME') && self::$mLogFileName = \struggle\C('DEBUG_LOG_FILE_NAME');
        \struggle\C('DEBUG_LOG_FILE_EXT') && self::$mLogFileExt = \struggle\C('DEBUG_LOG_FILE_EXT');
        \struggle\C('DEBUG_LOG_FILE_DIR') && self::$mLogFileDir = \struggle\C('DEBUG_LOG_FILE_DIR');
        \struggle\C('DEBUG_LOG_FILE_PATH') && self::$mLogFilePath = \struggle\C('DEBUG_LOG_FILE_PATH');
        \struggle\C('DEBUG_LOG_FILE_MODE') && self::$mLogFileMode = \struggle\C('DEBUG_LOG_FILE_MODE');
        \struggle\C('DEBUG_LOG_FILE_SIZE') && self::$mLogFileMaxSize = \struggle\C('DEBUG_LOG_FILE_SIZE');
        //Sle::app()->file
        import('@.Cache.Driver.File');
        Sle::app()->file->file = self::$mLogFileDir.self::$mLogFilePath.self::$mLogFileName.'.'.self::$mLogFileExt;
        Sle::app()->file->mode = self::$mLogFileMode;
        Sle::app()->file->size = self::$mLogFileMaxSize;
	}
    
    /**
     * 追踪调试信息
     * @param unknown $message
     * @param unknown $type
     * @param number $displayTime
     */
    public static function trace($message, $type = self::NOTICE, $displayTime = 0){
        if (C('DEBUG_ENABLED')){
			self::init();
            $displayTime || $displayTime = microtime(true);
            $displayTime = $displayTime - BEGIN_TIME;
            self::save($message, $type, $displayTime);
            $sMsg = is_string($message)?$message:(is_array($message)?print_r($message,true):var_export($message));
            self::$mTraceInfo[] = array($sMsg,$type,$displayTime);
            print_r(self::$mTraceInfo);die;
            $aInfo = array($sLogInfo,$iLevel, $iFrom, $iRunTime);
            \struggle\Sle::app()->hasInfo($aInfo[0],$aInfo[1],$aInfo[2],$aInfo[3]);
            $this->save($aInfo[0],$aInfo[1],$aInfo[2],$aInfo[3]);
        }
    }
    

    /**
     * 把追踪信息写入日志
     * @param string $msg
     * @param integer $type
     * @param integer $time
     * @throws \Exception
     * @tutorial 格式
     *           1970-01-01 00:00:00 错误:[system error]0.001s 错误信息  文件     第几行
     */
    public static function save($msg,$type,$time){
        $sTxt = '';
        $sMsgTypeTxt = self::getTypeText($type);
        $sTxt .= $sMsgTypeTxt;
        //是否记录时间，调试性能
        if(C('DEBUG_DISPLAY_TIME')){
            $sTxt .= $time."s\t"; 
        }
        $sTxt .= $msg."\t".PHP_EOL;
        if(!Sle::app()->file->write($sTxt)){
            halt("写入日志失败\t".__METHOD__."\tline\t".__LINE__);
        }
        die('endq');
        
        if ($this->decideDebug($iCode, $iType)){
            $sTxt = date('Y-m-d H:i:s')."/".($iExecTime-BEGIN_TIME)."s";
            $aInfoType = \struggle\getErrLevel($iCode);
            $sTxt .="[{$aInfoType[1]} {$aInfoType[2]}]{$mInfo}".PHP_EOL;
            if(!$this->hdRecord->write($sTxt))
                throw new \Exception('写入日志失败', E_USER_ERROR);
        }
    }
    
    
    
    /**
     * 根据消息类型，拼接消息类型内容
     * @param integer $msgType  消息类型
     * @return string
     */
    public static function getTypeText($msgType){
        $sTypeText = date('Y-m-d H:i:s')."\t";
        switch ($msgType){
            case self::ERROR:
                $sTypeText .= "错误：[ERROR]\t";
                break;
            case self::SYS_ERROR:
                $sTypeText .= "错误：[SYSTEM ERROR]\t";
                break;
            case self::WARNING:
                $sTypeText .= "警告：[WARNING]\t";
                break;
            case self::SYS_WARNING:
                $sTypeText .= "警告：[SYSTEM WARNING]\t";
                break;
            case self::NOTICE:
                $sTypeText .= "通知：[NOTICE]\t";
                break;
            case self::SYS_NOTICE:
                $sTypeText .= "通知：[SYSTEM NOTICE]\t";
                break;
            default:
                $sTypeText .= "其他：[OTHER]\t";
        }
        return $sTypeText;
    }









}




