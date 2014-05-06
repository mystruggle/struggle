<?php
namespace struggle\libraries\cache\driver;
/**
 * 需要动态改变配置的，不用写在config.php文件中，如日志跟其他文本文件的读写，
   其他文件的路径、文件名随时都有可能改变，所以不需要写入，只需在构造函数添加
   一个配置数组形参即可
*/
class File extends Object{
    public  $file    = '';
    public  $path    = '';
    public  $mode    = 'ab';
    public  $size    = 2000;  //kb
    public  $renum   = 3;     //超过文件设置的大小时重命名的数量
    
    public function __construct($aOpt = array()){
        if (!empty($aOpt)){
            isset($aOpt['file'])  && $aOpt['file'] && $this->file = $aOpt['file'];
            isset($aOpt['path'])  && $aOpt['path'] && $this->path = $aOpt['path'];
            isset($aOpt['mode'])  && $aOpt['mode'] && $this->mode = $aOpt['mode'];
            isset($aOpt['size'])  && $aOpt['size'] && $this->size = $aOpt['size']; 
            isset($aOpt['renum']) && $aOpt['renum'] && $this->renum = $aOpt['renum']; 
        }       
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
    
    public function read(){
        //
    }
    
    
    public function __destruct(){
        @fclose($this->itsHandle);
    }






}




