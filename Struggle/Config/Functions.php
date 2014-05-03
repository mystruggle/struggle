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
                return $aConfig[$sName];
            $aConfig[$sName] = $mVal;
        }else{
            $aName = explode('.', $sName);
            $sArrName = '$aConfig';
            $sArr = '';
            for ($i=0;$i<count($aName);$i++){
                $aName[$i] = strtolower($aName[$i]);
                $sArr .="['{$aName[$i]}']";
            }
            if (is_null($mVal)){
                return eval('return '.$sArrName.$sArr.';');
            }else{
                eval($sArrName.$sArr.'='.(is_string($mVal)?"'{$mVal}'":$mVal).';');
            }
        }
    }
}


/**
 * 言语配置函数
 * @param string $sName  配置名称
 * @param mix    $mVal    配置值
 */
function L($sName, $mVal = null){
    static $aLang=array();
    if (is_string($sName)){
        if (!strpos($sName, '.')){
            $sName = strtolower($sName);
            if (is_null($mVal)) //不能用empty,否则不能把值设为空
                return $aLang[$sName];
            $aLang[$sName] = $mVal;
        }else{
            $aName = explode('.', $sName);
            $sArrName = '$aConfig';
            $sArr = '';
            for ($i=0;$i<count($aName);$i++){
                $aName[$i] = strtolower($aName[$i]);
                $sArr .="['{$aName[$i]}']";
            }
            if (is_null($mVal)){
                return eval('return '.$sArrName.$sArr.';');
            }else{
                eval($sArrName.$sArr.'='.(is_string($mVal)?"'{$mVal}'":$mVal).';');
            }
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
 * @param  string       $sName    需要转换的名称
 * @param  integer      $iTarget  转换的命名法，0为下划线命名法(默认)、1为帕斯卡命名法、2为骆驼命名法、3为匈牙利命名法
 * @param  integer      $iSource  被转换的命名法，0为下划线命名法、1为帕斯卡命名法(默认)、2为骆驼命名法、3为匈牙利命名法
 * @return string       成功返回转换后的字符，失败原文返回
 */
function cname($sName,$iTarget = 0, $iSource = 1){
    if (is_numeric($iTarget) && is_numeric($iSource)){
        if ($iTarget == 0  && $iSource == 1){
            $sName=trim(preg_replace_callback('/([A-Z])/', create_function('$a', 'return \'_\'.strtolower($a[1]);'), $sName),'_');
        }
        if ($iTarget == 1 && $iSource == 0){
            $sName = ucfirst(preg_replace_callback('/_([a-z])/',create_function('$a', 'return strtoupper($a[1]);'), $sName));
        }
    }
    
    return $sName;
}

/**
 * PASCAL命名转换C命名
 * @param string $sName   需要转换的字符串
 * @return string              成功返回转换后的字符，失败原文返回
 */
function ptoc($sName){
    return cname($sName);
}

/**
 * C命名转换PASCAL命名
 * @param  string     $sName   需要转换的字符串
 * @return string              成功返回转换后的字符，失败原文返回
 */
function ctop($sName){
    return cname($sName, 1, 0);
}



/**
 * 自动加载处理函数
 */
function autoLoad($sName){
    static $aIncludeFile=array();
    $sKey = md5($sName);
    if (!isset($aIncludeFile[$sKey])){
    	$sName = str_replace(array('\\','/'), DIRECTORY_SEPARATOR, $sName);
        $sFileName = basename($sName).'.php';
        \struggle\Sle::getInstance()->hasInfo("自动加载文件{$sFileName}",E_USER_NOTICE);
        include $sFileName;
    }
}




