<?php
namespace struggle\libraries\cache\driver;
use struggle as sle;
/**
 * 需要动态改变配置的，不用写在config.php文件中，如日志跟其他文本文件的读写，
   其他文件的路径、文件名随时都有可能改变，所以不需要写入，只需在构造函数添加
   一个配置数组形参即可
*/
class File extends \struggle\libraries\Object{
    public  $file    = '';
    public  $length  = 1024;    //读取文件的长度，字节byte
    public  $mode    = 'ab';
    public  $size    = 2000;  //kb
    public  $renum   = 3;     //超过文件设置的大小时重命名的数量
    private $moHandle = null;
    
    public function __construct($aOpt = array()){
        if (!empty($aOpt)){
            isset($aOpt['file'])  && $aOpt['file'] && $this->file = $aOpt['file'];
            //isset($aOpt['path'])  && $aOpt['path'] && $this->path = $aOpt['path'];
            isset($aOpt['mode'])  && $aOpt['mode'] && $this->mode = $aOpt['mode'];
            isset($aOpt['size'])  && $aOpt['size'] && $this->size = $aOpt['size']; 
            isset($aOpt['renum']) && $aOpt['renum'] && $this->renum = $aOpt['renum']; 
        }       
    }
    
    public function write($sContent){
        $bRlt = false;
        if (is_null($this->moHandle) && !$this->open()){
            return $bRlt;
        }
        if (!$this->chkFileSize())
            return $bRlt;
        @flock($this->moHandle, LOCK_EX);
        $mStat = @fwrite($this->moHandle, $sContent);
        @flock($this->moHandle, LOCK_UN);
        if ($mStat === false)
            return false;
        return true;
    }
    
    private function chkFileSize(){
        $bRlt = true;
        if ((filesize($this->file) / 1024) > $this->size){
            $bRlt = false;
            $max = $this->renum;
            flock($this->moHandle, LOCK_EX);
            for($i=$max;$i>0;$i--){
                $sReName = $this->file.".{$i}";
                if (is_file($sReName)){
                    if ($i == $max)
                        @unlink($sReName);
                    else 
                        @rename($sReName, $this->file.'.'.($i+1));
                }
            }
            if (is_file($this->file))
                @rename($this->file, $this->file.'.1');   //会覆盖同名文件
            flock($this->moHandle, LOCK_UN);
            if (!is_file($this->file))
                $bRlt = true;
        }
        return $bRlt;
    }
    
    private function open(){
        $bRlt = false;
        if (is_writable(dirname($this->file))){
            if (is_null($this->moHandle) && ($this->moHandle = @fopen($this->file, $this->mode))){
                $bRlt = true;
            }elseif (sle\isResource($this->moHandle)){
                $bRlt = true;
            }
        }
        return $bRlt;
    }
    
    public function read(){
        $sRlt = '';
        if (is_null($this->moHandle) && !$this->open()){
            return false;
        }
        while (!feof($this->moHandle)){
            $sRlt .= fread($this->moHandle, $this->length);
        }
        return $sRlt;
    }
    
    
    public function __destruct(){
        if (sle\isResource($this->moHandle))
            @fclose($this->moHandle);
    }






}




