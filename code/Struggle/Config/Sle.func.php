<?php
namespace Struggle;

/*
 * 框架核心函数
 * 
 */

/**
 * 包含文件，区分大小写
 * @param string $file  文件名，区分大写(传入文件须存在，否则导入失败)
 * @return boolean
 */
function require_cache($file){
	$file = str_replace('\\','/',$file);
	//提取类名，必须严格按照文件命名规则
	$sFileName = basename($file);
	$aFileName = explode('.',$sFileName);
	$sClassName = $aFileName[0];
    static $aFiles=array();
    $sKey=md5($file);
    if (!isset($aFiles[$sKey])){
		$aFiles[$sKey] = false;
        if (isFile($file)){
			require $file;
			if(strtolower($aFileName[1]) == 'class'){
				$sFilePath = realpath($file);
				$sBasePath = realpath(SLE_PATH);
				if(strpos($sFilePath,$sBasePath)!==false){
					$sNamespace = basename(SLE_PATH);
					$sNamespace .= str_replace($sBasePath,'',$sFilePath);
				}
			}
			$aFiles[$sKey] = true;
        }
    }
    return $aFiles[$sKey];
}


/**
 * 导入文件,格式sp.name.name1.。。。
 * sp为特殊标识符,当为@时表示框架库目录为起始位置;
 *               当为Sle时入口文件目录为起始位置;
 *               当为空值时项目库目录为起始位置
 * @param string $name 导入名称，以'.'分割
 * @return boolean|string  成功返回文件名，失败返回false
 */
function import($name){
    static $aInclude = array();
	$sName = $name;
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
    }
    $sName = $sBasePath.$sName.'.php';
    $sKey = md5($sName);
    
    if (!isset($aInclude[$sKey])){
        $aInclude[$sKey] = false;
        if (isFile($sName)){
            require_cache($sName) && $aInclude[$sKey] = $sName;
        }else{
            try {
                throw new \Exception("文件不存在或该文件没有读权限,{$sName}");
            }catch (\Exception $e){
                echo "异常错误: {$e->getMessage()}  {$e->getFile()} 第{$e->getLine()}行";
            }
        }
    }
    return $aInclude[$sKey];
}


/**
 * 根据类文件获取类名，文件名严格按照文件起名规范命名(ClassName.suffix.php)
 * @param string $classFile  类文件
 * @param boolean $suffix     是否包含后缀，默认包含
 * @return string   返回类名或空字符串
 * @example 如果类文件名为ClassName.suffix.php ,则类名为ClassNameSuffix
 */
function fetchClassName($classFile,$suffix = true){
	$sClassName = '';
	if(isFile($classFile)){
		//非windows 下'\'basename不起作用
		strpos($classFile,'\\')!==false && $classFile = str_replace('\\','/',$classFile);
		$sClassName = basename($classFile);
		if(strpos($sClassName,'.')!==false){
			if($suffix){
		        $sClassName = substr($sClassName,0,strrpos($sClassName,'.'));
			}else{
		        $sClassName = substr($sClassName,0,strpos($sClassName,'.'));
			}
		}else{
			$sClassName = substr($sClassName,0);
		}
		$sClassName = trim($sClassName);
	}
	return $sClassName;
}




/**
 * 获取文件中首次发现的命名空间，搜寻从上至下
 * @param string $file
 * @return string
 */
function fetchNamespace($file){
    static $aNamespace = array();
    $sKey = md5($file);
    if(isset($aNamespace[$sKey]))
        return $aNamespace[$sKey];
    $sNamespace = '';
    $hd = fopen($file, 'rb');
    while ($row = fgets($hd)){
        if (preg_match('/(?<=namespace)([^;]+)/i', $row,$matchs)){
            $sNamespace = trim($matchs[1]);
            break;
        }
    }
    fclose($hd);
    return $sNamespace;
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
 * 部署项目目录
 * @param array $dir
 */
function buildAppDir($dir){
    try {
        if (!is_array($dir))
            throw new \Exception('参数不能为非数组形式'.print_r($dir,true));
    }catch (\Exception $e){
        halt("异常错误: {$e->getMessage()}  {$e->getFile()} 第{$e->getLine()}行");
    }
    foreach($dir as $value){
        try {
            if (!buildDir($value))
                throw new \Exception('目录建立失败'.print_r($value,true));
        }catch (\Exception $e){
            halt("异常错误: {$e->getMessage()}  {$e->getFile()} 第{$e->getLine()}行");
        }
        
    }
    return true;
}



/**
 * 停止执行
 * @param string $message
 */
function halt($message){
    echo $message;
    exit;
}




/**
 * 设置配置文件
 * @param $file  配置文件
 * @param $isBuild 如果配置文件不存在，是否建立
 * @return boolean
 */
 function setConfig($file,$isBuild = false){
	 $aConfig = chkConfigFile($file,'config',$isBuild);
	 if($aConfig !== false){
		 foreach($aConfig as $key=>$value){
			 C($key,$value);
		 }
		 return true;
	 }
	 return false;
 }




/**
 * 设置语言配置
 * @param $file  语言文件
 * @param $isBuild 如果语言文件不存在，是否建立
 * @return boolean
 */
 function setLangConfig($file,$isBuild = false){
	 $aLang = chkConfigFile($file,'lang',$isBuild);
	 if($aLang !== false){
		 foreach($aLang as $key=>$value){
			 L($key,$value);
		 }
		 return true;
	 }
	 return false;
 }


/**
 * 检查配置文件
 * @param $file  文件
 * @param $type 文件类型，如，config或lang，扩展字段
 * @param $isBuild 文件不存在是否建立
 * @return array|boolean
 */
 function chkConfigFile($file,$type,$isBuild){
	 if(!file_exists($file) || basename($file) != basename(realpath($file))){
		 if($isBuild){
			$sPath = dirname($file);
			if (is_writeable($sPath)){
				$hdFile = fopen($file, 'wb+');
				fwrite($hdFile, "<?php\r\n//".($type=='lang'?'语言':'')."配置文件\r\nreturn array(\r\n);");
				fclose($hdFile);
				return true;
			}
		 }
		 return false;
	 }
	 if(!is_readable($file)) return false;
	 $aConfig = include $file;
	 if(!is_array($aConfig)){
		 return false;
	 }
	 return $aConfig;
 }




/**
 * 设置/获取配置函数
 * @param string $name     配置名称
 * @param mix    $value    配置值
 * @return mix|void
 */
function C($name, $value = null){
    static $aConfig=array();
	if(is_null($value)){
	    return $aConfig[strtolower($name)];
	}
	readConf($name, $value, $aConfig);
}






/**
 * 设置/获取语言函数
 * @param string $name    名称
 * @param mix    $value    值
 * @return mix|void
 */
function L($name, $value = null){
    static $aLang=array();
	if(is_null($value)){
		$aKey = array();
		$sMultiKeyLan = '';
		if(strpos($name,'.')!==false){
			$aKey = explode('.',$name);
		}

		foreach($aKey as $key){
			if($sMultiKeyLan)
				$sMultiKeyLan = $sMultiKeyLan[$key];
			else
				$sMultiKeyLan = $aLang[$key];
		}
		if($sMultiKeyLan)
			return $sMultiKeyLan;
	    return $aLang[strtolower($name)];
	}
	readConf($name, $value, $aLang);
}



/**
 * 读入配置函数
 * @param string   $key    设置配置名称
 * @param mix      $value  设置配置值
 * @param array    $var    存放对象
 * @param return    void
 */
function readConf($key, $value, & $var){
	$sKey = $key;
    if (is_string($sKey)){
        $sKey = strtolower($sKey);
        if (!strpos($sKey, '.')){
            $var[$sKey] = $value;
        }else{
            $mTmpRlt = strToArrElement($sKey, $value, $var);
        }
    }
}





/**
 * 字符串点格式转换成数组元素
 * (利用json建立动态数组)
 * @param string $sName      数组键名,  如key1.key2...
 * @param mixed  $mVal       键名对应的值
 * @param Array  $aAppend  & 插入的目标数组(引用类型)
 * @return  mixed  $mVal为null时成功返回对应的值，失败返回null;$mVal不为null时，成功返回true失败返回false
 */
function strToArrElement($sName, $mVal, &$aAppend){
    $mRlt = false;
    is_null($mVal) && $mRlt = null;
    if (strpos($sName, '.') === false || !is_array($aAppend))
        return $mRlt;
    $aName = explode('.', $sName);
    $temp = '';
    $bExist = false;
    for ($i=0;$i < count($aName);$i++){
        if ($i == 0){
            if (isset($aAppend[$aName[$i]]))
               $temp = $aAppend[$aName[$i]];
            else 
                break;
        }else{
            if (isset($temp[$aName[$i]]))
                $temp = $temp[$aName[$i]];
            else
                break;                    
        }
        if ($i+1 == count($aName))
            $bExist = true;
    }
    
    if (is_null($mVal)){
        if ($bExist)
            return $temp;
        $mRlt = null;
    }else{
		$mVal = addslashes($mVal); 
        $sArrKey = '';
        $sCloseTag = '';
        foreach ($aName as $key){
            $sArrKey .= '{"'.$key.'":';
            $sCloseTag .= '}';
        }
        $sTar = "{$sArrKey}\"{$mVal}\"{$sCloseTag}";
        $aTar = json_decode($sTar,true);
        if(isset($aAppend[\key($aTar)])){
            $aOrg[\key($aTar)]=$aAppend[\key($aTar)];
            $sOrg=json_encode($aOrg);
            $tarKeys='';
            do{
                $tarKeys[]=\key($aOrg);
                $aOrg=array_shift($aOrg);
            }while(is_array($aOrg));

            //定位数组维度
            $iPos= 1;
            foreach($aName as $index=>$name){
                if(isset($tarKeys[$index]) && $tarKeys[$index] == $name)$iPos+=1;
            }
            //定位json中的插入点
            $iPos2=-1;
			$iDeep2=$iPos;
			$isDropWrap=true;
			if((count($tarKeys) == ($iPos-1)) or (count($aName) == ($iPos-1))){
				$iDeep2 -= 1;
				$isDropWrap=false;
			}
            for($i=0;$i<$iDeep2;$i++){
                $iPos2=strpos($sOrg,'{',$iPos2+1);
            }
            //去掉数组重复的维度
            $iDeep=$iPos;
            while(($iDeep-1)>0){
                $aTar = array_shift($aTar);
                $iDeep-=1;
            }
            if(count($aName) == ($iPos-1)){
                $iLastKeyPos=strpos($sOrg,end($aName),$iPos2+1);//定位最后一维 
                $iColonPos = strpos($sOrg,':',$iLastKeyPos+1);//定位冒号
                $isArr = $sOrg[$iColonPos+1];//是否覆盖数组
                if($isArr == '{'){
                    $sPart1=substr($sOrg,0,$iColonPos+1);//截取到左大括号{
				    $iPosPart2=$iColonPos+1;
					for($ii=1;$ii<$iPos;$ii++){
                        $iPosPart2=strpos($sOrg,'}',$iPosPart2+1);
					}
                    $sPart2=substr($sOrg,$iPosPart2);
                }else{
                    $sPart1=substr($sOrg,0,$iColonPos+1);//截取到冒号
                    if($iPosPart2=strpos(substr($sOrg,0,strpos($sOrg,'}',$iColonPos)),',',$iColonPos)){
                        $sPart2=substr($sOrg,$iPosPart2);
                    }else{
                        $iPosPart2=strpos($sOrg,'}',$iColonPos+2);
                        $sPart2=substr($sOrg,$iPosPart2);
                    }
                }
            }else{
				if(count($tarKeys) == ($iPos-1)){
					$iLastKeyPos=strpos($sOrg,end($tarKeys),$iPos2+1);//定位最后一维
					$iColonPos = strpos($sOrg,':',$iLastKeyPos+1);//定位冒号
                    $sPart1=substr($sOrg,0,$iColonPos+1);//截取到冒号
                    if($iPosPart2=strpos(substr($sOrg,0,strpos($sOrg,'}',$iColonPos)),',',$iColonPos)){
                        $sPart2=substr($sOrg,$iPosPart2);
                    }else{
                        $iPosPart2=strpos($sOrg,'}',$iColonPos+2);
                        $sPart2=substr($sOrg,$iPosPart2);
                    }
				}else{
				$sPart2=substr($sOrg,$iPos2+1);
				$sPart1=str_replace($sPart2,'',$sOrg);
				}
            }
			$sAddJson = json_encode($aTar);
			if($isDropWrap)
			    $sAddJson = substr($sAddJson,1,(strlen($sAddJson)-2));
            $sRlt= $sPart1.$sAddJson.($sPart2[0]=='}'?'':',').$sPart2;
			//echo $sRlt;
            $aRlt=json_decode($sRlt,true);
            $aAppend[\key($aRlt)]=\current($aRlt);
        }else{
            $aAppend[\key($aTar)] = \current($aTar);
        }
        $mRlt = true;
    }
    return $mRlt;
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


/**
 * 获取指定目录下所有目录
 * @param string $dir 指定目录
 * @return array
 */
function fetchDirs($dir){
    $aRlt=array();
    if (is_dir($dir)){
        $hdDir = dir($dir);
        while ($sSubDir = $hdDir->read()){
			//刷选目录，过滤目录下文件
            if (!is_file($dir.'/'.$sSubDir) && !in_array($sSubDir, array('.','..'))){
                $aRlt[]=rtrim($dir,'/').'/'.$sSubDir.'/';
                $aRlt = array_merge($aRlt,fetchDirs(rtrim($dir,'/').'/'.$sSubDir));
            }
        }
        $hdDir->close();
    }
    return $aRlt;
}




/**
 * 模型实例函数
 * @param string $sName 模型名称
 * @return mixed 成功返回resource 或 失败 返回 null
*/
function M($name = ''){
	static $aModel = array();
	$sKey = md5($name);
	if(empty($name)){
		$sClassName ='\Struggle\Libraries\Core\Model';
		$aModel[$sKey] = new $sClassName;
	}
	if(!isset($aModel[$sKey])){
		$sModelSuffix = '.class.php';
		$sModelFile = APP_MODEL."{$name}.class.php";
		if(require_cache($sModelFile)){
			$sClassName = "\Model\\".$name;
			if(class_exists($sClassName)){
				$oModel =  new $sClassName();
				$aModel[$sKey] = $oModel;
			}
		}
	}
	return isset($aModel[$sKey])?$aModel[$sKey]:null;
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
 * 自动加载类处理函数
 */
function autoLoad($name){
	static $aInclude = array();
	$sClassFileSuffix = '.class.php';
	$sKey = md5($name);
	if(isset($aInclude[$sKey])){
		return $aInclude[$sKey];
	}
	//包含文件
	$sFile = $name;
	strpos($sFile,'\\')!==false && $sFile = str_replace('\\','/',$sFile);
	if(strtolower(substr(trim($sFile,'/'),0,strpos($sFile,'/'))) == 'struggle'){
        $sLoadPath = dirname(realpath(SLE_PATH));
    }else{
        $sLoadPath = dirname(realpath(APP_PATH));
    }
    $sFile = rtrim($sLoadPath,'/').'/'.ltrim($sFile,'/').$sClassFileSuffix;
	try{
		//include 失败返回false并发警告,成功返回1，除非包含文件有return
		if (is_file($sFile)){
			require_cache($sFile);
        }else{
            debug_print_backtrace();
            throw new \Exception("找不到文件{$sFile}");
		}
	}catch(\Exception $e){
		halt("异常错误: {$e->getMessage()}  {$e->getFile()} 第{$e->getLine()}行");
	}
}


/**
 * 检查文件是否存在,且可读,区分大小写
 * @param string $file 文件名
 * @return boolean
 */
function isFile($file){
    if (is_string($file)){
        if (file_exists($file) && (basename($file) == basename(realpath($file))) && is_readable($file)) {
            return true;
        }
    }
    return false;
}


function isResource ($res) {
    return !is_null(@get_resource_type($res)); 
}




/**
 * 异或加/解密算法(不同语言也可)
 * @param $file  需要加密或解密的文件
 * @param $key  需要异或的值(加/解密key)，必须十进制
 * @param $new  非空时为文件名，即另生成一个文件;为空时返回加/解密后字符串
 * @return string
 * @author luguo@139.com 
 */
function fileXor($file,$key,$new=''){
	$sFile = $file;
	$sKey  = $key;
	$str   = '';
	$sNewFile = $new;
    if($hanldle=fopen($sFile,"rb")) {
		while(!feof($hanldle)) {
			$bCon= fread($hanldle,1); //读文件 读取
			//不能直接从二进制转成十进制，要先从二进制转成十六进制，再从十六进制转成十进制，再用chr转成字符串
			$bCon=hexdec(bin2hex($bCon));
            $str .= chr( $bCon ^ $sKey);   // 读取个8位，并进行惑运算
	    }
		fclose($hanldle) ; // 关闭资源  $filebinary
    }
	if($sNewFile)
        return file_put_contents($sNewFile,$str)?'1':'';
	return $str;
}
















