<?php
namespace struggle\libraries\db\driver;
use struggle as sle;
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
		foreach($this->mBindParam as $name=>$value){
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
				if(is_array($p)){
					$aSql[]=$this->traversalArr($p,true);
					$this->debug("解析where数组参数后=>".print_r(end($aSql),true).__METHOD__.' line '.__LINE__,E_USER_NOTICE,sle\Sle::SLE_SYS);
				}else{
					if($name === '_logic'){
						$sepa = strtoupper($p);
						continue;
					}
					$sParamKey = ":".str_replace('`','',$name);
					$aSql[] = "{$this->mAlias}.{$name}={$sParamKey}";
					$this->mBindParam[$sParamKey] = $p;
				}
            }//end foreach
			if($aSql){
				$this->mSelectInfo['where'] = 'WHERE '.implode(" {$sepa} ",$aSql);
			}
        }//end array  
        else{//(?:[^`{]+\{([^}])+\})     `([^`{]+)`(?:[^{`]+\{[^}`]+\})*
			$param = preg_replace_callback('#(\{([^}]+)\})+#',array($this,'fetchFieldValue'),$param,-1);
            $this->mSelectInfo['where'] = "WHERE {$param}";
        }

    }

	private function fetchFieldValue($matchs){
		print_r($matchs);
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

// a=1 or (b=2 and(c=3))
//array('a'=>1,array('b'=>2,array('c'=>3,'_logic'=>'and'),'_logic'=>'and'),'_logic'=>'or')
//array('a=1','or',)   (b=2 and e=4) and(
	private function traversalArr($aVal,$isNew = false){
		uasort($aVal,array($this,'btosSort'));
		static $sSql = '';
		if($isNew)
			$sSql = '';
		$sSql .= '(';
		$sepa = 'AND';
		isset($aVal['_logic']) && $sepa = strtoupper($aVal['_logic']);
		foreach($aVal as $key=>$value){
			if($key === '_logic'){
				continue;
			}
			if(is_array($value)){
				$this->traversalArr($value);
			}else{
				$sKey = $key;
				if(preg_match('/(.*)\.(gt|lt|ge|le|eq)$/i',$sKey,$arr)){
					$aMap = array("gt"=>">","lt"=>"<","ge"=>">=","le"=>"<=","eq"=>"=");
					$k = ":{$arr[1]}";
					$key = "{$arr[1]}{$aMap[$arr[2]]}";
				}else{
					$k = ":{$key}";
					$key = "{$key}=";
				}
				$sSql .="{$this->mAlias}.{$key}{$k} {$sepa} ";
				$this->mBindParam[$k] = $value;
			}
		}
		$sSql = substr($sSql,0,strrpos($sSql,$sepa));
		$sSql .= ')';
		return $sSql;

	}






}