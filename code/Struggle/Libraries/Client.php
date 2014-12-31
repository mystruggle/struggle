<?php
namespace struggle\libraries;

use struggle\Sle;
class Client extends Object{
    protected $mJs = array();
    private $mJsBasePath = '';
    private $mCssBasePath = '';
    //存放动态建立的属性
    private $mDynAttr = null;
	//本类定义的常量
	private $mConst   = array();
    const POS_HEAD_TOP = 1;
    const POS_HEAD_BOTTOM = 2;
    const POS_BODY_BOTTOM = 3;
    const POS_BODY_AFTER  = 4;
    const TYPE_CSS        = 5;
    const TYPE_JS         = 6;
    
    public function __construct(){
        parent::__construct();
        $sTheme = Sle::app()->view->Theme;
        $this->mJsBasePath = APP_PUBLIC.$sTheme.'/js/';
        $this->mCssBasePath = APP_PUBLIC.$sTheme.'/css/';
		//获取类常量
		static $aConst = array();
		if(!($this->mConst = $aConst)){
			$oReflectionClass = new \ReflectionClass($this);
			$this->mConst = $aConst = $oReflectionClass->getConstants();
		}
    }
    
    
    public function __get($name){
        return isset($this->mDynAttr->$name)?$this->mDynAttr->$name:null;
    }
    
    /**
     * 注册html引用文件
     * 需要参数：
     * 1、文件还是字符串//通过判断是否为文件
     * 2、插入位置
     * 3、类型，如js/css
     * 4、内容
     * @param string|array    $content   注册内容，当需要表明注册内容以外信息时，可用关联数组传递额外信息
     * 数组支持的键有
     * 数据类型type：表明该数据为js代码抑或css代码等，默认为js
     * 位置信息pos ：表明插入该代码位置，默认为body_bottom
     * 内容 content：插入的内容
     * 内容是否为文件isFile：说明内容类型是否为文件，false为其他类型如字符串等，默认为true
     * @return Boolean
     */
    public function register($content){
        $type = self::TYPE_JS;
        $pos  = self::POS_BODY_BOTTOM;
        $isFile = true;

        if(is_array($content) && isset($content['content'])){
            if(empty($content['content'])){
                Debug::trace("参数错误，注册内容不能为空\tfile\t".__FILE__."\tline\t".__LINE__,Debug::SYS_ERROR);
                return false;
            }
        }

        if(is_array($content) && isset($content['type'])){
            $type = $content['type'];
        }

        if(is_array($content) && isset($content['pos'])){
            $pos = $content['pos'];
			$aPos = explode(',',$pos);
			if(!isset($aPos[0]) || !isset($aPos[1]) || !isset($this->mConst['POS_'.strtoupper($aPos[0]).'_'.strtoupper($aPos[1])])){
				Debug::trace("导入文件位置参数错误{$pos}\tfile\t".__FILE__."\tline\t".__LINE__,Debug::SYS_ERROR);
				return false;
			}
			$pos = $this->mConst['POS_'.strtoupper($aPos[0]).'_'.strtoupper($aPos[1])];
        }

        if(is_array($content) && isset($content['isFile'])){
            $isFile = $content['isFile'];
        }
        if(!isset($this->mConst['TYPE_'.strtoupper($type)])) {
				Debug::trace("导入文件类型参数错误{$type}\tfile\t".__FILE__."\tline\t".__LINE__,Debug::SYS_ERROR);
				return false;
        }
        $type = $this->mConst['TYPE_'.strtoupper($type)];

        $content = $content['content'];
        if ($type == self::TYPE_JS)
            return $this->_buildJs($content, $isFile, $pos);
        elseif($type == self::TYPE_CSS)
            return $this->_buildCss($content, $isFile, $pos);

        return false;
    }


    /**
     * 组装js代码
     * @param string $content  内容
     * @param boolean $isFile  内容是否为文件
     * @param integer $pos     内容放置的位置
     * @return boolean
     */
    private function _buildJs($content, $isFile, $pos){
		$sQuesAfter = '';
        $js = $content;
        if($isFile){
            if(($iQuesPos = strrpos($content,'?'))!==false){
                $sQuesAfter = substr($content,$iQuesPos);
                $js = substr($content,0,$iQuesPos);
                $sQuesAfter = Sle::app()->view->replaceGlobalConst($sQuesAfter);
            }
            !is_file($js) && $js = $this->mJsBasePath.$js;
            if (is_file($js) && is_string($js)){
                //$this->mDynAttr['pager'][$pos][] = "<script type='text/javascript' src='{$js}{$sQuesAfter}'></script>";
                Sle::app()->view->addJs("<script type='text/javascript' src='{$js}{$sQuesAfter}'></script>",$pos);
                return true;
            }
        }else{
            if ($js && is_string($js)){
                //$this->mDynAttr['pager'][$pos][] = "<script type='text/javascript'>\n{$js}\n</script>";
				Sle::app()->view->addJs("<script type='text/javascript'>\n{$js}\n</script>",$pos);
                return true;
            }
        }

        Debug::trace('传递参数有误'.var_export($content,true).' '.__METHOD__.' line '.__LINE__,Debug::SYS_ERROR);
        return false;
    }


    /**
     * 组装css代码
     * @param string $content  内容
     * @param boolean $isFile  内容是否为文件
     * @param integer $pos     内容放置的位置
     * @return boolean
     */
    private function _buildCss($content, $isFile, $pos){
        $css = $content;
        if($isFile){
            !is_file($css) && $css = $this->mCssBasePath.$css;
            if (is_file($css) && is_string($css)){
                Sle::app()->view->addCss("<link type='text/css' rel='stylesheet' href='{$css}' />",$pos);
                return true;
            }
        }else{
            if ($css && is_string($css)){
                Sle::app()->view->addCss("<script type='text/javascript'>\n{$css}\n</script>",$pos);
                return true;
            }
        }

        Debug::trace('传递参数有误'.var_export($content,true)."\tin file\t".__FILE__."\tline\t".__LINE__,Debug::SYS_ERROR);
        return false;
    }


    /**
     * 导入配置文件
     * @param string $config  配置文件
     * @return boolean
     */
    public function load($config){
        if (!\struggle\isFile($config)){
            Debug::trace("配置文件不存在或不可读{$config}.\t".__METHOD__."\tline\t".__LINE__);
            return false;
        }
        $aConfig = include $config;
        if (!is_array($aConfig)){
            Debug::trace("配置文件返回数据非数组，{$config}.\t".__METHOD__."\tline\t".__LINE__);
            return false;
        }
        foreach ($aConfig as $key=>$config){
            $this->mDynAttr = $aConfig;
        }
        return true;
    }

    /**
	 * 把数组转换为对象
	 * @param array &$arr 需要转换的数组 
	 * @return void
	 * @example
	 * $this->arr2Object($arr);
	 */
	private function arr2Object(&$arr){
		static $iCount = 0;
		if(!is_array($arr))
			return false;
		$iCount++;
		foreach($arr as $key=>$val){
			if(is_array($val)){
				$this->arr2Object($val);
				$arr[$key] = (Object)$val;
			}
		}
		if(--$iCount === 0){
			$arr = (Object)$arr;
		}
	}




    /**
     * 加载js，css等配置文件
     * 格式(parse_url析之)
     * file://文件类型.标签.方位/[[sle|@].path/]]文件名称.[file|string].php文件,
	 * 1、文件类型:js,css
	 * 2、标签:    body,head等
	 * 3、方位:    top,bottom等
	 *    默认body.bottom
	 * 4、sle表示Sle的根目录为起点(即struggle目录),@表示项目的根目录,默认在项目配置目录(APP_CONF)
	 * 5、path文件路径
	 * 6、文件名称必须以file.php或string.php结尾,file表示该文件返回的是文件路径;string表示该文件返回的是字符串内容
     * 判断顺序
     * 1、判断是否存在sle.或@.,拼接php文件
     * 2、php文件是否为绝对路径，如果否，则在项目目录(APP_CONF)下的config目录下寻找，如果找不到判断文件不存在
     * 或
     * string://文件类型.标签.方位/字符串
	 * 1、文件类型:js,css
	 * 2、标签:    body,head等
	 * 3、方位:    top,bottom等
	 *    默认body.bottom
     * @example
     * file://js.body.bottom/test.php或file://js.body.bottom/@.index.test.php或file://js.body.bottom/@.test/index.test.php
     * string://js.body.bottom/jQuery(document).ready(function(){});
     * 
     */
    private function _chkPagerConfig(){
    }


    /**
	 * 解析包含文件，数组中的类型，位置信息保留
	 * @param $str url格式字符串
	 * @return array|boolean
	 */
	private function parseUrlFormatStr($str){
		$retval = array();
	    if($aAttr = parse_url($str)){
			//读取类型
			if(!isset($aAttr['scheme']) || empty($aAttr['scheme'])){
				Debug::trace("配置文件有误{$str} in file ".__FILE__.' line '.__LINE__,Debug::SYS_ERROR);
				return false;
			}
			//如果类型为文件
			if(strtolower($aAttr['scheme']) == 'file'){
				if(!isset($aAttr['path']) || empty($aAttr['path'])){
					Debug::trace("配置文件有误{$str} in file ".__FILE__.' line '.__LINE__,Debug::SYS_ERROR);
					return false;
				}
				if(!isset($aAttr['host']) || empty($aAttr['host'])){
					Debug::trace("配置文件有误{$str} in file ".__FILE__.' line '.__LINE__,Debug::SYS_ERROR);
					return false;
				}
				//解析文件属性
				$aFileAttr = explode('.',$aAttr['host']);
				$iPos = self::POS_BODY_BOTTOM;
				if(isset($aFileAttr[1]) && isset($aFileAttr[2])){
					$iPos = $this->mConst['POS_'.strtoupper($aFileAttr[1]).'_'.strtoupper($aFileAttr[2])];
				}
				$sBaseDir = APP_CONF;
				//指定根目录
				$tmp = array();
				if(isset($aAttr['user']) && $aAttr['user']){
					if(strtolower($aAttr['user']) == 'sle'){
						$sBaseDir = SLE_PATH;
					}elseif(strtolower($aAttr['user']) == 'app'){
						$sBaseDir = APP_PATH;
					}
					if(file_exists($sBaseDir.ltrim($aAttr['path'],'/')))
						$tmp = include $sBaseDir.ltrim($aAttr['path'],'/');
						//$aRes[$this->mConst['TYPE_'.strtoupper($aFileAttr[0])]][$iPos][] = $sBaseDir.ltrim($aAttr['path'],'/');
				}else{
					//没有指定根目录
					if(!file_exists($aAttr['path']) && !file_exists($sBaseDir.ltrim($aAttr['path'],'/'))){
						Debug::trace("文件不存在，配置有误{$str} in file ".__FILE__.' line '.__LINE__,Debug::SYS_ERROR);
						return false;
					}
					$tmp = file_exists($aAttr['path'])?include $aAttr['path'] : include $sBaseDir.ltrim($aAttr['path'],'/');
					

				}
				if(!is_array($tmp)){
					Debug::trace("包含文件返回值须为数组形式，{$str} in file ".__FILE__.' line '.__LINE__,Debug::SYS_ERROR);
					return false;
				}
				$retval['TYPE_'.strtoupper($aFileAttr[0])][$iPos] = $tmp;//$this->mConst[]
			}
		}else{
			Debug::trace("配置文件有误{$str}",Debug::SYS_ERROR);
		}
		return $retval;
	}





}



/*
				foreach($aRes as $type=>$res){
					foreach($res as $pos=>$files){
						foreach($files as $file){
							$tmp = include $file;
							if(isset($aImportFile[$type][$pos])){
								if(is_array($tmp))
									$aImportFile[$type][$pos] = array_merge($aImportFile[$type][$pos],$tmp);
								else
									array_push($aImportFile[$type][$pos],$tmp);
							}else{
								$aImportFile[$type][$pos] = array();
								!is_array($tmp) && $tmp = array($tmp);
								$aImportFile[$type][$pos] = array_merge($aImportFile[$type][$pos],$tmp);
							}
						}
					}
				}
*/