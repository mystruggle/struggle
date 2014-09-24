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
    const SYS_NOTICE  = 1;
    const SYS_WARNING = 2;
    const SYS_ERROR   = 3;
    const NOTICE  = 4;
    const WARNING = 5;
    const ERROR   = 6;
    
    public static function init(){
        static $oReocrd = null;
        $this->recordType     = \struggle\C('DEBUG_RECORD_TYPE');
        $this->recordFileName = \struggle\C('DEBUG_RECORD_FILE_NAME');
        $this->recordFilePath = \struggle\C('DEBUG_RECORD_FILE_PATH');
        $this->recordFileExt  = \struggle\C('DEBUG_RECORD_FILE_EXT');
        $this->recordFileMode = \struggle\C('DEBUG_RECORD_FILE_MODE');
        $this->recordFileSize = \struggle\C('DEBUG_RECORD_FILE_SIZE');
        $this->recordFileNum  = \struggle\C('DEBUG_RECORD_FILE_NUM');
        $this->recordType     || $this->recordType     = 'file';
        $this->recordFileName || $this->recordFileName = 'application';
        $this->recordFilePath || $this->recordFilePath = APP_RUNTIME;
        $this->recordFileExt  || $this->recordFileExt  = 'log';
        $this->recordFileMode || $this->recordFileMode = 'ab';
        $this->recordFileSize || $this->recordFileSize = 2000;
        $this->recordFileNum  || $this->recordFileNum  = 3;
		die('end');
        if (is_null($oReocrd)){
            $sClassName = '\struggle\libraries\cache\driver\\'.sle\ctop($this->recordType);
            $sRecordFile = rtrim($this->recordFilePath,'/').'/'.$this->recordFileName.'.'.$this->recordFileExt;
            $aOpt = array('file'=>$sRecordFile,'mode'=>$this->recordFileMode,'size'=>$this->recordFileSize,'renum'=>$this->recordFileNum);
            \struggle\Sle::app()->hasInfo("初始化类{$sClassName},初始化参数".print_r($aOpt,true),E_USER_NOTICE,\struggle\Sle::SLE_SYS,\microtime(true));
            $oReocrd = new $sClassName($aOpt);
        }
        $this->hdRecord = $oReocrd;
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
     *           错误:[system error]0.001s 错误信息  文件     第几行
     */
    public static function save($msg,$type,$time){
        //Sle::app()->file
        import('@.Cache.Driver.File');
        $sTxt = '';
        $sMsgTypeTxt = self::getTypeText($type);
        $sTxt .= $sMsgTypeTxt;
        //是否记录时间，调试性能
        if(C('DEBUG_DISPLAY_TIME')){
            $sTxt .= $time."s\t"; 
        }
        $sTxt .= $msg."\t";
        if(!Sle::app()->file->write($sTxt)){
            halt("写入日志失败\t".__METHOD__."\tline\t".__LINE__);
        }
        die('end');
        
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
        $sTypeText = '';
        switch ($msgType){
            case self::ERROR:
                $sTypeText = '错误：[ERROR]\t';
                break;
            case self::SYS_ERROR:
                $sTypeText = '错误：[SYSTEM ERROR]\t';
                break;
            case self::WARNING:
                $sTypeText = '警告：[WARNING]\t';
                break;
            case self::SYS_WARNING:
                $sTypeText = '警告：[SYSTEM WARNING]\t';
                break;
            case self::NOTICE:
                $sTypeText = '通知：[NOTICE]\t';
                break;
            case self::SYS_NOTICE:
                $sTypeText = '通知：[SYSTEM NOTICE]\t';
                break;
            default:
                $sTypeText = '其他：[OTHER]\t';
        }
        return $sTypeText;
    }









}




