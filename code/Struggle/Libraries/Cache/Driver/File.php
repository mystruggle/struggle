<?php
/**
 * - 需要动态改变配置的，不用写在config.php文件中，如日志跟其他文本文件的读写，
 * 其他文件的路径、文件名随时都有可能改变，所以不需要写入，只需在构造函数添加
 * 一个配置数组形参即可
*/
namespace struggle\libraries\cache\driver;
use \struggle\libraries\Object;
use struggle\Sle;

class File extends Object{
    public  $dir     = '';    //文件存放目录
    public  $path    = '';    //文件路径
    public  $name    = '';    //文件名
    public  $ext     = '';    //文件扩展名
    public  $parts   = 3;     //超过文件设置的大小时重命名的数量
    public  $mode    = 'ab';  //打开文件模式
    public  $size    = 2000;  //文件大小 kb
    public  $length  = 1024;  //读取文件的长度，字节byte
    public  $error   = '';     //错误信息
    private $mHandle = null;
    private $mFile   = '';    //文件
    public  $debug   = false; //本类内部调试
    private $mTrace  = array(); //内部调试信息
    
    public function __construct($aOpt = array()){
		parent::__construct();
        if (!empty($aOpt)){
            isset($aOpt['dir'])   && $this->dir   = $aOpt['dir'];
            isset($aOpt['path'])  && $this->path  = $aOpt['path'];
            isset($aOpt['name'])  && $this->dir   = $aOpt['name'];
            isset($aOpt['ext'])   && $this->ext   = $aOpt['ext'];
            isset($aOpt['parts']) && $this->parts = $aOpt['parts'];
            isset($aOpt['mode'])  && $this->mode  = $aOpt['mode'];
            isset($aOpt['size'])  && $this->size  = $aOpt['size'];
        }       
    }

	public function _init(){
	    $this->dir || $this->dir = APP_RUNTIME;
	    $this->ext || $this->ext = 'txt';
	    $this->mFile = $this->dir.$this->path.$this->name.'.'.$this->ext;
	    $this->_trace('文件名'.$this->mFile);
	    if (!is_dir(dirname($this->mFile))){
	        $this->error = "目录不存在{$this->mFile}\t".__METHOD__."\tline\t".__LINE__;
	    }
	}
	
	
    /**
     * 把信息写入文件
     * @param string $content
     * @return boolean
     */
    public function write($content){
        $this->mHandle = $this->open();
        if (!$this->chkFileSize()){
            $this->error = "文件重命名失败\t".__METHOD__."\tline\t".__LINE__;
            return false;
        }
        @flock($this->mHandle, LOCK_EX);
        $mStat = @fwrite($this->mHandle, $content);
        @flock($this->mHandle, LOCK_UN);
        if ($mStat === false){
            $this->error = "文件写入失败\t".__METHOD__."\tline\t".__LINE__;
            return false;
        }
        return true;
    }
    
    
    /**
     * 检查文件大小，超过设定大小将重命名文件
     * @return boolean
     * @author luguo@139.com
     */
    private function chkFileSize(){
        $bRlt = true;
        if ((filesize($this->mFile) / 1024) > $this->size){
            $bRlt = false;
            $max = $this->parts;
            flock($this->mHandle, LOCK_EX);
            for($i=$max;$i>0;$i--){
                $sReName = $this->mFile.".{$i}";
                if (is_file($sReName)){
                    if ($i == $max)
                        @unlink($sReName);
                    else 
                        @rename($sReName, $this->mFile.'.'.($i+1));
                }
            }
            if (is_file($this->mFile))
                @rename($this->mFile, $this->mFile.'.1');   //会覆盖同名文件
            flock($this->mHandle, LOCK_UN);
            if (!is_file($this->mFile))
                $bRlt = true;
        }
        return $bRlt;
    }
    
    
    /**
     * 打开文件句柄
     * @return resource|null
     */
    private function open(){
        self::__construct();
        static $aHandle = array();
        $sKey = md5($this->mFile);
        if (isset($aHandle[$sKey]) && $aHandle[$sKey]){
            return $aHandle[$sKey];
        }
        $sWriteDir = dirname($this->mFile);
        if (!is_writable($sWriteDir)){
            $this->error = "目录不可写{$sWriteDir}\t".__METHOD__."\tline\t".__LINE__;
            return null;
        }
        
        !isset($aHandle[$sKey]) && $aHandle[$sKey] = @fopen($this->mFile, $this->mode);
        if (!\struggle\isResource($aHandle[$sKey])){
            $this->error = "文件打开失败{$this->mFile}\t".__METHOD__."\tline\t".__LINE__;
            return null;
        }
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
    
    
    /**
     * 返回调试信息
     * @return array:
     */
    public function getDebugInfo(){
        return $this->mTrace;
    }
    
    
    
    /**
     * 用于类内部的调试方法
     * @param string $message
     * @return void
     */
    private function _trace($message){
        if ($this->debug)
            $this->mTrace[] = $message;
    }
    
    
    public function __destruct(){
        if (\struggle\isResource($this->mHandle))
            @fclose($this->mHandle);
    }






}




