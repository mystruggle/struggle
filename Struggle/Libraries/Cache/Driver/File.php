<?php
namespace struggle\libraries\cache\driver;
/**
 * 需要动态改变配置的，不用写在config.php文件中，如日志跟其他文本文件的读写，
   其他文件的路径、文件名随时都有可能改变，所以不需要写入，只需在构造函数添加
   一个配置数组形参即可
*/
class File extends \struggle\libraries\cache\Cache{
    private $itsBasePath    = '';
    private $itsSavePath    = '';
    private $itsHandle      = null;
    private $itsFileName    = 'application';
    private $itsFileExt     = 'txt';
    private $itsFileMode    = 'ab';
    private $itsFileMaxSize = 0;  //kb
    private $itsFileMaxNum  = 3;     //最多生成日志文件个数
    
    public function __construct($aOpt = array()){
        $this->itsBasePath = APP_CACHE;
        isset($aOpt['savePath'])     && $this->itsSavePath = $aOpt['savePath'];
        isset($aOpt['fileName'])     && $this->itsFileName = $aOpt['fileName'];
        isset($aOpt['fileExt'])      && $this->itsFileExt  = $aOpt['fileExt'];
        isset($aOpt['fileMode'])     && $this->itsFileMode = $aOpt['fileMode'];
        isset($aOpt['fileMaxSize'])  && $this->itsFileMaxSize = $aOpt['fileMaxSize'];

        $sFile = "{$this->itsBasePath}{$this->itsSavePath}{$this->itsFileName}.{$this->itsFileExt}";
        $sDir  = $this->itsBasePath.$this->itsSavePath;
        if (!is_dir($sDir)){
            @mkdir($sDir, 0777, true);
        }
        $this->itsHandle = @fopen($sFile, $this->itsFileMode);
        
        
    }
    
    public function write($sContent){
        $sContent = "[".date('Y-m-d H:i:s')."]{$sContent}".PHP_EOL;
        if ($this->itsFileMaxSize)
            $this->chkFileSize();
        @flock($this->itsHandle, LOCK_EX);
        @fwrite($this->itsHandle, $sContent);
        @flock($this->itsHandle, LOCK_UN);
    }
    
    private function chkFileSize(){
        $sFile = "{$this->itsBasePath}{$this->itsSavePath}{$this->itsFileName}.{$this->itsFileExt}";
        if ((filesize($sFile) / 1024) > $this->itsFileMaxSize){
            $max = $this->itsFileMaxNum;
            flock($this->itsHandle, LOCK_EX);
            for($i=$max;$i>0;$i--){
                $sReName = $sFile.".{$i}";
                if (is_file($sReName)){
                    if ($i == $max)
                        @unlink($sReName);
                    else 
                        @rename($sReName, $sFile.'.'.($i+1));
                }
            }
            if (is_file($sFile))
                @rename($sFile, $sFile.'.1');   //会覆盖同名文件
            flock($this->itsHandle, LOCK_UN);
        }
    }

    public function __set($sName,$mVal){
        if(isset($this->$sName)){
            $this->$sName = $mVal;
        }
    }
    
    
    public function __destruct(){
        @fclose($this->itsHandle);
    }






}




