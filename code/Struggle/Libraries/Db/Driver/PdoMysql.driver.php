<?php
namespace struggle\libraries\db\driver;
use struggle as sle;
/**
 * 模型运用例子
 * name='sys'
 * array('name'=>'sys')
 *
 * name>'sys'
 * array('`name`.gt'=>'sys')
 * 
 * (name='sys' and pwd>=1) or (desc<=2 and create_time in(2,3) )
 * array(array('name'=>'sys','`pwd`.ge'=>1),'_logic'=>'or',array('`desc`.le'=>2,'`create_time`.in'=>array(2,3)))
 *
 * $sys='sys';
 * $pwd=1;
 * $desc='2&#123;';
 * $create_time=array(2,3);
 * (name='sys' and pwd>=1) or (desc<='2{' and create_time in(2,3) )
 * (`name`={{$sys}} and pwd>={{$pwd}}) or(`desc`<={{$desc}} and create_time in ({{$create_time}}))
 *
 * $this->bindValue(array('name'=>'sys','pwd'=>1,':a'=>'2{',':b'=>2,':c'=>3));
 * (name='sys' and pwd>=1) or (desc<='2{' and create_time in(2,3) )
 * (name=:name and pwd >=:pwd) or (desc<=:a and create_time in(:b,:c))
 *
 * $this->bindParam(array('sys',1,'2{',2,3));
 * (name='sys' and pwd>=1) or (desc<='2{' and create_time in(2,3) )
 * (name=? and pwd >=?) or (desc<=? and create_time in(?,?))
 */



class PdoMysqlDriver extends \struggle\libraries\db\Db{
	protected $mLink = null;
    protected $mErrorInfo = '';
    protected $mErrorCode = '';
    protected $mConnTimeOut = 30;  //连接超时
    protected $mConnErrMode = \PDO::ERRMODE_WARNING;  //错误报告模式ERRMODE_EXCEPTION
    protected $mTableInfo   = array(); //表结构信息
    protected $mTableFullName = '';
    protected $mAlias         = '';
    protected $mSelectInfo    = array();
    protected $mBindParam     = array();  //绑定的参数
	private   $mPdoStatement  = null;
	protected $mFetchMode     = \PDO::FETCH_BOTH;
	private   $mFieldRegexp      = '/`([^`]+)`(?:\.(gt|lt|ge|le|eq|neq|in|link|notin)$)?/i';
	private   $mFieldExpMap   = array("gt"=>">","lt"=>"<","ge"=>">=","le"=>"<=","eq"=>"=");

    public function __construct($aOpt){
        parent::__construct();
        $this->connect($aOpt);
    }

    public function _init(){
    }
	public function connect($aOpt){
		static $oLink = null;
        if(!$oLink){
            $sConnCharset= "set character_set_client={$aOpt['charset']},character_set_connection={$aOpt['charset']},character_set_results=binary";
            $sDns = "{$aOpt['driver']}:host={$aOpt['host']};port={$aOpt['port']};dbname={$aOpt['dbname']}";
            $oLink = new \PDO($sDns,$aOpt['user'],$aOpt['pwd'],array(\PDO::ATTR_ERRMODE=>$this->mConnErrMode,
                                                                     \PDO::ATTR_TIMEOUT=>$this->mConnTimeOut,
                                                                     \PDO::MYSQL_ATTR_INIT_COMMAND=>$sConnCharset));
//            $oLink->exec('set names utf8');
            $this->mErrorInfo = $oLink->errorInfo();
            $this->mErrorCode = $oLink->errorCode();
        }
        return $this->mLink = $oLink;
	}

	public function initTableMetadata($sTab,$sAlias){
		static $aMetadata;
		$sKey = md5("{$sTab}.{$sAlias}");
		if(!isset($aMetadata[$sKey])){
			$sMetaSql = "desc {$sTab}";
			$oStatement = $this->mLink->query($sMetaSql);
			$aTmp=$oStatement->fetchAll(\PDO::FETCH_ASSOC);
            $aTableInfo=array();
			foreach($aTmp as $info){
                $sField = $info['Field'];
                unset($info['Field']);
                $aTableInfo[$sField] = $info;
            }
            $this->mTableFullName = $sTab;
            $this->mAlias         = $sAlias;
            $aMetadata[$sKey] = $aTableInfo;
		}
        $this->mTableInfo[$sTab] = $aMetadata[$sKey];
	}


	public function setAttr($sName,$mVal){
		return $this->mLink->setAttribute($sName,$mVal);
	}

    public function getAttr($sName){
        return $this->mLink->getAttribute($sName);
    }

    public function find($aOpt = array()){
        $this->parseOpt($aOpt);
		$this->prepare($this->buildSelect());
		$this->_beginBind();
		$this->execute();
		//$this->fetchAll();
		var_dump($this->fetch(),$this->mErrorInfo);
		//return $this->fetch();
    }

	private function _beginBind(){
		foreach($this->mBindParam as $name=>$value){echo $name.'|<br>';
            $sField = substr(str_replace('`','',$name),1);
			if(!isset($this->mTableInfo[$this->mTableFullName][$sField]['Type'])){
                preg_match('#`([^`]+?)`[^`]+?(?='.$name.')#',$this->mSelectInfo['where'],$arr);
                $sField = $arr[1];
                if(!isset($this->mTableInfo[$this->mTableFullName][$sField]['Type']))
				    throw new \Exception("字段{$sField}不存在!");
			}
			$sFieldType = $this->mTableInfo[$this->mTableFullName][$sField]['Type'];
			$sFieldType = strtolower(substr($sFieldType,0,strpos($sFieldType,'(')));
			$aIntegerType = array('tinyint','smallint','mediumint','int','integer','bigint','float','double','decimal','numeric','bit');
			$iDataType = \PDO::PARAM_STR;
			if(in_array($sFieldType,$aIntegerType)){
				$iDataType = \PDO::	PARAM_INT;
			}
			$this->mPdoStatement->bindValue($name,$value,$iDataType);
		}
		$this->debug("SQL绑定参数:".print_r($this->mBindParam,true),E_USER_NOTICE,sle\Sle::SLE_SYS);
		//每次查询后清空绑定的参数
		$this->mBindParam = array();
	}

	private function buildSelect(){
		$sField = $this->mSelectInfo['field'];
		$sWhere = $this->mSelectInfo['where'];
		$sTable = $this->mTableFullName;
		$sAlias = $this->mAlias;
		$sJoin  = $this->mSelectInfo['join'];
		$sGroupby = $this->mSelectInfo['groupby'];
		$sHaving  = $this->mSelectInfo['having'];
		$sOrderby = $this->mSelectInfo['orderby'];
		$sLimit   = $this->mSelectInfo['limit'];
		$sSql = "SELECT {$sField} FROM {$sTable} AS {$sAlias} {$sJoin} {$sWhere} {$sGroupby} {$sHaving} {$sOrderby} {$sLimit}";
		$this->debug("SQL语句拼接:{$sSql}",E_USER_NOTICE,sle\Sle::SLE_SYS);
		return $sSql;
	}


    /**
	 * 解析查询语句各部分
	 * 将会调用传递过来的参数以其键名命名的方法
	 * 如，_field,_join,_where,_groupby,_having,_orderby,_limit
	 * @param array $aOpt  传递的参数
	 * @return void
	 * @author luguo<luguo@139.com>
	 */
    private function parseOpt($aOpt){
        if(is_array($aOpt)){
            foreach($aOpt as $name=>$param){
                $sMethodName = "_{$name}";
                if(method_exists($this,$sMethodName)){
                    $this->$sMethodName($param);
                }
            }
        }
		//获取列处理
        if(!isset($this->mSelectInfo['field']) || empty($this->mSelectInfo['field'])){
            $this->mSelectInfo['field'] = "*";
        }
		$this->debug("select 语句参数信息 : ".print_r($this->mSelectInfo,true).__METHOD__.' line '.__LINE__,E_USER_NOTICE,sle\Sle::SLE_SYS);
    }



    private function _field($sField){
		$aParams = array();
        if(is_string($sField)){
			$aField = explode(',',$sField);
			foreach($aField as $index=>$field){
				preg_match('/\s+as\s+/i',$field,$arr);
				if(count($arr)){
					$aField[$index] = "{$this->mSelectInfo['join']['alias']}.{$field}";
				}else{
					$aField[$index] = "{$this->mAlias}.{$field}";
				}
			}
            $sField = implode(',',$aField);
            $this->mSelectInfo['field'] = $sField;
        }else
            return false;

    }

    private function _join($param){
		$sJoin = '';
		if($param){
		}
		$this->mSelectInfo['join'] = $sJoin;
        //print_r($param);
    }

    /**
	 * 解析WHERE条件语句参数，由parseOpt方法调用
	 * 不需要bind(自动绑定类型)
	 *   数组类型, 键名中使用``(标注表字段),ge(>=),in,like等等
	 *   字符串类型，使用``标记表字段，{{}}标记值
	 * 需要bind(自动判断类型绑定)
	 *   数组类型，主要是``标记表字段，占位符不含有表字段时
	 *   字符串类型，无
	 * 最后把所有没有占位符的自动添加占位符，格式如:name，然后预处理再执行操作
	 * @param mixed $param  传入的参数 
	 * @return void
	 * @author luguo<luguo@139.com>
	 */
    private function _where($param){
        $aSql = array();
		$sepa  = 'AND';
        if(is_array($param)){
		    uasort($param,array($this,'btosSort'));
			$this->debug("where参数排序后=>".print_r($param,true).__METHOD__.' line '.__LINE__,E_USER_NOTICE,sle\Sle::SLE_SYS);
            foreach($param as $name=>$p){
                $sMethod = "_Where".ucfirst($name);
                if(method_exists($this,$sMethod)){
                    $this->$sMethod($p);
                    continue;
                }
				if(is_numeric($name) && is_array($p)){
					$aSql[]=$this->traversalArr($p,true);
					$this->debug("解析where数组参数后=>".print_r(end($aSql),true).__METHOD__.' line '.__LINE__,E_USER_NOTICE,sle\Sle::SLE_SYS);
				}else{
					if(strtolower($name) === '_logic'){
						$sepa = strtoupper($p);
						continue;
					}
                    //过滤array('value')这种情况
                    if(is_numeric($name) && is_string($p)){
                        $this->debug("错误的WHERE参数=> {$name}=>{$p}".print_r($param,true).__METHOD__.' line '.__LINE__,E_USER_ERROR,sle\Sle::SLE_SYS);
                    }else{
					    $aSql[] = $this->reassoc($name,$p);
                    }
				}
            }//end foreach
			if($aSql){
				$this->mSelectInfo['where'] = 'WHERE '.implode(" {$sepa} ",$aSql);
			}
        }//end array  
        else{
			$param = preg_replace_callback('#`([^`{]+)`(?:[^{`]+\{[^}`]+\})+#',array($this,'fetchFieldValue'),$param,-1);
            $this->mSelectInfo['where'] = "WHERE {$param}";
        }

    }


    /**
     * 把占位符统一改成“:表字段”，绑定参数数组的键名也统一替换成表字段，如“array(':表字段'=>value)”
     * 替换键名中.ge、.gt、.le等特殊符号
     * @param string $key   数组键名
     * @param mixed  $val   数组值
     * @return void
     * @author luguo<luguo@139.com>
     * @date 2014/7/7
     */
    private function reassoc($key,$val){
        $sField = $key;
        $sOp    = 'eq';
        $sBindField = ":{$sField}";
        $aBindValues = array();
        preg_match($this->mFieldRegexp,$key,$arr);
        if(isset($arr[1])){
            $sField = $arr[1];
            $sBindField = ":{$sField}";
        }
        if(isset($arr[2])){
            $sOp = $arr[2];
        }
        //用表字段替换原绑定值的键
        $aBindValues[$sBindField] = $val;

        switch(strtolower($sOp)){
            case 'in':
            case 'notin':
                if(is_array($val)){
                   for($i=0;$i<count($val);$i++){
                       if($i){
                         $sTmpKey = ":{sField}{$i}";
                         $aBindValues[$sTmpKey] = $val[$i];
                         $sBindField .= ",:{$sTmpKey}";
                       }else{
                         $aBindValues[":{$sField}"] = $val[$i];
                       }
                   }
                   $sBindField = "({$sBindField})";
                }elseif(is_string($val)){
                    $aBindValues[":{$sField}"] = $val[$i];
                    $sBindField = "({$sBindField})";
                }
                break;
            default:
        }
        isset($this->mFieldExpMap[$sOp]) && $sOp = $this->mFieldExpMap[$sOp];
        $this->mBindParam = array_merge($this->mBindParam,$aBindValues);
        return "{$this->mAlias}.`{$sField}` {$sOp} {$sBindField}";
    }

	private function fetchFieldValue($matchs){
		$aStr = array();
		$sField = $matchs[1];
		$iValStartPos = strpos($matchs[0],'{');
		$sPreVal = substr($matchs[0],0,$iValStartPos);
		$aStr[] = $sPreVal;
		$sVal = substr($matchs[0],$iValStartPos,strrpos($matchs[0],'}'));
		$iLeftSepPos = 0;
		$iRightSepPos = 0;
		$i = 1;
		while(true){
			$iLeftSepPos = strpos($sVal,'{',$iRightSepPos);
			if($iLeftSepPos === false)
				break;
			if($i>1){
				$aStr[] = substr($sVal,$iRightSepPos+1,$iLeftSepPos-$iRightSepPos-1);
			}
			$iRightSepPos = strpos($sVal,'}',$iLeftSepPos);
			if($i === 1)
			    $sKey = ":{$sField}";
			else
				$sKey = ":{$sField}".($i-1);
			$aStr[] = $sKey;
			$sValue = substr($sVal,$iLeftSepPos+1,$iRightSepPos-$iLeftSepPos-1);
			$this->mBindParam[$sKey] =str_replace(array('&#123;','&#125;'),array('{','}'),$sValue);
			$i++;
		}
		return implode('',$aStr);
	}

	private function _groupby($param){
		$sGroupby = '';
		if($param){
		}
		$this->mSelectInfo['groupby'] = $sGroupby;
	}

	private function _having($param){
		$sHaving = '';
		if($param){
		}
		$this->mSelectInfo['having'] = $sHaving;
	}

	private function _orderby($param){
		$sOrderby = '';
		if($param){
			//
		}
		$this->mSelectInfo['orderby'] = $sOrderby;
	}



	private function _limit($param){
		if(is_string($param)){
		    $this->mSelectInfo['limit'] = "LIMIT {$param}";
	    }
	}



    /**
	 * 参数排序，让数组排在后面(由大到小排序)
	 */
	private function btosSort($param1,$param2){
		//param1<param2  return 1;
		//param1>param2  reutrn -1
		//param1=param2  return 0
		if(is_array($param1))return 1;
		if(is_array($param2))return -1;
		return 0;
	}

    /**
	 * 开始事务
	 * @return boolean 
	 */
	public function beginTransaction(){
		return $this->mLink->beginTransaction();
	}


    /**
	 * 事务提交
	 * @return boolean 
	 */
	public function commit(){
		return $this->mLink->commit();
	}


    /**
	 * 事务回滚
	 * @return boolean
	 */
	public function rollback(){
		return $this->mLink->rollBack();
	}

	/**
	 * 返回具体驱动错误码
	 */
	public function getErrorCode(){
		return $this->mErrorInfo[1];
	}

	/**
	 * 返回具体驱动错误信息
	 */
	public function getErrorInfo(){
		return $this->mErrorInfo[2];
	}

	public function execute($param = array()){
		if(!$this->mPdoStatement->execute()){
		    $this->mErrorInfo = $this->mPdoStatement->errorInfo();
			$this->mErrorCode = $this->mPdoStatement->errorCode();
		}
	}

	public function bindValue($name,$value){
		if(is_string($name)){
		    $this->mBindParam[$name] = $value;
			return true;
		}elseif(is_array($name)){
            foreach($name as $key=>$val){
                $this->mBindParam[$key] = $val;
            }
            return true;
        }
		return false;
	}

    /**
	 * 按索引绑定参数
	 * @param mixed $value  需要绑定的参数，数组或字符串
	 * @return void
	 * @author luguo<luguo@139.com>
	 */
    public function bindParam($value){
		if($this->mBindParam){
			$this->mBindParam = array();
		}
		if(is_array($value)){
			$this->mBindParam = array_merge($this->mBindParam,$value);
		}else{
			$this->mBindParam[] = $value;
		}
	}

	public function fetch(){
		return $this->mPdoStatement->fetch($this->mFetchMode);
	}

	public function fetchAll(){
		return $this->mPdoStatement->fetchAll($this->mFetchMode);
	}





    private function _query($sSql){
    }

	public function prepare($sSql,$aParam = array()){
		$this->mPdoStatement = $this->mLink->prepare($sSql);
	}

    /**
     * 实现用数组拼接SQL语句
     * 如，WHERE条件里元素为一个索引数组，则用一个括号把该数组解析后的字符串括起来
     */
	private function traversalArr($aVal,$isNew = false){
		uasort($aVal,array($this,'btosSort'));
		static $sSql = '';
		$isNew && $sSql = '';
		$sSql .= '(';
		$sepa = 'AND';
		if(isset($aVal['_logic'])){
            $sepa = strtoupper($aVal['_logic']);
            unset($aVal['_logic']);
        }
		foreach($aVal as $key=>$value){
			if(is_numeric($key) && is_array($value)){
				$this->traversalArr($value);
			}elseif(is_string($key)){
				$sKey = $key;
				if(isset($this->mTableInfo[$this->mTableFullName][trim($key)])){
					$sk = $k = ":{$key}";
					$key = "{$key}=";
			    }elseif(preg_match($this->mFieldRegexp,$sKey,$arr)){print_r($arr);//die('end');
					$sk = $k = ":{$arr[1]}";
					$sOp = $arr[2];
					if(isset($this->mFieldExpMap[$sOp]))$sOp = $this->mFieldExpMap[$sOp];
					if(in_array(trim($arr[2]),array('in','notin'))){
				        $sk = "($k)";
					}
					$key = "{$arr[1]} {$sOp}";
				}else{
				    $this->debug("解析where参数错误=>{$key} ".print_r(end($aVal),true).__METHOD__.' line '.__LINE__,E_USER_ERROR,sle\Sle::SLE_SYS);
				}
				$sSql .="{$this->mAlias}.{$key}{$sk} {$sepa} ";
				$this->mBindParam[$k] = $value;
			}else{
				$this->debug("解析where参数错误=>{$key} ".print_r(end($aVal),true).__METHOD__.' line '.__LINE__,E_USER_ERROR,sle\Sle::SLE_SYS);
			}
		}
		$sSql = substr($sSql,0,strrpos($sSql,$sepa));
		$sSql .= ')';
		return $sSql;

	}






}