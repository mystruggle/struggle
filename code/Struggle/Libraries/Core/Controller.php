<?php
namespace struggle\controller;
class Controller extends \struggle\libraries\core\BaseController{}

namespace struggle\libraries\core;
use struggle as sle;
use struggle\libraries\cache\driver\File;
use struggle\libraries\Client;

class BaseController extends \struggle\libraries\Object{
    private $mView = '';
    private $mSle  = '';
    private $msWidgetPath = '';
    protected $mTplData = array();
    private $mCompiledTplFile = '';
    private $mWidgetThemePath = "Widget/";
    private $mWidgetModuleSuffix = '.widget.php';
    private $widgetModule = '';
    private $widgetAction = '';
    private $curTpl       = '';//当前模板文件
    
    public function __construct(){
        parent::__construct();
        static $oView='';
        if (!$oView){
            $oView = new View();
        }
        $this->mView = $oView;
        $this->mSle = \struggle\Sle::app();
    }

	public function _init(){
		array_walk($_GET,array($this,'stripSpecialChar'));
		array_walk($_POST,array($this,'stripSpecialChar'));
		array_walk($_REQUEST,array($this,'stripSpecialChar'));
	}

	private function stripSpecialChar(&$item,$key){
		if(is_array($item)){
			array_walk($item,array($this,'stripSpecialChar'));
		}else{
            $item = htmlentities($item);
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
            $this->debug("传递参数不规范，传递给模板的参数不是数组".(is_string($aTplData)?$aTplData:print_r($aTplData,true)).'line '.__LINE__,E_USER_ERROR,sle\Sle::SLE_SYS);
            $aTplData = array();
        }
        $this->mTplData = array_merge($this->mTplData,$aTplData);
        if ($this->getCurTpl($sPath)){
            if ($this->mCompiledTplFile = $this->mView->render($this->curTpl)){
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
        $sLoyout = '';
        $sTpl    = '';
        $sFile = $tpl;
        $aRlt  = array('status'=>true,'msg'=>'');
        //检查布局文件
        if ($tpl){
            $sLoyout = $this->getCurTpl($tpl);
        }else{
            $sFile = APP_THEME.$this->mView->Theme.'/Layout/layout.'.$this->mView->TplSuffix;
            $sLoyout = $this->getCurTpl($sFile);
        }
        $sTpl = $this->getCurTpl();
        if ($sTpl && $sLoyout){
            //替换布局文件中{content}标签，用当前控制器模板内容替换之
            $sLoyout = preg_replace('/\{content\}/i', $sTpl, $sLoyout);
            //把替换后的布局文件写一个文件
            $sFileKey = $this->mView->getFileKey($sFile);
            $sContentKey = $this->mView->getFileKey($this->curTpl);
            $sFile = APP_RUNTIME.md5($sFileKey.$sContentKey).'.'.$this->mView->TplSuffix;
            if (!file_exists($sFile)){
                $oFile = new File(array('file'=>$sFile,'mode'=>'wb+'));
                $oFile->write($sLoyout);
            }
            if ($this->mCompiledTplFile = $this->mView->render($sFile)){
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
        if (empty($file)){
            $aRlt['status'] = false;
            $aRlt['msg']    = '编译文件不能为空 '.__METHOD__.' line '.__LINE__;
        }
        if ($aRlt['status'] && !file_exists($file)){
            $aRlt['status']
             = false;
            $aRlt['msg']    = '编译文件不存在 '.__METHOD__.' line '.__LINE__;
        }
        if ($aRlt['status'] && !is_readable($file)){
            $aRlt['status'] = false;
            $aRlt['msg']    = '编译文件不可读 '.__METHOD__.' line '.__LINE__;
        }
        
        if (!$aRlt['status']){
            $this->debug($aRlt['msg'], E_USER_ERROR,sle\Sle::SLE_SYS);
        }
        if (sle\Sle::app()->LastError){
            $this->debug('由于存在致命错误，程序中止执行 '.__METHOD__.' line '.__LINE__,E_USER_ERROR,sle\Sle::SLE_SYS);
            return false;
        }else{
            //输出内容
            header('Content-type:text/html;charset=utf-8');
            ob_flush();
            flush();
            ob_start();
            extract($this->mTplData);
            include $file;
            $sTxt=ob_get_clean();
            $this->_before($sTxt);
            echo $sTxt;
            return true;
        }
    }
    
    private function _before(&$content){
        $aJs = \struggle\Sle::app()->Client->Js;
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
     * @return string 成功返回模板内容,否则返回空字符
     * @author luguo@139.com
     * explain 当$tpl为空时，默认获取当前控制器模板；否则获取对应模板
     */
    private function getCurTpl($tpl = ''){
        $sFile = '';
        $aRlt = array('status'=>true,'msg'=>'');
        if ($tpl){
            if (file_exists($tpl)){
                $sFile = $tpl;
            }else{
                //不是绝对路径，检查是否是控制器/动作 格式
                $aTplInfo = explode('/', $tpl);
                $sTplPath = sle\ctop($aTplInfo[0]).'/'.sle\ptoc($this->mSle->Route->action);
                if (isset($aTplInfo[1]) && $aTplInfo[1]){
                    $sTplPath = sle\ctop($aTplInfo[1]).'/'.sle\ptoc($aTplInfo[0]);
                }
                $sFile = $this->mView->ThemePath.$this->mView->Theme.'/'.$sTplPath.'.'.$this->mView->TplSuffix;
                
            }
        }else{
            $sFile = $this->mView->ThemePath.$this->mView->Theme.'/'.sle\ctop($this->mSle->Route->module).'/'.sle\ptoc($this->mSle->Route->action).'.'.$this->mView->TplSuffix;
        }
        //判断文件是否存在
        if (file_exists($sFile)){
            if (!is_readable($sFile)){
                $aRlt['status'] = false;
                $aRlt['msg']    = '模板文件不可读'.$sFile.' '.__METHOD__.' line '.__LINE__;
            }
        }else{
            $aRlt['status'] = false;
            $aRlt['msg']    = '模板文件不存在'.$sFile.' '.__METHOD__.' line '.__LINE__;
        }
        if ($aRlt['status']){
            $this->curTpl = $sFile;
            return file_get_contents($this->curTpl);
        }else{
            $this->debug($aRlt['msg'],E_USER_ERROR,sle\Sle::SLE_SYS);
            return '';
        }
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
                $sModuleName = sle\ctop($aControlPart[0]);
                $sActName = sle\ctop($aControlPart[1]);
                $sWidgetFile = APP_CONTROLLER."{$sModuleName}{$this->mWidgetModuleSuffix}";
                if(sle\isFile($sWidgetFile) && is_readable($sWidgetFile)){
					sle\require_cache($sWidgetFile);
                    $sClassName = $this->mSle->Route->namespaceModule.$sModuleName.
						          sle\ctop(dirname(trim(str_replace('.','/',$this->mWidgetModuleSuffix),'/')));
                    $oWidget = new $sClassName();
                    $sMethodName = "action{$sActName}";
                    if(method_exists($oWidget,$sMethodName)){
                        $oWidget->widgetModule = $sModuleName;
                        $oWidget->widgetAction = $sActName;
                        //解析参数
                        if (isset($aTmp['query']) && $aTmp['query']){
                            $this->mSle->Route->registerGlobalVar($aTmp['query']);
                        }
                        $oWidget->$sMethodName();
                    }else{
                        $this->debug(__METHOD__."该方法不存在{$sClassName}::{$sMethodName} line ".__LINE__,E_USER_ERROR,sle\Sle::SLE_SYS);
                    }
                }else{
                    $this->debug(__METHOD__."文件不存在或不可读{$sWidgetFile} line ".__LINE__,E_USER_ERROR,sle\Sle::SLE_SYS);
                }

            }
        }else{
            $this->debug(__METHOD__."传递的参数有误".(print_r($aTmp,true))." line ".__LINE__,E_USER_ERROR,sle\Sle::SLE_SYS);
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
            $this->debug(__METHOD__."挂件模块方法不能为空 line ".__LINE__,E_USER_ERROR,sle\Sle::SLE_SYS);
        }
        //传递的参数
        if (!is_array($aData)){
            $this->debug("传递参数不规范，传递给模板的参数不是数组".(is_string($aData)?$aData:print_r($aData,true)).'line '.__LINE__,E_USER_ERROR,sle\Sle::SLE_SYS);
            $aData = array();
        }
        $this->mTplData = array_merge($this->mTplData,$aData);

        if (sle\Sle::app()->LastError){
            $this->debug(__METHOD__."由于存在致命错误，程序中止执行 line ".__LINE__,E_USER_ERROR,sle\Sle::SLE_SYS);
        }else{
            $this->mView->WidgetTplPath='Widget/';
            if($this->mCompiledTplFile = $this->mView->render($sPath)){
                extract($this->mTplData);
                include $this->mCompiledTplFile;
            }else{
                $this->debug(__METHOD__."挂件模板渲染失败！ line ".__LINE__,E_USER_ERROR,sle\Sle::SLE_SYS);
            }

        }
    }


    public function _include_tpl_($sFile){
        $sIncludeFile = $sFile;
        if (strrpos($sIncludeFile, '.',strrpos($sIncludeFile, '/'))!==false){
            if(substr($sIncludeFile, strrpos($sIncludeFile, '.',strrpos($sIncludeFile, '/'))+1) != $this->mView->TplSuffix)
                $sIncludeFile .= ".{$this->mView->TplSuffix}";
        }else{
            $sIncludeFile .=".{$this->mView->TplSuffix}";
        }
        if (!realpath($sIncludeFile)){
            $sIncludeFile = ltrim($sIncludeFile,'/');
            $sIncludeFile = "{$this->mView->PublicTplPath}{$sIncludeFile}";
        }
        if(sle\isFile($sIncludeFile) && is_readable($sIncludeFile) && ($this->mCompiledTplFile = $this->mView->render($sIncludeFile)) ){
            ob_start();
            include $this->mCompiledTplFile;
            $sIncludeCon = ob_get_clean();
            return $sIncludeCon;
        }else{
            $this->debug(__METHOD__."文件不存在或不可读 {$sIncludeFile} line ".__LINE__, E_USER_ERROR,sle\Sle::SLE_SYS);
        }
    }





}







