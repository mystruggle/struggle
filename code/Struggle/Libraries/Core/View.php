<?php 
namespace struggle\libraries\core;
use struggle as sle;


class View extends \struggle\libraries\Object{
    protected $mThemePath = '';
    protected $mTplSuffix   = 'htm';
    protected $mTheme    = 'Default';
    protected $mCompilePath = '';
    protected $mCompileFileName = '';
    protected $mTplData = array();
    protected $mWidgetTplPath = '';
    protected $mIncludeTplPath  = '';
    
    public function __construct(){
        parent::__construct();
        $this->mThemePath    = APP_THEME;
        sle\C('VIEW_THEME_PATH') && $this->mThemePath   = sle\C('VIEW_THEME_PATH');
        sle\C('VIEW_TPL_SUFFIX') && $this->mTplSuffix   = sle\C('VIEW_TPL_SUFFIX');
        sle\C('VIEW_THEME')      && $this->mTheme       = sle\C('VIEW_THEME');
        $this->mIncludeTplPath = APP_PUBLIC."{$this->mTheme}/";
        
    }
    
    /**
     * 渲染模板
     * @param string $sRenderFile   模板路径.  format:   controller/action or 模板文件相对或绝对路径
     * @param array  $aParam        传递的参数
     * @return boolean  成功返回编译后的模板路径失败返回false
     */
    public function render($sRenderFile = '', $aParam = array()){
        static $aTpl = array();
        $sTplFile = '';
        if (sle\fexists($sRenderFile)){
            $sTplFile = $sRenderFile;
        }else {
            $aControlPart = explode('/', trim($sRenderFile,'/'));
            if (count($aControlPart) >= 2){
                $sTplFile = "{$this->mThemePath}{$this->mTheme}/{$this->mWidgetTplPath}{$aControlPart[0]}/".(sle\ptoc($aControlPart[1])).".{$this->mTplSuffix}";
            }
            //传递的参数
            if (!is_array($aParam)){
                $this->debug(__METHOD__."参数有误，非数组类型 ".(is_string($aParam)?$aParam:print_r($aParam,true))." line ".__LINE__, E_USER_ERROR,sle\Sle::SLE_SYS);
                $aParam = array();
            }
        }
        $sKey = '';
        if ($sTplFile && sle\fexists($sTplFile) && is_readable($sTplFile)){
            $sKey = md5($sTplFile.filemtime(realpath($sTplFile)));
            //clearstatcache();TODO;
            if (!isset($aTpl[$sKey])){
                $sCompileFile = APP_RUNTIME."{$this->mCompilePath}{$sKey}.php";
                if(sle\fexists($sCompileFile) && !APP_DEBUG){
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
            $sTextCon = preg_replace_callback('/[{]([^\s{][^}]*?)[}]/', array('struggle\\libraries\\core\\View','replaceTag'), $sTextCon);
        }
        return $sTextCon;
    }
    
    private function replaceTag($aMatch){
        $mRlt = $aMatch[0];
        if (isset($aMatch[1])){
            switch ($aMatch[1][0]){
                case '$':
                    return $this->isVariable($aMatch[1]);
                    break;
                case '/':
                    $sMethodName = str_replace('/','',$aMatch[1]);
                    $sMethodName = "_close_{$sMethodName}";
                    if(method_exists($this,$sMethodName)){
                        $this->debug("调用".__CLASS__."::{$sMethodName}方法",E_USER_NOTICE,sle\Sle::SLE_SYS);
                        return $this->$sMethodName();
                    }
                    break;
                default:
                    $aTmp = preg_split('/\s/', $aMatch[1]);
                    if (count($aTmp)>=1){
                        $sMethodName = "_".trim(array_shift($aTmp));
                        if (method_exists($this, $sMethodName)){
                            $sTmpParam = implode(' ' , $aTmp);
                            $this->debug("调用".__CLASS__."::{$sMethodName}('{$sTmpParam}')方法",E_USER_NOTICE,sle\Sle::SLE_SYS);
                            return $this->$sMethodName($sTmpParam);
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
    
    
    
    
    
    private function _if($sCondition){
        $sCondition = str_replace(array(' gt ',' ge ',' lt ',' le ',' eq '),array(' > ',' >= ',' < ',' <= ',' == '),$sCondition);
        return "<?php if({$sCondition}):?>";
    }


    private function _elseif($sCondition){
        $sCondition = str_replace(array(' gt ',' ge ',' lt ',' le ',' eq '),array(' > ',' >= ',' < ',' <= ',' == '),$sCondition);
        return "<?php elseif({$sCondition}):?>";
    }


    private function _else(){
        return "<?php else:?>";
    }
    

    private function _close_if(){
        return "<?php endif;?>";
    }
    
    private function _widget($sWidgetTpl){
        return "<?php \$this->_widget_('{$sWidgetTpl}');?>";
    }


    private function _include_once($sFile){
        $aTmp = explode('/',trim($sFile));
        $sIncludeFile = $sFile;
        if(isset($aTmp[0]) && isset($aTmp[1])){
            $sIncludeFile = "{$this->mIncludeTplPath}{$aTmp[0]}/{$aTmp[1]}.{$this->mTplSuffix}";
        }
        if(sle\fexists($sIncludeFile) && is_readable($sIncludeFile)){
            ob_start();
            include $sIncludeFile;
            $sIncludeCon = ob_get_clean();
            return $sIncludeCon;
        }else{
            $this->debug(__METHOD__."文件不存在或不可读 {$sIncludeFile} line ".__LINE__, E_USER_ERROR,sle\Sle::SLE_SYS);
        }
    }


    private function _include($sFile){
        return "<?php echo \$this->_include_tpl_('{$sFile}');?>";
    }
    
    
    



}










