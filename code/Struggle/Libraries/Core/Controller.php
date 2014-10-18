<?php
namespace struggle\controller;
class Controller extends \struggle\libraries\core\BaseController{}

namespace struggle\libraries\core;
use struggle\Sle;
use struggle\libraries\cache\driver\File;
use struggle\libraries\Client;
use struggle\libraries\Exception;
use struggle\libraries\Debug;

class BaseController extends \struggle\libraries\Object{
    private $msWidgetPath = '';
    protected $mTplData = array();
    private $mCompiledTplFile = '';
    private $mWidgetThemePath = "Widget/";
    private $mWidgetModuleSuffix = '.widget.php';
    private $widgetModule = '';
    private $widgetAction = '';
    private $curTpl       = '';//当前模板文件
	//模块类名后缀
	public  $moduleSuffix = 'Controller';
	//动作方法名前缀
	public  $methodPrefix = 'action';
	//控制器文件后缀
	public  $fileSuffix   = '.controller.php';
    
    public function __construct(){
        parent::__construct();
    }

	public function _init(){
		array_walk($_GET,array($this,'stripSpecialChar'));
		array_walk($_POST,array($this,'stripSpecialChar'));
		array_walk($_REQUEST,array($this,'stripSpecialChar'));
		//初始化Client类 LIB_PATH
		$sClientFile = LIB_PATH.'Client.php';
		Sle::app()->registerClass($sClientFile);
	}
	
	
	public function __get($name){
	    $sAttrName = 'm'.strtoupper($name[0]).substr($name, 1);
	    if (property_exists($this, $sAttrName) && !is_null($this->$sAttrName))
	        return $this->$sAttrName;
	    $sMethodName = '_'.strtolower($name[0]).substr($name, 1);
	    if (method_exists($this, $sMethodName))
	        return $this->$sMethodName();
	    return false;
	}
	
	protected function redirect($url = '',$message = ''){
	    if (!$url){
	        $sModule = Sle::app()->route->module;
	        $url = Sle::app()->route->scheme.
	               Sle::app()->route->host.
	               (Sle::app()->route->port == 80?'':Sle::app()->route->port).
	               Sle::app()->route->baseUrl.'index.php'.
	               Sle::app()->route->genUrl("{$sModule}/index");
	        
	    }
	    $this->layout('tpl:'.APP_PUBLIC.'Default/html/redirect.html',array('message'=>$message,'url'=>$url));
	}
	
	
	
	private function stripSpecialChar(&$item,$key){
		if(is_array($item)){
			array_walk($item,array($this,'stripSpecialChar'));
		}else{
            //$item = htmlentities($item);
			$item = str_replace(array('{','}'),array('&#123;','&#125;'),$item);
		}
	}
    
    /**
     * 显示模板文件，不使用布局模板显示
     * @param string $sPath 需要显示模板文件，当显示的模板不是当前默认模板时，传递该参数
     * @param array $aTplData  传递给模板的参数，必须是关联数组
     * @return void
     * @author luguo@139.com
     */
    public function display($sPath='', $aTplData = array()){
        $sTpl = $this->getCurTpl($sPath);

        //传递的参数
        if (!is_array($aTplData)){
            Debug::trace("传递参数不规范，传递给模板的参数不是数组".(is_string($aTplData)?$aTplData:print_r($aTplData,true)).'line '.__LINE__,Debug::SYS_ERROR);
            $aTplData = array();
        }
        $this->mTplData = array_merge($this->mTplData,$aTplData);
        if ($this->getCurTpl($sPath)){
            if ($this->mCompiledTplFile = Sle::app()->view->render($this->curTpl)){
                return $this->outputComplieFile($this->mCompiledTplFile);
            }
        }
        return false;
    }
    
    
    /**
     * 调用布局模板
     * @param string $tpl   布局模板文件名，为空将渲染默认布局模板
     * @param array  $data  传递的模板值
     * @return boolean
     * @author luguo@139.com
     */
    public function layout($tpl = '', $data=array()){
        $sLoyout = ''; //布局文件内容
        $sTpl    = '';
        $sFile = $tpl; //布局文件路径
        $aRlt  = array('status'=>true,'msg'=>'');
        $oView =Sle::app()->view;
        //tpl:开头表示用户模板文件不是布局文件
        $iPos = strpos($tpl, 'tpl:');
        
        //检查布局文件
        if ($iPos===false && $tpl){
            $sLoyout = $this->getCurTpl($tpl);
        }else{
            $sFile = APP_THEME.$oView->Theme.'/Layout/layout.'.$oView->TplSuffix;
            $sLoyout = $this->getCurTpl($sFile);
        }
        //当前模板内容
        if ($iPos===false){
            $sTpl = $this->getCurTpl(); 
        }else{
            $sTpl = $this->getCurTpl(substr($tpl, 4));
        }
        if ($sTpl && $sLoyout){
            //替换布局文件中{content}标签，用当前控制器模板内容替换之
            $sLoyout = preg_replace('/\{content\}/i', $sTpl, $sLoyout);
            //把替换后的布局文件写一个文件
            $sFileKey = $oView->getFileKey($sFile);
            $sContentKey = $oView->getFileKey($this->curTpl);
            $sFile = APP_RUNTIME.md5($sFileKey.$sContentKey).'.'.$oView->TplSuffix;
            if (!file_exists($sFile)){
                $oFile = Sle::app()->file;
                $oFile->setAttr('mode','wb+');
                if(!$oFile->open($sFile) || !$oFile->write($sLoyout)){
					throw new Exception($oFile->error);
				}
            }
            if ($this->mCompiledTplFile = $oView->render($sFile)){
                $this->mTplData = array_merge($this->mTplData,$data);
                return $this->outputComplieFile($this->mCompiledTplFile);
            }
        }
        return false;
    }
    
    
    /**
     * 输出编译后文件
     * @param string $file   编译后的文件
     * @return boolean
     * @author luguo@139.com
     */
    private function outputComplieFile($file){
        $aRlt = array('status'=>true,'msg'=>'');
        if (!\struggle\isFile($file)){
            throw new Exception('编译文件不存在或不可读 '.$file);
            return false;
        }
		//输出内容
		header('Content-type:text/html;charset=utf-8');
		//ob_flush();
		//flush();
		ob_start();
		extract($this->mTplData);
		include $file;
		$sTxt=ob_get_clean();
		$this->_before($sTxt);
		echo $sTxt;
		return true;
    }
    
    private function _before(&$content){
        $aJs = \struggle\Sle::app()->client->pager;
        $aJs || $aJs = array();
        foreach ($aJs as $pos=>$js){
            switch ($pos){
                case Client::POS_HEAD_TOP:
                    $sJs = implode("\n", $aJs[Client::POS_HEAD_TOP]);
                    $content = preg_replace('/(?<=\<head\>)/i', "\n{$sJs}", $content);
                    break;
                case Client::POS_HEAD_BOTTOM:
                    $sJs = implode("\n", $aJs[Client::POS_HEAD_BOTTOM]);
                    $content = preg_replace('/(?=\<\/head\>)/i', "{$sJs}\n", $content);
                    break;
                case Client::POS_BODY_BOTTOM:
                    $sJs = implode("\n", $aJs[Client::POS_BODY_BOTTOM]);
                    $content = preg_replace('/(?=\<\/body\>)/i', "{$sJs}\n", $content);
                    break;
                case Client::POS_BODY_AFTER:
                    $sJs = implode("\n", $aJs[Client::POS_BODY_AFTER]);
                    $content = preg_replace('/(?<=\<\/body\>)/i', "\n{$sJs}", $content);
                    break;
            }
        }
        
    }
    
    
    
    /**
     * 获取模板内容
     * @param string $tpl 模板文件
     * @return string 成功返回模板内容,模板不存在将抛出异常
     * @author luguo@139.com
     * explain 当$tpl为空时，默认获取当前控制器模板；否则获取对应模板
     */
    private function getCurTpl($tpl = ''){
        $sFile = '';
        $oView = Sle::app()->view;
        $oRoute = Sle::app()->route;
        if ($tpl){
            if (file_exists($tpl)){
                $sFile = $tpl;
            }else{
                //不是绝对路径，检查是否是控制器/动作 格式
                $aTplInfo = explode('/', $tpl);
                $sTplPath = \struggle\ctop($aTplInfo[0]).'/'.\struggle\ptoc($oRoute->action);
                if (isset($aTplInfo[1]) && $aTplInfo[1]){
                    $sTplPath = \struggle\ctop($aTplInfo[1]).'/'.\struggle\ptoc($aTplInfo[0]);
                }
                $sFile = $oView->ThemePath.$oView->Theme.'/'.$sTplPath.'.'.$oView->TplSuffix;
                
            }
        }else{
            $sFile = $oView->ThemePath.$oView->Theme.'/'.\struggle\ctop($oRoute->module).'/'.\struggle\ptoc($oRoute->action).'.'.$oView->tplSuffix;
        }
        //判断文件是否存在
		if(!\struggle\isFile($sFile)){
			throw new Exception("模板文件不存在{$tpl},{$sFile}");
		}
		$this->curTpl = $sFile;
		return file_get_contents($this->curTpl);
    }

    /**
     * 挂件处理函数
     * @param    string   $sPath    挂件目标地址，采用URI格式 module/action[?key1=value1&key2=value2]
     * @return   void
     * @example $sPath  index.php?name=value&name2=value2 问号后面为传递的参数
     */
    public function _widget_($sPath){
        $aTmp = parse_url($sPath);
        $sWidgetFile = '';
        if (isset($aTmp['path']) && $aTmp['path']){
            $aControlPart = explode('/',trim($aTmp['path'],'/'));
            if(count($aControlPart)>=2){
                $sModuleName = \struggle\ctop($aControlPart[0]);
                $sActName = \struggle\ctop($aControlPart[1]);
                $sWidgetFile = APP_CONTROLLER."{$sModuleName}{$this->mWidgetModuleSuffix}";
                if(\struggle\isFile($sWidgetFile) && is_readable($sWidgetFile)){
					\struggle\require_cache($sWidgetFile);
                    $sClassName = Sle::app()->route->namespaceModule.$sModuleName.
						          \struggle\ctop(dirname(trim(str_replace('.','/',$this->mWidgetModuleSuffix),'/')));
                    $oWidget = new $sClassName();
                    $sMethodName = "action{$sActName}";
                    if(method_exists($oWidget,$sMethodName)){
                        $oWidget->widgetModule = $sModuleName;
                        $oWidget->widgetAction = $sActName;
                        //解析参数
                        if (isset($aTmp['query']) && $aTmp['query']){
                            Sle::app()->route->registerGlobalVar($aTmp['query']);
                        }
                        $oWidget->$sMethodName();
                    }else{
                        Debug::trace(__METHOD__."该方法不存在{$sClassName}::{$sMethodName} line ".__LINE__,Debug::SYS_ERROR);
                    }
                }else{
                    Debug::trace(__METHOD__."文件不存在或不可读{$sWidgetFile} line ".__LINE__,Debug::SYS_ERROR);
                }

            }
        }else{
            Debug::trace(__METHOD__."传递的参数有误".(print_r($aTmp,true))." line ".__LINE__,Debug::SYS_ERROR);
            return;
        }
    }
    
    
    public function assgin($sKey,$mValue){
        $this->mTplData[$sKey] = $mValue;
    }
    
    public function output($aData = array()){
        if($this->widgetModule && $this->widgetAction){
            $sPath = "{$this->widgetModule}/{$this->widgetAction}";
        }else{
            Debug::trace(__METHOD__."挂件模块方法不能为空 line ".__LINE__,Debug::SYS_ERROR);
        }
        //传递的参数
        if (!is_array($aData)){
            Debug::trace("传递参数不规范，传递给模板的参数不是数组".(is_string($aData)?$aData:print_r($aData,true)).'line '.__LINE__,Debug::SYS_ERROR);
            $aData = array();
        }
        $this->mTplData = array_merge($this->mTplData,$aData);

        Sle::app()->view->WidgetTplPath='Widget/';
		if($this->mCompiledTplFile = Sle::app()->view->render($sPath)){
			extract($this->mTplData);
			include $this->mCompiledTplFile;
		}else{
			Debug::trace(__METHOD__."挂件模板渲染失败！ line ".__LINE__,Debug::SYS_ERROR);
		}
    }


    public function _include_tpl_($sFile){
        $sIncludeFile = $sFile;
        $oView = Sle::app()->view;
        if (strrpos($sIncludeFile, '.',strrpos($sIncludeFile, '/'))!==false){
            if(substr($sIncludeFile, strrpos($sIncludeFile, '.',strrpos($sIncludeFile, '/'))+1) != $oView->TplSuffix)
                $sIncludeFile .= ".{$oView->TplSuffix}";
        }else{
            $sIncludeFile .=".{$oView->TplSuffix}";
        }
        if (!realpath($sIncludeFile)){
            $sIncludeFile = ltrim($sIncludeFile,'/');
            $sIncludeFile = "{$oView->PublicTplPath}{$sIncludeFile}";
        }
        if(\struggle\isFile($sIncludeFile) && is_readable($sIncludeFile) && ($this->mCompiledTplFile = $oView->render($sIncludeFile)) ){
            ob_start();
            include $this->mCompiledTplFile;
            $sIncludeCon = ob_get_clean();
            return $sIncludeCon;
        }else{
            Debug::trace(__METHOD__."文件不存在或不可读 {$sIncludeFile} line ".__LINE__, Debug::SYS_ERROR);
        }
    }





}







