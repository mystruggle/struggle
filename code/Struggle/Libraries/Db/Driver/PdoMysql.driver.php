<?php
namespace struggle\libraries\db\driver;
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
    protected $mSelectParam   = array();  //绑定的参数

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
        print_r($this->mSelectInfo);
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
        //print_r($param);
    }
    private function _where($param){
        if(is_array($param)){
            $aVals = array();
            foreach($param as $name=>$p){
                $sMethod = "_m{$name}";
                if(method_exists($this,$sMethod)){
                    $this->$sMethod($p);
                    continue;
                }
                $sParamKey = ":{$name}";
                $aVals[] = "{$this->mAlias}.{$name}={$sParamKey}";
                $this->mSelectParam['where'][$sParamKey] = $p;
            }
        }
    }

    private function _query($sSql){
    }

    private function execute($sSql){
    }

	public function prepare($sSql,$aParam){
	}






}