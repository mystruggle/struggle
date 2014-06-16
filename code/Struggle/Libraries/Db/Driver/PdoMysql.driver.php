<?php
namespace struggle\libraries\db\driver;
class PdoMysqlDriver extends \struggle\libraries\db\Db{
	protected $mLink = null;
    protected $mErrorInfo = '';
    protected $mErrorCode = '';
    protected $mConnTimeOut = 30;  //连接超时
    protected $mConnErrMode = \PDO::ERRMODE_WARNING;  //错误报告模式ERRMODE_EXCEPTION
    protected $mTableInfo   = array(); //表结构信息

    public function __construct($aOpt){
        parent::__construct();
        $this->connect($aOpt);
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
		$this->initTableMetadata($aOpt,$oLink);
        return $this->mLink = $oLink;
	}

	public function initTableMetadata($aOpt,$oLink){
		static $aMetadata;
        if(!isset($aOpt['table']) || empty($aOpt['table']))
            return ;
		$sKey = md5("{$aOpt['host']}:{$aOpt['port']}.{$aOpt['dbname']}.{$aOpt['table']}");
		if(!isset($aMetadata[$sKey])){
			$sMetaSql = "desc {$aOpt['table']}";
			$oStatement = $oLink->query($sMetaSql);
			$aTmp=$oStatement->fetchAll(\PDO::FETCH_ASSOC);
            $aTableInfo=array();
			foreach($aTmp as $info){
                $sField = $info['Field'];
                unset($info['Field']);
                $aTableInfo[$sField] = $info;
            }
            if(isset($aTableInfo['alias']))
                $aOpt['alias'] = $aTableInfo['alias'];
            $aMetadata[$sKey] = $aTableInfo;
		}
        $this->mTableInfo[$aOpt['table']] = $aMetadata[$sKey];
	}


	public function setAttr($sName,$mVal){
		return $this->mLink->setAttribute($sName,$mVal);
	}

    public function getAttr($sName){
        return $this->mLink->getAttribute($sName);
    }

    public function find($aOpt = array()){
        $this->parseOpt($aOpt);
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
    }

    private function _field($sField){
        if(is_string($sField)){
            $
        }
    }

    private function _query($sSql){
    }

    private function execute($sSql){
    }






}