<?php
/*
 * 全局函数
 */

function require_cache($sName){
    static $aFiles=array();
    $sKey=md5($sName);
    if (!isset($aFiles[$sKey])){
        if (!file_exists($sName) || !is_readable($sName)){
            trace("该文件不存在或不可读,{$sName}", E_USER_ERROR);
        }
        if (IS_WIN && basename(realpath($sName)) == basename($sName)){
            include $sName;
            $aFiles[$sKey] = true;
        }else {
            $aFiles[$sKey] = false;
            if (!IS_WIN){
                include $sName;
                $aFiles[$sKey] = true;
            }
        }
    }
    return $aFiles[$sKey];
}

/**
 * 导入文件,格式sp.name.name1.。。。
 * sp为特殊标识符,当为@时表示框架库目录为起始位置;
 *               当为Sle时入口文件目录为起始位置;
 *               当为空值时项目库目录为起始位置
 */
function import($sName){
    static $aInclude = array();
    $sBasePath = APP_LIB;
    if(($iPos=strpos($sName, '.')) !==false){
        $sPre=substr($sName, 0,$iPos);
        if ($sPre == '@'){
            $sBasePath = LIB_PATH;
        }elseif ($sPre == 'Sle'){
            $sBasePath = APP_ROOT;
        }
        $sName = substr($sName, $iPos+1);
        $sName = implode('/', explode('.', $sName));
        $sName = $sBasePath.$sName.'.php';
    }
    $sName = $sBasePath.$sName.'.php';
    $sKey = md5($sName);
    
    if (!isset($aInclude[$sKey])){
        $aInclude[$sKey] = false;
        if (file_exists($sName) && is_readable($sName)){
            $aInclude[$sKey] = include $sName;
        }else{
            trace("文件不存在或该文件没有读权限,{$sName}", E_USER_ERROR);
        }
    }
    return $aInclude[$sKey];
}

/**
 * 规范命名
 * @param string $sName
 * @param string $sType   0、java转c风格(下划线); 1、c转java风格(驼峰命名)
 */
function parse_name($sName, $sType = 0){
    if ($sType){
        $sName = ucfirst(preg_replace_callback('@_([a-zA-Z])@', function($sRes){return strtoupper($sRes[1]);}, $sName));
    }else{
        $sName = strtolower(trim(preg_replace('@[A-Z]@', "_\\0", $sName),'_'));
    }
    return $sName;
}


function redirect($sTargetUrl, $sInfo='', $iTime = 3){
    if ($iTime){
    }else{
        
    }
}




function getLibDir($sDir){
    $aRlt=array();
    if (is_dir($sDir)){
        $hdDir = dir($sDir);
        while ($sIsDir = $hdDir->read()){
            if (!is_file($sDir.'/'.$sIsDir) && !in_array($sIsDir, array('.','..'))){
                $aRlt[]=rtrim($sDir,'/').'/'.$sIsDir;
                $aRlt = array_merge($aRlt,getLibDir(rtrim($sDir,'/').'/'.$sIsDir.'/'));
            }
        }
        $hdDir->close();
    }
    return $aRlt;
}


/**
 * 设置自动包含路径
 */
function setIncludePath(){
    $sPath = '';
    $sPath = '.';
    $sLibPath = realpath(LIB_PATH);
    $aAppendPath = getLibDir($sLibPath);
    $sPath = $sPath.PATH_SEPARATOR.rtrim($sLibPath,'/').'/'.PATH_SEPARATOR.implode(DIRECTORY_SEPARATOR.PATH_SEPARATOR, $aAppendPath);
    set_include_path($sPath);
}

/**
 * 自动加载处理函数
 */
function autoLoad($sName){
    static $aIncludeFile=array();
    $sKey = md5($sName);
    if (!isset($aIncludeFile[$sKey])){
    	$sName = str_replace(array('\\','/'), DIRECTORY_SEPARATOR, $sName);
        $sFileName = basename($sName);
        $sFilePath = dirname($sName);
        $sFile = $sFileName.'.php';
        include $sFile;
        struggle\trace("自动加载文件 {$sFile}", E_USER_NOTICE);
    }
}




