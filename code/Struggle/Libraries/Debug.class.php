<?php
/**
 * 配置变量debug_enabled设置调试是否开始
 * 1、页面展示、后台保存(默认)
 * 2、只页面展示
 * 3、只后台记录
 * 4、保存等级 sys_notice sys_warning sys_error notice warning error
 * 5、是否记录消耗时间，默认记录
 * 6、判断生产环境还是开发环境，对重要信息隐藏，如文件路径；生产环境跳到500页面
 * @author luguo<luguo@139.com>
 *
 */
namespace Struggle\Libraries;
use Struggle\Libraries\Cache\Driver\File;
use Struggle\Sle;

class Debug{
    private static $mTraceInfo = array();
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
	public $drv = null;
    const SYS_NOTICE  = 11;
    const SYS_WARNING = 21;
    const SYS_ERROR   = 31;
    const NOTICE  = 41;
    const WARNING = 51;
    const ERROR   = 61;
    
    public  function __construct(){
        \Struggle\C('DEBUG_LOG_TYPE') && self::$mLogType = \Struggle\C('DEBUG_LOG_TYPE');
        if (self::$mLogType == 'file'){
            self::initFile();
        }
	}
	


	private static function initFile(){
        \Struggle\C('DEBUG_LOG_FILE_NAME') && self::$mLogFileName = \Struggle\C('DEBUG_LOG_FILE_NAME');
        \Struggle\C('DEBUG_LOG_FILE_EXT') && self::$mLogFileExt = \Struggle\C('DEBUG_LOG_FILE_EXT');
        \Struggle\C('DEBUG_LOG_FILE_DIR') && self::$mLogFileDir = \Struggle\C('DEBUG_LOG_FILE_DIR');
        \Struggle\C('DEBUG_LOG_FILE_PATH') && self::$mLogFilePath = \Struggle\C('DEBUG_LOG_FILE_PATH');
        \Struggle\C('DEBUG_LOG_FILE_MODE') && self::$mLogFileMode = \Struggle\C('DEBUG_LOG_FILE_MODE');
        \Struggle\C('DEBUG_LOG_FILE_SIZE') && self::$mLogFileMaxSize = \Struggle\C('DEBUG_LOG_FILE_SIZE');
	}
    
    /**
     * 追踪调试信息
     * @param string $message      消息内容
     * @param integer $type        消息类型 
     * @param number $displayTime  执行该语句的时间戳
     * @return boolean 返回true表示该信息已经记录，false没有记录该信息
     */
    public static function trace($message, $type = self::NOTICE, $displayTime = 0){
        //先判断是否开启调试，再判断该信息是否符合记录等级
        if (\Struggle\C('DEBUG_ENABLED') && self::_isPassed($type)){
            $displayTime || $displayTime = microtime(true);
            $displayTime = $displayTime - BEGIN_TIME;
            $sMsg = is_string($message)?$message:(is_array($message)?print_r($message,true):var_export($message));
            if (\Struggle\C('DEBUG_STORAGE'))
                self::save($sMsg, $type, $displayTime);
            self::$mTraceInfo[] = array($sMsg,$type,$displayTime);
            return true;
        }
        return false;
    }

	public static function self(){
		static $obj = null;
		if(is_null($obj)){
			$obj = new self;
		}
		return $obj;
	}
    

    /**
     * 把追踪信息写入日志
     * @param string $msg    追踪信息
     * @param integer $type  信息类型
     * @param integer $time  程序执行该处所花费时间
     * @tutorial 格式
     *           1970-01-01 00:00:00 错误:[system error 错误代码]0.001s 错误信息  文件     第几行
     * @return void
     */
    public static function save($msg,$type,$time){
        $sTxt = '';
        $sMsgTypeTxt = self::getTypeText($type);
        $sTxt .= $sMsgTypeTxt;
        //是否记录时间，调试性能
        if(\Struggle\C('DEBUG_DISPLAY_TIME')){
            $sTxt .= $time."s\t"; 
        }
        $sTxt .= $msg."\t".PHP_EOL;
        $sFile = self::$mLogFileDir.self::$mLogFilePath.self::$mLogFileName.'.'.self::$mLogFileExt;
        File::self()->setAttr('mode',self::$mLogFileMode);
        File::self()->setAttr('size',self::$mLogFileMaxSize);
        //写入文件
        if(!File::self()->open($sFile) || !File::self()->write($sTxt)){
            \Struggle\halt(File::self()->error);
        }
    }
    
    
    /**
     * 在页面显示调试信息
     */
    public static function show(){
        if (!\Struggle\C('DEBUG_PAGE'))
            return false;
        $sHtml="<div style='word-break:break-all;font-family:\"宋体\",sans-serif,verdana,arial;width:auto;border:1px solid #cccccc;font-size:13px;position:relative;margin:0px;padding:10px;'>"
                ."<div style='text-align:right;'><a style='text-decoration:none;color:blue;' href='javascript:void(0);' onclick='this.parentNode.parentNode.style.display=\"none\";'>X</a></div><div style='margin:0px;padding:0px;'><ul style='margin:0px;padding:0px;list-style-type:none;'>";
        $sTxt='';
        foreach (self::$mTraceInfo as $info){
            $sMsgTypeTxt = self::getTypeText($info[1]);
            $info[2] = sprintf('%1.5f',round($info[2],5));
            switch ($info[1]){
                case self::ERROR:
                case self::SYS_ERROR:
                case E_ERROR:
                case E_PARSE:
                case E_CORE_ERROR:
                case E_COMPILE_ERROR:
                case E_USER_ERROR:
                case E_RECOVERABLE_ERROR:
                    $sTxt .="<li style='line-height:100%;'><font color='red'>{$sMsgTypeTxt} {$info[2]}s {$info[0]}</font></li>";
                    break;
                case self::WARNING:
                case self::SYS_WARNING:
                case E_WARNING:
                case E_CORE_WARNING:
                case E_COMPILE_WARNING:
                case E_USER_WARNING:
                    $sTxt .= "<li style='line-height:100%;'><font color='blue'>{$sMsgTypeTxt} {$info[2]}s {$info[0]}</font></li>";
                    break;
                default:
                    $sTxt .="<li style='line-height:120%;'><font color='#999999'>{$sMsgTypeTxt} {$info[2]}s {$info[0]}</font></li>";
                    break;
            }
        }
        if ($sTxt)
            echo "{$sHtml}{$sTxt}</ul></div></div>";
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
            case self::SYS_ERROR:
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
            case E_RECOVERABLE_ERROR:
                $iCode = $msgType;
                if ($msgType == self::ERROR || $msgType == self::SYS_ERROR) $iCode = 256;
                $sTypeText .= "错误：[".(($msgType == self::SYS_ERROR)?'SYSTEM ':'')."ERROR {$iCode}]\t";
                break;
            case self::WARNING:
            case self::SYS_WARNING:
            case E_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_USER_WARNING:
                $iCode = $msgType;
                if ($msgType == self::WARNING || $msgType == self::SYS_WARNING) $iCode = 512;
                $sTypeText .= "警告：[".(($msgType == self::SYS_WARNING)?'SYSTEM ':'')."WARNING {$iCode}]\t";
                break;
            case self::SYS_NOTICE:
            case self::NOTICE:
            case E_NOTICE:
            case E_USER_NOTICE:
            case E_STRICT:
                $iCode = $msgType;
                if ($msgType == self::NOTICE || $msgType == self::SYS_NOTICE) $iCode = 1024;
                $sTypeText .= "通知：[".(($msgType == self::SYS_NOTICE)?'SYSTEM ':'')."NOTICE {$iCode}]\t";
                break;
            default:
                $sTypeText .= "其他：[OTHER {$msgType}]\t";
        }
        return $sTypeText;
    }
    
    /**
     * 根据调试等级，判断该消息类型是否需要跟踪、记录
     * @param integer $type  消息类型
     * @return boolean  符合等级返回true，否则返回false
     * @author luguo@139.com
     */
    private static function _isPassed($type){
        //类型等级有 all，sys,app,error,warning,notice,other
        $bFlag = true;
        static $aLevel = array();
        if (!$aLevel){
            $sLevel = strtolower(\Struggle\C('DEBUG_LEVEL'));
            $aLevel = explode(',', $sLevel);
            $aMsgLevel = array();
            
            //other不能与其他类型共用
            if (in_array('other', $aLevel) && count($aLevel)>1){
                self::_error("调试等级other不能与其他类型等级共用\t".__METHOD__."\tline\t".__LINE__);
            }
            //调试等级类型处理        
            if (in_array('sys', $aLevel) && strpos($sLevel, ',') === false){
                $aLevel[] = 'sys';
                $aLevel[] = 'error';
                $aLevel[] = 'warning';
                $aLevel[] = 'notice';
            }        
            if (in_array('app', $aLevel) && strpos($sLevel, ',') === false){
                $aLevel[] = 'app';
                $aLevel[] = 'error';
                $aLevel[] = 'warning';
                $aLevel[] = 'notice';
            }
            if (in_array('error', $aLevel) && strpos($sLevel, 'app') === false && strpos($sLevel, 'sys') === false){
                $aLevel[] = 'sys';
                $aLevel[] = 'app';
                $aLevel[] = 'error';
            }
            
            if (in_array('warning', $aLevel) && strpos($sLevel, 'app') === false && strpos($sLevel, 'sys') === false){
                $aLevel[] = 'sys';
                $aLevel[] = 'app';
                $aLevel[] = 'warning';
            }
    
            if (in_array('notice', $aLevel) && strpos($sLevel, 'app') === false && strpos($sLevel, 'sys') === false){
                $aLevel[] = 'sys';
                $aLevel[] = 'app';
                $aLevel[] = 'notice';
            }
    
            if (in_array('other', $aLevel)){
                $aLevel[] = 'app';
                $aLevel[] = 'other';
            }
            $aLevel = array_unique($aLevel);
        }
        if (in_array('all', $aLevel))
            return $bFlag;
        //处理消息类型
        switch ($type){
            //sys 类型
            case self::SYS_ERROR:
                $aMsgLevel[] = 'sys';
                $aMsgLevel[] = 'error';
                break;
            case self::SYS_WARNING:
                $aMsgLevel[] = 'sys';
                $aMsgLevel[] = 'warning';
                break;
            case self::SYS_NOTICE:
                $aMsgLevel[] = 'sys';
                $aMsgLevel[] = 'notice';
                break;
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
            case E_RECOVERABLE_ERROR:
                $aMsgLevel[] = 'app';
                $aMsgLevel[] = 'error';
                break;
            case E_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_USER_WARNING:
                $aMsgLevel[] = 'app';
                $aMsgLevel[] = 'warning';
                break;
            case E_NOTICE:
            case E_USER_NOTICE:
            case E_STRICT:
                $aMsgLevel[] = 'app';
                $aMsgLevel[] = 'notice';
                break;
            default:
                $aMsgLevel[] = 'app';
                $aMsgLevel[] = 'other';
        }
        //判断消息类型是否符合等级
        foreach ($aMsgLevel as $level){
            if (!in_array($level, $aLevel)){
                $bFlag = false;
                break;
            }
        }
        return $bFlag;
    }
    
    /**
     * 类内部错误处理方法
     * @param string $message
     * @return void
     */
    private static function _error($message){
        \Struggle\halt($message);
    }









}


