<?php
/**
 * - 需要动态改变配置的，不用写在config.php文件中，如日志跟其他文本文件的读写，
 * 其他文件的路径、文件名随时都有可能改变，所以不需要写入，只需在构造函数添加
 * 一个配置数组形参即可
*/
namespace Struggle\Libraries\cache\driver;
use \Struggle\Libraries\Object;
use Struggle\Sle;

class File extends Object{
    //public  $dir     = '';    //文件存放目录
    //public  $path    = '';    //文件路径
    //public  $name    = '';    //文件名
    //public  $ext     = '';    //文件扩展名
    private  $mParts   = 3;     //超过文件设置的大小时重命名的数量
    private  $mMode    = 'ab';  //打开文件模式
    private  $mSize    = 2000;  //文件大小 kb
    private  $mLength  = 1024;  //读取文件的长度，字节byte
    private $mHandle = null;
    private $mFile   = '';    //文件
    private $mTrace  = array(); //内部调试信息
    public  $error   = '';     //错误信息
    public  $debug   = false; //本类内部调试
    
    public function __construct($aOpt = array()){
		parent::__construct();
    }

	public function _init(){
	}

	public static function self(){
		static $obj = null;
		if(is_null($obj)){
			$obj = new self;
		}
		return $obj;
	}
	
	/**
	 * 设置类属性
	 * @param string $name
	 * @param mixed $value
	 * @return boolean
	 */
	public function setAttr($name,$value){
	    $name = $this->_buildAttr($name);
	    if (property_exists($this, $name)){
	        $this->$name = $value;
	        return true;
	    }
	    return false;
	}
	
	/**
	 * 获取类型属性值
	 * @param string $name
	 * @return mixed|boolean  成功返回该属性的值，否则返回false
	 */
	public function getAttr($name){
	    $name = $this->_buildAttr($name);
	    if (property_exists($this, $name)){
	        return $this->$name;
	    }
	    return false;
	}
	
	
	/**
	 * 拼接属性名
	 * @param string $name
	 */
	private function _buildAttr($name){
	    if (!$name){
	        $this->error = "属性名不能为空.\t".__FILE__."\tline\t".__LINE__;
	        return false;
	    }
	    return 'm'.strtoupper($name[0]).substr($name, 1);
	}
	
	
    /**
     * 把信息写入文件
     * @param string $content
     * @return boolean
     */
    public function write($content){
        if (!\Struggle\isResource($this->mHandle)){
            $this->error = "文件打开失败{$this->mFile}.\t".__FILE__."\tline\t".__LINE__;
            return false;
        }
        if (!$this->chkFileSize()){
            $this->error = "文件重命名失败\t".__METHOD__."\tline\t".__LINE__;
            return false;
        }
        @flock($this->mHandle, LOCK_EX);
        $mStat = fwrite($this->mHandle, $content);
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
        if (\Struggle\isFile($this->mFile) && (filesize($this->mFile) / 1024) > $this->mSize){
            $bRlt = false;
            $max = $this->mParts;
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
     * 打开文件
     * @param  string $file  文件名
     * @return boolean
     */
    public function open($file){
        $this->mFile = $file;
        if (in_array(strtolower($this->mMode), array('r','r+')) && !\Struggle\isFile($this->mFile)){
            $this->error = "文件不存在或不可读{$this->mFile}\t".__FILE__."\tline\t".__LINE__;
            return false;
        }
        $this->_trace("打开文件{$this->mFile}");
        static $aHandle = array();
        $sKey = md5(realpath($this->mFile).$this->mMode);
        if (isset($aHandle[$sKey]) && $aHandle[$sKey]){
            $this->mHandle = $aHandle[$sKey];
            return true;
        }
        $aHandle[$sKey] = fopen($this->mFile, $this->mMode);
		//debug_print_backtrace();
        if (!\Struggle\isResource($aHandle[$sKey])){
            $this->error = "文件打开失败{$this->mFile}\t".__METHOD__."\tline\t".__LINE__;
            return false;
        }
        $this->mHandle = $aHandle[$sKey];
        return true;
    }
    
    
    
    public function read(){
        $sRlt = '';
        if (!\Struggle\isResource($this->mHandle)){
            $this->error = "文件打开失败{$this->mFile}.\t".__FILE__."\tline\t".__LINE__;
            return false;
        }
        while (!feof($this->mHandle)){
            $sRlt .= fread($this->mHandle, $this->mLength);
        }
        return $sRlt;
    }
    
    public function close(){
        \fclose($this->mHandle);
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
        if (\Struggle\isResource($this->mHandle)){
            //想重复利用不能关闭
            //@fclose($this->mHandle);
        }
    }






}




