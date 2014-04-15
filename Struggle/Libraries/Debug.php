<?php 
namespace struggle\libraries;

class Debug{
    private $maBugInfo = array();
    const ERROR      = 1;
    const WARINING   = 2;
    const NOTICE     = 3;
    
    public function show(){
        $sHtml="<div style='width:960px;border:1px solid #cccccc;font-size:12px;position:relative;margin:0px;padding:10px;'>"
               ."<div style='text-align:right;'><a style='text-decoration:none;color:blue;' href='javascript:void(0);' onclick='this.parentNode.parentNode.style.display=\"none\";'>X</a></div><div style='margin:0px;padding:0px;'><ul style='margin:0px;padding:0px;list-style-type:none;'>";
        $sTxt='';
        foreach ($this->maBugInfo as $aInfo){
            $aLevelInfo = \struggle\getErrLevel($aInfo[1]);
            switch ($aLevelInfo[0]){
            	case self::ERROR:
            		$sTxt .="<li><font color='red'>[{$aLevelInfo[1]}] {$aInfo[2]}s {$aInfo[0]}</font></li>";
            		break;
            	case self::WARINING:
            		$sTxt .= "<li><font color='blue'>[{$aLevelInfo[1]}] {$aInfo[2]}s {$aInfo[0]}</font></li>";
            		break;
            	default:
            		$sTxt .="<li><font color='#999999'>[{$aLevelInfo[1]}] {$aInfo[2]}s {$aInfo[0]}</font></li>";
            		break;
            }
        }
        $sHtml .=$sTxt."</ul></div></div>";
        echo $sHtml;
    }
    /**
     * 记录日志
     * @param string       $sLogInfo   日志内容
     * @param int          $iLevel     日志类型代码，引用php代码本身
     * @param unknown_type $iRunTime
     * @param unknown_type $bFromDebug
     */
    public function log($sLogInfo, $iLevel, $iRunTime=0, $bFromDebug=true){
        if (APP_DEBUG){
            $fCurStampTime = microtime(true);
            $iRunTime = $iRunTime?round($iRunTime - BEGIN_TIME, 5):round($fCurStampTime-BEGIN_TIME,5);
            $this->maBugInfo[]=array($sLogInfo,$iLevel,$iRunTime);
            if ($bFromDebug)
                \struggle\sysNote($sLogInfo,$iLevel, $fCurStampTime);
        }
    }
    
    public function test(){
        print_r($this->maBugInfo);
    }
    
    
    
    
    private function getErrStr(){
    }
}