<?php 
namespace struggle\libraries;

class Debug extends Object{
    private $maBugInfo = array();
    private $hdRecord  = null;
    public  $recordType     = 'file';
    public  $recordFileName = '';
    public  $recordFilePath = '';
    public  $recordFileExt  = '';
    Public  $recordFileMode = '';
    Public  $recordFileSize = 2000;  //kb
    Public  $recordFileNum  = '';    //超出文件大小重命名文件数量
    const ERROR      = 1;
    const WARINING   = 2;
    const NOTICE     = 3;
    
    public function __construct(){
        static $oReocrd = null;
        $this->recordType     = \C('DEBUG_RECORD_TYPE');
        $this->recordFileName = \C('DEBUG_RECORD_FILE_NAME');
        $this->recordFilePath = \C('DEBUG_RECORD_FILE_PATH');
        $this->recordFileExt  = \C('DEBUG_RECORD_FILE_EXT');
        $this->recordFileMode = \C('DEBUG_RECORD_FILE_MODE');
        $this->recordFileSize = \C('DEBUG_RECORD_FILE_SIZE');
        $this->recordFileNum  = \C('DEBUG_RECORD_FILE_NUM');
        $this->recordType     || $this->recordType     = 'file';
        $this->recordFileName || $this->recordFileName = 'application';
        $this->recordFilePath || $this->recordFilePath = APP_RUNTIME;
        $this->recordFileExt  || $this->recordFileExt  = 'log';
        $this->recordFileMode || $this->recordFileMode = 'ab';
        $this->recordFileSize || $this->recordFileSize = 2000;
        $this->recordFileNum  || $this->recordFileNum  = 3;
        if (is_null($oReocrd)){
            $sClassName = '\struggle\libraries\cache\driver\\'.ctop($this->recordType);
            $sRecordFile = rtrim($this->recordFilePath,'/').'/'.$this->recordFileName.'.'.$this->recordFileExt;
            $aOpt = array('file'=>$sRecordFile,'mode'=>$this->recordFileMode,'size'=>$this->recordFileSize,'renum'=>$this->recordFileNum);
            \struggle\Sle::getInstance()->hasInfo("初始化类{$sClassName},初始化参数".print_r($aOpt,true),E_USER_NOTICE,\struggle\Sle::SLE_SYS,\microtime(true));
            $oReocrd = new $sClassName($aOpt);
        }
        $this->hdRecord = $oReocrd;
    }
    
    public function show(){
        $sHtml="<div style='font-family:sans-serif,verdana,arial,\"新宋体\";width:auto;border:1px solid #cccccc;font-size:13px;position:relative;margin:0px;padding:10px;'>"
               ."<div style='text-align:right;'><a style='text-decoration:none;color:blue;' href='javascript:void(0);' onclick='this.parentNode.parentNode.style.display=\"none\";'>X</a></div><div style='margin:0px;padding:0px;'><ul style='margin:0px;padding:0px;list-style-type:none;'>";
        $sTxt='';
        foreach (\struggle\Sle::getInstance()->aInfo as $info){
            if ($this->decideDebug($info[1], $info[2])){
                $aLevelInfo = \struggle\getErrLevel($info[1]);
                $info[3] = sprintf('%1.5f',round(($info[3] - BEGIN_TIME),5));
                switch ($aLevelInfo[0]){
                	case self::ERROR:
                		$sTxt .="<li style='line-height:100%;'><font color='red'>[{$aLevelInfo[1]}] {$info[3]}s {$info[0]}</font></li>";
                		break;
                	case self::WARINING:
                		$sTxt .= "<li style='line-height:100%;'><font color='blue'>[{$aLevelInfo[1]}] {$info[3]}s {$info[0]}</font></li>";
                		break;
                	default:
                		$sTxt .="<li style='line-height:120%;'><font color='#999999'>[{$aLevelInfo[1]}] {$info[3]}s {$info[0]}</font></li>";
                		break;
                }
            }
        }
        if ($sTxt){
            $sHtml .=$sTxt."</ul></div></div>";
            echo $sHtml;
        }
    }
    /**
     * 记录日志
     * @param string       $sLogInfo   日志内容
     * @param int          $iLevel     日志类型代码，引用php代码本身
     * @param unknown_type $iRunTime
     * @param unknown_type $bFromDebug
     */
    public function trace($sLogInfo, $iLevel, $iFrom=\struggle\Sle::SLE_APP, $iRunTime=0 ){
        if ($this->decideDebug($iLevel,$iFrom)){
            empty($iRunTime) && $iRunTime = microtime(true);
            $aInfo = array($sLogInfo,$iLevel, $iFrom, $iRunTime);
            \struggle\Sle::getInstance()->hasInfo($aInfo[0],$aInfo[1],$aInfo[2],$aInfo[3]);
            $this->save($aInfo[0],$aInfo[1],$aInfo[2],$aInfo[3]);
        }
    }
    
    public function save($mInfo,$iCode,$iType,$iExecTime){
        if ($this->decideDebug($iCode, $iType)){
            $sTxt = date('Y-m-d H:i:s')."/".($iExecTime-BEGIN_TIME)."s";
            $aInfoType = \struggle\getErrLevel($iCode);
            $sTxt .="[{$aInfoType[1]} {$aInfoType[2]}]{$mInfo}".PHP_EOL;
            if(!$this->hdRecord->write($sTxt))
                throw new Exception('写入日志失败'.__FILE__.'第'.__LINE__.'行', E_USER_ERROR);
        }    
    }
    
    private function decideDebug($iCode,$iFrom){
        $bRlt = false;
        if (APP_DEBUG){
            $sBugLevel = \C('DEBUG_LEVEL');
            $aInfoType = \struggle\getErrLevel($iCode);
            switch ($sBugLevel){
                case 'all':
                    $bRlt = true;
                    break;
                case 'sys':
                    $iFrom == \struggle\Sle::SLE_SYS && $bRlt = true;
                    break;
                case 'app':
                    $iFrom == \struggle\Sle::SLE_APP && $bRlt = true;
                    break;
                case 'sys_err':
                    if ($iFrom == \struggle\Sle::SLE_SYS && $aInfoType[0] == '1'){
                        $bRlt = true;
                    }
                    break;
                case 'sys_other':
                    if ($iFrom == \struggle\Sle::SLE_SYS && $aInfoType[0] != '1'){
                        $bRlt = true;
                    }
                    break;
                case 'app_err':
                    if ($iFrom == \struggle\Sle::SLE_APP && $aInfoType[0] == '1'){
                        $bRlt = true;
                    }
                    break;
                case 'app_other':
                    if ($iFrom == \struggle\Sle::SLE_APP && $aInfoType[0] != '1'){
                        $bRlt = true;
                    }
                    break;
                default:
                    $bRlt = true;
            }
        }
        return $bRlt;
    }
    
    
}