<?php 
namespace struggle\libraries\core;
use struggle as sle;


class View extends \struggle\libraries\Object{
    private $mThemePath = '';
    private $mTplSuffix   = 'htm';
    private $mTheme    = 'default';
    private $msCompilePath = '';
    private $mCompileFileName = '';
    private $mWidgetThemePath = "Widget/";
    private $mWidgetModuleSuffix = '.widget.php';
    private $mTplData = array();
    
    public function __construct(){
        parent::__construct();
        $this->mThemePath    = APP_THEME;
        $this->msCompiledPath = APP_RUNTIME;
        sle\C('VIEW_THEME_PATH') && $this->mThemePath   = sle\C('VIEW_THEME_PATH');
        sle\C('VIEW_TPL_SUFFIX') && $this->mTplSuffix   = sle\C('VIEW_TPL_SUFFIX');
        sle\C('VIEW_THEME')      && $this->mTheme       = sle\C('VIEW_THEME');
        
    }
        
    public function __get($sName){
        $sAttrName = "m{$sName}";
        if (isset($this->$sAttrName)){
            if ($this->$sAttrName){
                return $this->$sAttrName;
            }elseif (method_exists($this, $sName)){
                return $this->$sName();
            }
        }else{
            $this->debug("访问一个不存在的属性{$sName}", E_USER_WARNING, \struggle\Sle::SLE_SYS);
        }
        return false;
    }
    
    
    /**
     * 渲染模板
     * @param string $sRenderFile   模板路径.  format:   controller/action[?key1=value1&key2=value2] or 模板文件相对或绝对路径
     * @return boolean  成功返回编译后的模板路径失败返回false
     */
    public function render($sRenderFile = ''){
        static $aTpl = array();
        $sTplFile = '';
        if (sle\fexists($sRenderFile)){
            $sTplFile = $sRenderFile;
        }else {
            $aTmp = parse_url($sRenderFile);
            if(isset($aTmp['path']) && $aTmp['path']){
                $aControlPart = explode('/',trim($aTmp['path'],'/'));
                if (count($aControlPart) == 2){
                    $sTplFile = "{$this->mThemePath}{$this->mTheme}/{$aControlPart[0]}/".(sle\ptoc($aControlPart[1])).".{$this->mTplSuffix}";
                }
                //传递的参数
                if(isset($aTmp['query']) && $aTmp['query']){
                    $this->TplData = array_merge($this->TplData,explode('&',$aTmp['query']));
                }
            }
        }
        $sKey = '';
        if ($sTplFile && sle\fexists($sTplFile) && is_readable($sTplFile)){
            $sKey = md5($sTplFile.filemtime(realpath($sTplFile)));
            //clearstatcache();TODO;
            if (!isset($aTpl[$sKey])){
                $sCompileFile = APP_RUNTIME."{$this->msCompilePath}{$sKey}.php";
                if(sle\fexists($sCompileFile)){
                    $aTpl[$sKey] = $sCompileFile;
                }elseif (is_writeable(dirname($sCompileFile))){
                    $oFile=new \struggle\libraries\cache\driver\File(array('file'=>$sTplFile,'mode'=>'rb'));
                    $sTplCon = $oFile->read();
                    $sParsedCon = $this->parse($sTplCon);
                    $oFile = new \struggle\libraries\Cache\Driver\File(array('file'=>$sCompileFile,'mode'=>'wb'));
                    if($oFile->write($sParsedCon)){
                        $aTpl[$sKey] = $sCompileFile;
                        $this->debug("把编译后内容写入编译文件{$sCompileFile}",E_USER_NOTICE,sle\Sle::SLE_SYS);
                    }
                }else{
                    $this->debug("目录不可写".dirname($sCompileFile),E_USER_ERROR,sle\Sle::SLE_SYS);
                }
            }
        }else{
            $this->debug(__METHOD__."文件不存在或不可读 ".($sTplFile?$sTplFile:$sRenderFile)." line ".__LINE__, E_USER_ERROR,sle\Sle::SLE_SYS);
        }
        return sle\Sle::getInstance()->LastError?false:$aTpl[$sKey];
    }
    
    
    private function parse($sTextCon){
        if (!empty($sTextCon)){
            $sTextCon = preg_replace_callback('/[{]([^\s}][^}]*?)[}]/', array('struggle\\libraries\\core\\View','replaceTag'), $sTextCon);
        }
        return $sTextCon;
    }
    
    private function replaceTag($aMatch){
        $mRlt = $aMatch[0];
        if (isset($aMatch[1])){
            switch ($aMatch[1][0]){
                case '$':
                    break;
                case '/':
                    break;
                default:
                    $aTmp = str_split('/\s/', $aMatch[1]);
                    if (count($aTmp)>=2){
                        $sMethodName = "_".trim(array_shift($aTmp));
                        if (method_exists($this, $sMethodName)){
                            return $this->$sMethodName(implode(' ' , $aTmp));
                        }
                    }
            }
        }
        return $mRlt;
    }
    
    private function isVariable($mVar){
        $sVar       = $mVar;
        $sFuncName  = '';
        $aFuncParam = array();
        $sFuncParam = '';
        if (substr($mVar, -2) == '++'){
            return '<?php echo '.$mVar.';?>';
        }
        if (($iVertPos = strpos($mVar,'|')) !==false){
            $sVar = substr($mVar, 0,$iVertPos);
            $sFuncName = substr($mVar, $iVertPos+1);
            if (($iEqualPos = strpos($sFuncName, '=')) !== false){
                $sFuncParam = substr($sFuncName, $iEqualPos+1);
                $sFuncName  = substr($sFuncName, 0,$iEqualPos);
                $aFuncParam = explode(',', $sFuncParam);
            }
        }
        if ($sFuncName) {
            if ($aFuncParam){
                $sFuncParam = implode(',', $aFuncParam);
                if (in_array('#', $aFuncParam)){
                    $sFuncParam = str_replace('#', $sVar, $sFuncParam);
                }else {
                    $sFuncParam = $sVar.','.$sFuncParam;
                }
                return '<?php echo '.$sFuncName.'('.$sFuncParam.');?>';
            }
            return '<?php echo '.$sFuncName.'('.$sVar.');?>';
        }
        return '<?php echo '.$sVar.';?>';
    }
    
    
    
    
    
    private function _if(){
        //
    }
    
    
    private function _widget($sWidgetTpl){
        return "<?php \$this->widget('{$sWidgetTpl}');?>";
        /*
        $aUrl = parse_url($sWidgetTpl);
        if (isset($aUrl['path'])){
            $aTmp = explode('/', $aUrl['path']);
            if (count($aTmp) == 2){
                $sWidgetModule = sle\ctop($aTmp[0]);
                $sWidgetMethod = sle\ctop($aTmp[1]);
                $sWidgetFile = APP_CONTROLLER."{$sWidgetModule}{$this->mWidgetModuleSuffix}";
                die($sWidgetFile);
            }
        }
        */
    }
    
    
    



}










