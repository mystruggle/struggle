<?php
/**
 * - 需要动态改变配置的，不用写在config.php文件中，如日志跟其他文本文件的读写，
 * 其他文件的路径、文件名随时都有可能改变，所以不需要写入，只需在构造函数添加
 * 一个配置数组形参即可
*/
namespace struggle\libraries\cache\driver;
use \struggle\libraries\Object;
use struggle\halt;
use struggle\Sle;

class File extends Object{
    public  $file    = '';
    public  $length  = 1024;    //读取文件的长度，字节byte
    public  $mode    = 'ab';
    public  $size    = 2000;  //kb
    public  $renum   = 3;     //超过文件设置的大小时重命名的数量
    private $mHandle = null;
    
    public function __construct($aOpt = array()){
		parent::__construct();
        if (!empty($aOpt)){
            isset($aOpt['file'])  && $aOpt['file'] && $this->file = $aOpt['file'];
            //isset($aOpt['path'])  && $aOpt['path'] && $this->path = $aOpt['path'];
            isset($aOpt['mode'])  && $aOpt['mode'] && $this->mode = $aOpt['mode'];
            isset($aOpt['size'])  && $aOpt['size'] && $this->size = $aOpt['size']; 
            isset($aOpt['renum']) && $aOpt['renum'] && $this->renum = $aOpt['renum']; 
        }       
    }

	public function _init(){
	}
    
    public function write($sContent){
        $this->mHandle = $this->open();
        if (!$this->chkFileSize())
            return $bRlt;
        @flock($this->mHandle, LOCK_EX);
        $mStat = @fwrite($this->mHandle, $sContent);
        @flock($this->mHandle, LOCK_UN);
        if ($mStat === false)
            return false;
        return true;
    }
    
    private function chkFileSize(){
        $bRlt = true;
        if ((filesize($this->file) / 1024) > $this->size){
            $bRlt = false;
            $max = $this->renum;
            flock($this->mHandle, LOCK_EX);
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
            flock($this->mHandle, LOCK_UN);
            if (!is_file($this->file))
                $bRlt = true;
        }
        return $bRlt;
    }
    
    
    /**
     * 打开文件句柄
     * @return resource
     */
    private function open(){
        static $aHandle = array();
        $sKey = md5($this->file);
        if (isset($aHandle[$sKey]) && $aHandle[$sKey]){
            return $aHandle[$sKey];
        }
        $sWriteDir = dirname($this->file);
        if (is_writeable($sWriteDir)){
            halt("目录不可写{$sWriteDir}\t".__METHOD__."\tline\t".__LINE__);
        }
        
        !isset($aHandle[$sKey]) && $aHandle[$sKey] = @fopen($this->file, $this->mode);
        var_dump($aHandle[$sKey],$this->file);
        if (!\struggle\isResource($aHandle[$sKey]))
            \struggle\halt("文件打开失败{$this->file}\t".__METHOD__."\tline\t".__LINE__);
        return $aHandle[$sKey];
    }
    
    
    
    public function read(){
        $sRlt = '';
        if (is_null($this->mHandle) && !$this->open()){
            return false;
        }
        while (!feof($this->mHandle)){
            $sRlt .= fread($this->mHandle, $this->length);
        }
        return $sRlt;
    }
    
    
    public function __destruct(){
        if (\struggle\isResource($this->mHandle))
            @fclose($this->mHandle);
    }






}




