<?php 
namespace struggle\libraries\core;
use struggle\libraries\Debug;
use struggle\Sle;
use \struggle\libraries\cache\driver\File;


class View extends \struggle\libraries\Object{
    protected $mThemePath = '';
    protected $mTplSuffix   = 'htm';
    protected $mTheme    = 'Default';
    protected $mCompilePath = '';
    protected $mCompileFileName = '';
    //protected $mTplData = array();
    protected $mWidgetTplPath = '';
    protected $mPublicTplPath  = '';
    //自定义常量数组
    protected $mConstants = array(); 
	private   $mLogicTag  = array('gt'=>'>','ge'=>'>=','lt'=>'<','le'=>'<=','eq'=>'==');
    
    public function __construct(){
        parent::__construct();
        $this->mThemePath    = APP_THEME;
        \struggle\C('VIEW_THEME_PATH') && $this->mThemePath   = \struggle\C('VIEW_THEME_PATH');
        \struggle\C('VIEW_TPL_SUFFIX') && $this->mTplSuffix   = \struggle\C('VIEW_TPL_SUFFIX');
        \struggle\C('VIEW_THEME')      && $this->mTheme       = \struggle\C('VIEW_THEME');
        $this->mPublicTplPath = APP_PUBLIC."{$this->mTheme}/html/";
        //获取已定义常量
        static $aConstants = array();
        if(!$aConstants){
            $aConstants = get_defined_constants(true);
            $aConstants = $aConstants['user'];
        }
        $this->mConstants = $aConstants;
        
    }
    
    public function __get($name){
        $sAttrName = 'm'.strtoupper($name[0]).substr($name, 1);
        if (property_exists($this, $sAttrName))
            return $this->$sAttrName;
        $sMethodName = '_'.strtolower($name[0]).substr($name, 1);
        if (method_exists($this, $sMethodName))
            return $this->$sMethodName();
        return false;
    }
    
    /**
     * 返回文件的唯一键名
     * @param string $file   文件(相对路径或绝对路径)
     * @param string $type   用于处理文件名的方法
     * @return string 返回字符串或空字符串
     */
    public function getFileKey($file,$type = 'md5'){
        $sFile = realpath($file);
        $sFileName = '';
        if($sFile){
            $sFileName = $type($sFile.filemtime($sFile));
        }
        return $sFileName;
    }
    
    /**
     * 渲染模板
     * @param string $sRenderFile   模板路径.  format:   controller/action or 模板文件相对或绝对路径
     * @param array  $aParam        传递的参数
     * @return string|null  成功返回编译后的模板路径失败返回null
     */
    public function render($sRenderFile = '', $aParam = array()){
        static $aTpl = array();
        $sTplFile = $sRenderFile;
        if (!$sTplFile) {
            $aControlPart = explode('/', trim($sTplFile,'/'));
            if (count($aControlPart) >= 2){
                $sTplFile = "{$this->mThemePath}{$this->mTheme}/{$this->mWidgetTplPath}{$aControlPart[0]}/".(\struggle\ptoc($aControlPart[1])).".{$this->mTplSuffix}";
            }
            //传递的参数
            if (!is_array($aParam)){
                Debug::trace("参数有误，非数组类型 ".print_r($aParam,true)."\t".__METHOD__."\tline\t".__LINE__, Debug::SYS_ERROR);
                $aParam = array();
            }
        }
        $sKey = '';
        if (\struggle\isFile($sTplFile)){
            $sKey = md5($sTplFile.filemtime(realpath($sTplFile)));
            //clearstatcache();TODO;
            if (!isset($aTpl[$sKey])){
                $sCompileFile = APP_RUNTIME."{$this->mCompilePath}{$sKey}.php";
                if(\struggle\isFile($sCompileFile) && !\struggle\C('DEBUG_ENABLED')){
                    $aTpl[$sKey] = $sCompileFile;
                }elseif (is_writeable(dirname($sCompileFile))){
                    //$oFile=new File(array('file'=>$sTplFile,'mode'=>'rb'));die('end');
                    $oFile = Sle::app()->file;
                    $oFile->setAttr('mode','rb');
                    if (!$oFile->open($sTplFile) || !($sTplCon = $oFile->read())){
                        throw new Exception($oFile->error);
                    }
                    $sParsedCon = $this->parse($sTplCon);
                    $oFile->setAttr('mode','wb');
                    if (!$oFile->open($sCompileFile) || !$oFile->write($sParsedCon)){
                        throw new Exception($oFile->error);
                    }
                    $aTpl[$sKey] = $sCompileFile;
                    Debug::trace("把编译后内容写入编译文件{$sCompileFile}",Debug::SYS_NOTICE);
                }else{
                    Debug::trace("目录不可写".dirname($sCompileFile),Debug::SYS_ERROR);
                }
            }
        }else{
            Debug::trace(__METHOD__."文件不存在或不可读 ".($sTplFile?$sTplFile:$sRenderFile)." line ".__LINE__, Debug::SYS_ERROR);
        }
        return $aTpl[$sKey];
    }
    
    /**
     * 匹配模板中特殊字符
     * @param string $sTextCon    需要解析的文本内容
     * @return string        返回解析后的模板内容;模板中没有特殊标签将直接返回模板内容
     * @explain  只要模板存在一对大括号'{}'标签，该标签和标签里的内容将会被匹配，
     *           匹配内容将集中调用replaceTag方法处理
     */
    private function parse($sTextCon){
        if (!empty($sTextCon)){
            $sTextCon = preg_replace_callback('/[{]([^\s{][^}]*?)[}]/', array('struggle\\libraries\\core\\View','replaceTag'), $sTextCon);
        }
        return $sTextCon;
    }
    
    
    /**
     * 调用方法替换特殊标签内容
     * @param array $aMatch   正则匹配特殊标签后的数组
     * @return string  返回替换字符
     * @explain  匹配数组第二元素为方法名称或特殊字符串，如果存在'_方法名'的方法将被调用返回该函数的返回值
     */
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
                        Debug::trace("调用".__CLASS__."::{$sMethodName}方法",Debug::SYS_NOTICE);
                        return $this->$sMethodName();
                    }
                    break;
                default:
                    $aTmp = preg_split('/\s/', $aMatch[1]);
                    if (count($aTmp)>=1){
                        $sMethodName = "_".trim(array_shift($aTmp));
                        if (method_exists($this, $sMethodName)){
                            $sTmpParam = implode(' ' , $aTmp);
                            Debug::trace("调用".__CLASS__."::{$sMethodName}('{$sTmpParam}')方法",Debug::SYS_NOTICE);
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
            return '<?php echo '.$mVar.'++;?>';
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
    /**
     * 处理非$开头的字面常量
     * @param string $param  
     */
    private function _field($param){
        //检查是否用了函数处理
        $iVericalPos = strpos($param, '|');
        $sConstant = $param;
        if ($iVericalPos !== false){
            $sConstant = substr($param, 0,$iVericalPos);
            $sFunc      = substr($param, $iVericalPos+1);
            $sFuncParam = $sConstant;
            //判断是否有其他函数参数
            $iEqSignPos = strpos($sFunc, '=');
            if ($iEqSignPos !== false){
                $sFunc = substr($sFunc, 0,$iEqSignPos);
                $sFuncParam = substr($sFunc, $iEqSignPos+1);
                $sFuncParam = str_replace('#', $sConstant, $sFuncParam);
            }
            $sFuncParam = '"'.str_replace(',','","',str_replace('"', "'", $sFuncParam)).'"';
            return '<?php echo '.$sFunc.'('.$sFuncParam.');?>';
        }
        return '<?php echo "'.str_replace('"', "'", $sConstant).'";?>';
    }
    
    
    
    
    
    private function _if($sCondition){
        $aCondition = $this->parseTagAttr($sCondition);
        $sCondition = $aCondition['condition'];
        $sCondition = str_replace(array(' gt ',' ge ',' lt ',' le ',' eq '),array(' > ',' >= ',' < ',' <= ',' == '),$sCondition);
        return "<?php if({$sCondition}):?>";
    }


    private function _elseif($sCondition){
        $aCondition = $this->parseTagAttr($sCondition);
        $sCondition = $aCondition['condition'];
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
        return "<?php \$this->_widget_(\"{$sWidgetTpl}\");?>";
    }


    private function _include_once($sFile){
        $sIncludeFile = $sFile;
        if (strrpos($sIncludeFile, '.',strrpos($sIncludeFile, '/'))!==false){
            if(substr($sIncludeFile, strrpos($sIncludeFile, '.',strrpos($sIncludeFile, '/'))+1) != $this->mTplSuffix)
                $sIncludeFile .= ".{$this->mTplSuffix}";
        }else{
            $sIncludeFile .=".{$this->mTplSuffix}";
        }
        if (!realpath($sIncludeFile)){
            $sIncludeFile = ltrim($sIncludeFile,'/');
            $sIncludeFile = "{$this->mPublicTplPath}{$sIncludeFile}";
        }
        if(\struggle\isFile($sIncludeFile)){
            ob_start();
            include $sIncludeFile;
            $sIncludeCon = ob_get_clean();
            $sFilterIncludeTag = preg_replace('/\{(include|include_once)\s+?[^}]+\}/i', '', $sIncludeCon);
            $sIncludeCon = $this->parse($sFilterIncludeTag);
            return $sIncludeCon;
        }else{
            Debug::trace(__METHOD__."文件不存在或不可读 {$sIncludeFile} line ".__LINE__, Debug::SYS_ERROR);
        }
    }
    
    
    
    /**
     * 模板foreach标签
     * @param string $params   标签条件或参数
     * @return string
     */
    private function _foreach($params){
        $xRlt = array('status'=>true,'msg'=>'');
        //数组变量名
        $sData = '';
        //数组列表名
        $sList = '';
        if ($aAttr = $this->parseTagAttr($params)){
            if (isset($aAttr['sourceBySql']) && $aAttr['sourceBySql']){
                //\struggle\M('Menu')->getSidebarMenus();eval返回null(没有return)，语法错误返回false
                $sEvel = "return {$aAttr['sourceBySql']};";
                $xData = eval($sEvel);
                if ($xData !== false){
                    if (isset($aAttr['name']) && $aAttr['name']){
                        $oCtl = Sle::app()->controller;
                        $oCtl->assgin($aAttr['name'],$xData);
                        //$oCtl->tplData = array_merge($oCtl->tplData,array($aAttr['name']=>$xData));
                    }
                }else{
                    $xRlt['status'] = false;
                    $xRlt['msg'] = 'sourceBySql语法有误'.$aAttr['sourceBySql'].' '.__METHOD__.' line '.__LINE__;
                }
            }
            //数组名称
            if ($xRlt['status'] && isset($aAttr['name']) && $aAttr['name']){
                $sName = '$'.ltrim($aAttr['name'],'$');
            }elseif($xRlt['status']){
                $xRlt['status'] = false;
                $xRlt['msg'] = 'foreach数组名称有误'.var_export($aAttr['name'],true).' '.__METHOD__.' line '.__LINE__;
            }
            //数组索引名
            if ($xRlt['status'] && isset($aAttr['list']) && $aAttr['list']){
                $sList = '$'.str_replace(',', '=>$', ltrim($aAttr['list'],'$'));
            }elseif($xRlt['status']){
                $xRlt['status'] = false;
                $xRlt['msg'] = 'foreach数组列表有误'.$aAttr['list'].' '.__METHOD__.' line '.__LINE__;
            }
        }else{
            $xRlt['status'] = false;
            $xRlt['msg'] = 'foreach参数有误'.$params.' '.__METHOD__.' line '.__LINE__;
        }
        if ($xRlt['status']){
            return '<?php foreach('.$sName.' AS '.$sList.'):?>';
        }else {
            Debug::trace($xRlt['msg'],Debug::SYS_ERROR);
            return '';
        }
    }
    
    private function _close_foreach(){
        return '<?php endforeach;?>';
    }


    /**
	 * 模板标签for
	 * @params array 标签属性
	 * @return string
	 * @example
	 * for($i=0;$i<=count($menuChainInfo);$i+=1)
	 * 
	 * $menuChainInfo = count($menuChainInfo);
	 * {forcount='i,0' logic='le' name='menuChainInfo' sep='1'}
	 * 或
	 * {forcount='i,0' logic='le' name='count($menuChainInfo)' sep='1'}
	 */
	private function _for($params){
		$sFor = '';
		if ($aAttr = $this->parseTagAttr($params)){
			$sCount = 'i';   //计数器变量名
			$iCount = 0;    //计数器出事化值
			$sLogic = 'le';
			$iSep = 1;
			if(!isset($aAttr['name']) || empty($aAttr['name'])) {
				Debug::trace("模板标签for参数有误,name属性为必须".print_r($params,true),Debug::SYS_ERROR);
				return '';
			}
			$sName = (strpos($aAttr['name'],'(')!==false)?$aAttr['name']:'$'.ltrim($aAttr['name'],'$');
			if(isset($aAttr['count']) && $aAttr['count']){
				$aCount = explode(',',$aAttr['count']);
				isset($aCount[0]) && $sCount = $aCount[0];
				isset($aCount[1]) && $iCount = $aCount[1];
			}
			isset($this->mLogicTag[$aAttr['logic']]) && $sLogic = $this->mLogicTag[$aAttr['logic']];
			(isset($aAttr['sep']) && $aAttr['sep']) && $iSep =$aAttr['sep'];
			$sFor = "<?php for(".'$'."{$sCount}={$iCount},".'$'."_tmp_for_limit = {$sName};".'$'."{$sCount}{$sLogic}".'$'."_tmp_for_limit;".'$'."{$sCount}".($iSep>0?'+':'-')."={$iSep}):?>";
		}else{
		    Debug::trace("模板标签for参数有误".print_r($params,true),Debug::SYS_ERROR);
			return '';
		}
		return $sFor;
	}

	private function _close_for(){
		return '<?php endfor;?>';
	}
    
    /**
     * 解析标签属性，使其以数组形式存在
     * @param string $attr
     * @return array  如果发生错误返回空数组
     * @author luguo@139.com
     */
    private function parseTagAttr($attr){
        $xRlt = array('status'=>true,'msg'=>'');
		Debug::trace('解析标签属性'.$attr,Debug::SYS_NOTICE);
        $aAttr = preg_split('/\s+(?=[^=]+?=\s*\'|".+\'|")/i', trim($attr));
		Debug::trace('提取标签属性'.print_r($aAttr,true),Debug::SYS_NOTICE);
        $aReturn = array();
        if (is_array($aAttr) && $aAttr){
            foreach ($aAttr as  $attr){
                preg_match('/^([^=]+)=\s*[\'|"](.+)[\'|"]$/s', $attr,$match);
				Debug::trace('分别提取属性名称和值'.print_r($match,true),Debug::SYS_NOTICE);
                if($xRlt['status'] && $match){
                    $sAttrVal = $match[2];
                    if ($sAttrVal[0] =="'"){
                        $sAttrVal = trim($sAttrVal,"'");
                    }elseif ($sAttrVal[0] == '"'){
                        $sAttrVal = trim($sAttrVal,'"');
                    }
                    $aReturn[$match[1]] = $sAttrVal;
                }else{
                    $xRlt['status'] = false;
                    $xRlt['msg'] = 'foreach参数有误,参数中不能含有空格'.$params.' '.__METHOD__.' line '.__LINE__;
                    $aReturn = array();
                }
            }
        }else{
            $xRlt['status'] = false;
            $xRlt['msg'] = '标签属性有误'.print_r($aAttr,true).' '.__METHOD__.' line '.__LINE__;
        }
		Debug::trace('提取属性名称和值后的数组形式'.print_r($aReturn,true),Debug::SYS_NOTICE);
        return $aReturn;
    }


    private function _include($sFile){
        return "<?php echo \$this->_include_tpl_('{$sFile}');?>";
    }

    /**
     * 动态生成链接
     * @param string $path
     * @return string
     */
    private function _url($path){
        $oRoute = Sle::app()->route;
		return '<?PHP echo "'.$oRoute->genUrl($path).'";?>';
    }
    
    /**
	 * html 功能模板标签，针对html标签属性功能标签
	 * @param string 
	 * @return string
	 * @example
	 * script或link标签属性
	 * {html type/theme:path/file?name=value}
	 */
    private function _html($params){
        $xRlt = array('status'=>true,'msg'=>'');
        $sThemes = 'Default';
        $sPath   =  '';
        $sType   =  '';
        $xReturn = '';
        //截取冒号前面部分
        if ($iThemePos = strpos($params, ':')){
            $sTmpThemes = substr($params, 0,$iThemePos);
            //冒号前面部分，多个元素，以'/'分开，第一个为元素类型，如，css、js第二个为主题，第三个及之后尚未定义
            $aThemes = explode('/', $sTmpThemes);
            if (isset($aThemes[1]) && $aThemes[1]){
                $sThemes = $aThemes[1];
                $sType   = $aThemes[0];
            }else {
                $sType   = $aThemes[0];
            }
        }else {
            $xRlt['status'] = false;
            $xRlt['msg']    = "参数格式错误,{$params}".__METHOD__.' line '.__LINE__;
            Debug::trace($xRlt['msg'], Debug::SYS_ERROR);
        }

		//如果存在问号，取出问号部分
		$sQuesPart = '';
		if(($iQuesPos = strrpos($params,'?'))!==false) {
		    $sQuesPart = substr($params,$iQuesPos+1);
            Debug::trace("问号部分{$sQuesPart}");
			$params = substr($params,0,$iQuesPos);
			$sQuesPart = $this->replaceGlobalConst($sQuesPart);
		    Debug::trace("问号部分替换变量值后{$sQuesPart}");
		}

        //截取冒号后面的字符串
        $sColonAfter = ltrim(substr($params, $iThemePos+1),'/');
        switch (strtolower($sType)){
            case 'css':
                //判断是否包含存放路径
                $sCssPath = dirname(trim($sColonAfter,'/'));
                $sCssFile = '';
                if ($sCssPath == '.'){
                    $sCssFile = $sColonAfter;
                    $sCssPath = '';
                }else {
                    $sCssFile = basename($sColonAfter);
                }
                $sCssPath && $sCssPath .= '/';
                $sCssPath = $this->getHtmlElementPath($sType,$sCssPath,$sThemes);
                if (strtolower(substr($sCssFile, strrpos($sCssFile, '.')+1) != 'css')){
                    $sCssFile .= '.css';
                }
                $sCssFile = $sCssPath.$sCssFile;
                if(!is_file($sCssFile)){
                    $xRlt['status'] = false;
                    $xRlt['msg']    = "文件不存在{$sCssFile}".__METHOD__.' line '.__LINE__;
                    $xReturn = '';
                }else {
                    $xReturn = $sCssFile;
                }
                break;
            case 'js':
                //判断是否包含存放路径
                $sJsPath = dirname(trim($sColonAfter,'/'));
                $sJsFile = '';
                if ($sJsPath == '.'){
                    $sJsFile = $sColonAfter;
                    $sJsPath = '';
                }else {
                    $sJsFile = basename($sColonAfter);
                }
                $sJsPath && $sJsPath .= '/';
                $sJsPath = $this->getHtmlElementPath($sType,$sJsPath,$sThemes);
                if (strtolower(substr($sJsFile, strrpos($sJsFile, '.')+1) != 'js')){
                    $sJsFile .= '.js';
                }
                $sJsFile = $sJsPath.$sJsFile;
                if(!is_file($sJsFile)){
                    $xRlt['status'] = false;
                    $xRlt['msg']    = "文件不存在{$sJsFile}".__METHOD__.' line '.__LINE__;
                    $xReturn = '';
                }else {
                    $xReturn = $sJsFile;
                }
                $xReturn = $sQuesPart?$xReturn."?{$sQuesPart}":$xReturn;
                break;
            case 'image':
                $sType = 'images';
                //判断是否包含存放路径
                $sImagePath = dirname(trim($sColonAfter,'/'));
                $sImageFile = '';
                if ($sImagePath == '.'){
                    $sImageFile = $sColonAfter;
                    $sImagePath = '';
                }else {
                    $sImageFile = basename($sColonAfter);
                }
                $sImagePath && $sImagePath .= '/';
                $sImagePath = $this->getHtmlElementPath($sType,$sImagePath,$sThemes);
                $sImageFile = $sImagePath.$sImageFile;
                if(!is_file($sImageFile)){
                    $xRlt['status'] = false;
                    $xRlt['msg']    = "文件不存在{$sImageFile}".__METHOD__.' line '.__LINE__;
                    $xReturn = '';
                }else {
                    $xReturn = $sImageFile;
                }
                break;
            case 'layout':
                $xReturn = $this->doLayout($sType,$sThemes,$sColonAfter);
                break;
           default:
        }
        if (!$xRlt['status']){
            Debug::trace($xRlt['msg'], Debug::SYS_ERROR);
        }
        return $xReturn;
    }


    /**
	 * 替换字符串中包含的全局常量
	 * @access public
	 * @param string $str
	 * @return string
	 */
	public function replaceGlobalConst($str){
		foreach($this->mConstants as $constant=>$value){
			$str = str_replace($constant,urlencode($value),$str);
		}
		return $str;
	}
    
    /**
     * 组装html相关元素存放路径
     * @access private
     * @param string $element  元素名称  ，如css，js
     * @param string $path     存放的路径
     * @param string $theme    所属主题
     * @return string  成功返回元素路径，失败返回空字符串
     */
    private function getHtmlElementPath($element,$path = '',$theme = 'Default'){
        $sElementPath = APP_PUBLIC;
        $xRlt = array('status'=>true,'msg'=>'');
        if (empty($element)){
            $xRlt['status'] = false;
            $xRlt['msg']    = '元素不能为空 '.__METHOD__.' line '.__LINE__;
        }
        if ($xRlt['status']){
            $sElementPath .= $theme.'/'.$element.'/'.$path;
        }
        if ($xRlt['status'] && !is_dir($sElementPath)){
            $xRlt['status'] = false;
            $xRlt['msg']    = '元素路径不正确{$sElementPath} '.__METHOD__.' line '.__LINE__;
        }
        
        if ($xRlt['status']){
            return $sElementPath;
        }else{
            Debug::trace($xRlt['msg'],Debug::SYS_ERROR);
            return '';
        }
    }
    
    /**
     * 处理布局相关标签
     * @param string  $type   元素类型
     * @param string  $theme  所属主题
     * @param string  $params  参数，以'/'隔开
     * @return string  mixed
     * @author luguo@139.com
     */
    private function doLayout($type,$theme,$params){
        $aParam = explode('/', $params);
        $aData  = array();
        $xReturn = '';
        //把参数组装成关联数组形式
        for ($i=0;$i<count($aParam);$i+=2){
            $aData[$aParam[$i]] = isset($aParam[$i+1])?$aParam[$i+1]:'';
        }
        isset($aData['default']) && $xReturn = $aData['default'];
        //处理参数，格式name=value;多个参数由'&'符号隔开
        if (isset($aData['param']) && $aData['param']){
            $aMethodParam = explode('&', $aData['param']);
            $aData['param'] = array();
            for ($i=0;$i<count($aMethodParam);$i++){
                $aTmp = explode('=', $aMethodParam[$i]);
                $aData['param'][$aTmp[0]] = isset($aTmp[1])?$aTmp[1]:'';
            }
        }
        
        //调用某个类的方法，如果有，否则返回默认值(default)
        if (isset($aData['model'])){
            if ($oModel = sle\M(sle\ctop($aData['model']))){
                if (isset($aData['method']) && method_exists($oModel,$aData['method'])){
                    $sMethodName =  $aData['method'];
                    $aMethodParam = isset($aData['param'])?$aData['param']:array();
                    $xReturn = $oModel->$sMethodName($aMethodParam);
                }
            }
        }
        return $xReturn;
        
        
    }
    
    
    
    /**
     * 获取配置变量  TODO;
     * @param unknown $param
     * @return unknown
     */
    private function _config($param){
        return $param;
    }
    
    



}










