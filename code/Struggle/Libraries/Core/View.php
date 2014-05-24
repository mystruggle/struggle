<?php 
namespace struggle\libraries\core;
use struggle as sle;


class View extends \struggle\libraries\Object{
    private $mThemePath = '';
    private $mTplSuffix   = 'htm';
    private $mTheme    = 'default';
    private $msCompilePath = '';
    private $mCompileFileName = '';
    
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
     */
    public function render($sRenderFile = '',$aTplData = array()){
        static $aTpl = array();
        $sTplFile = '';
        if (sle\fexists($sRenderFile)){
            $sTplFile = $sRenderFile;
        }else {
            $aTmp = explode('/', $sRenderFile);
            if (count($aTmp) == 2){
                $sTplFile = "{$this->mThemePath}{$this->mTheme}/{$aTmp[0]}/{$aTmp[1]}.{$this->mTplSuffix}";
            }
        }
        if ($sTplFile && sle\fexists($sTplFile) && is_readable($sTplFile)){
            $sKey = md5($sRenderFile.fileatime(realpath($sRenderFile)));
            if (!isset($aTpl[$sKey])){
                $sCompileFile = "{$this->msCompilePath}{$sKey}.php";
                if (is_writeable(dirname($sCompileFile))){
                    $oFile=new \struggle\libraries\cache\driver\File(array('file'=>$sTplFile,'mode'=>'rb'));
                    $sTplCon = $oFile->read();
                }else{
                    $this->debug("目录不可写".dirname($sCompileFile),E_USER_ERROR);
                }
            }
        }else{
            $this->debug("文件不存在或不可读{$sTplFile}", E_USER_ERROR);
        }
        //if (!$this->Sle->LastError){
            
        //}
        /*
        echo $sTplFile;
        $sFileContent = '';
        $sBasePath = '';
        if (is_file($sRenderFile)){
            $sCompileFile = md5($sRenderFile.fileatime($sRenderFile));
            if (!isset($aTpl[$sCompileFile])){
                $sFileContent = file_get_contents($sRenderFile);
                $sFileContent = $this->parse($sFileContent);
                $sFile = $this->itsCompiledPath.$this->itsCompiledSaveDir.$sCompileFile.'.php';
                
            }
        }else{
            if ($sDotPos = strpos($sRenderFile, '.')){
                $sPrefix = substr($sRenderFile, 0, $sDotPos);
                $sRenderFile = substr($sRenderFile, $sDotPos+1);
                if ($sPrefix == 'struggle' && $this->itsThemePath != THEME_PATH){
                    $this->itsThemePath = THEME_PATH;
                }
            }
            
            $sRenderFile = $this->itsThemePath . $this->itsTheme.'/';
        }
        $this->itsCompileFileName = md5($sRenderFile.filemtime($sRenderFile));
        */
    }
    
    
    private function parse($sTextCon){
        if (!empty($sTextCon)){
            $sTextCon = preg_replace_callback('/[{]([^}]+?)[}]/', array('struggle\\libraries\\core\\View','replaceTag'), $sTextCon);
        }
        return $sTextCon;
    }
    
    private function replaceTag($aMatch){
        if (isset($aMatch[1])){
            if ($aMatch[1][0] == '$'){
                return $this->isVariable($aMatch[1]);
            }
        }
        return $aMatch[0];
        \struggle\dump($aMatch);
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
    



}










