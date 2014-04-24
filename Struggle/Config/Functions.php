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
 * 建立目录
 * @param string       $sDirPath   目录路径
 * @param integer      $iModel     建立该目录的模式，默认0755
 */
function buildDir($sDirPath, $iModel = 0755){
    if (is_dir($sDirPath)){
        return true;
    }else {
        if (is_writeable(dirname($sDirPath))){
            return @mkdir($sDirPath,$iModel);
        }
    }
    return false;
}



/**
 * 读入配置函数
 * @param string $sName  配置名称
 * @param mix    $mVal    配置值
 */
function C($sName, $mVal = null){
    static $aConfig=array();
    if (is_string($sName)){
        if (!strpos($sName, '.')){
            $sName = strtolower($sName);
            if (is_null($mVal)) //不能用empty,否则不能把值设为空
                return isset($aConfig[$sName])?$aConfig[$sName]:null;
            $aConfig[$sName] = $mVal;
        }
    }
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
 * 名称命名转换
 * @param string       $sName   需要转换的名称
 * @param integer      $iStyle  要转换的方法，0为下划线命名法、1为帕斯卡命名法、2为骆驼命名法、3为匈牙利命名法
 * @return Boolean     true成功false失败
 */
function cname($sName,$iStyle = 0){
    $sRlt='';
    if (is_numeric($iStyle) && $iStyle == 0){
        $sName=preg_replace('/([A-Z])/', create_function('&$k', 'echo $k;'), $sName);
    }
    return $sName;
}

/**
 * 自动加载处理函数
 */
function autoLoad($sName){
    static $aIncludeFile=array();include '';
    $sKey = md5($sName);
    if (!isset($aIncludeFile[$sKey])){
    	$sName = str_replace(array('\\','/'), DIRECTORY_SEPARATOR, $sName);
        $sFileName = basename($sName);
        $sFilePath = dirname($sName);
        $sFile = $sFileName.'.php';
        include $sFile;
    }
}




