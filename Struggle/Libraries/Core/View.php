<?php 
namespace struggle\libraries\core;


class View{
    private $itsThemePath = '';
    private $itsSuffix   = 'htm';
    private $itsTheme    = 'default';
    private $itsCompileFileName = ''; 
    private $itsCompiledPath = '';
    private $itsCompiledSaveDir = 'runtime/';
    
    public function __construct(){
        $this->itsThemePath = APP_THEME;
        $this->itsCompiledPath = APP_CACHE;
        \struggle\C('VIEW_THEME_PATH') && $this->itsThemePath = \struggle\C('VIEW_THEME_PATH');
        \struggle\C('VIEW_SUFFIX')    && $this->itsSuffix   = \struggle\C('VIEW_SUFFIX');
        \struggle\C('VIEW_THEME')     && $this->itsTheme    = \struggle\C('VIEW_THEME');
        \struggle\C('VIEW_CACHE_PATH')    && $this->itsCompiledPath    = \struggle\C('VIEW_CACHE_PATH');
        \struggle\C('VIEW_CACHE_DIR')     && $this->itsCompiledSaveDir = \struggle\C('VIEW_CACHE_DIR');
        
    }
    
    /**
     * 渲染模板
     */
    public function render($sRenderFile = ''){
        static $aTpl = array();
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










