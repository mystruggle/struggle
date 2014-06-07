<?php
namespace struggle\libraries\db\driver;
class PdoMysqlDriver extends \struggle\libraries\db\Db{
	protected $mLink = null;
    protected $mErrorInfo = '';
    protected $mErrorCode = '';
    protected $mConnTimeOut = 30;  //连接超时
    protected $mConnErrMode = \PDO::ERRMODE_EXCEPTION;  //错误报告模式

    public function __construct($aOpt){
        parent::__construct();
        $this->connect($aOpt);
    }
	public function connect($aOpt){
		static $oLink = null;
        if(!$oLink){
            $sDns = "{$aOpt['driver']}:host={$aOpt['host']};port={$aOpt['port']};dbname={$aOpt['dbname']}";
            $oLink = new \PDO($sDns,$aOpt['user'],$aOpt['pwd'],array(\PDO::ATTR_ERRMODE=>$this->mConnErrMode,\PDO::ATTR_TIMEOUT=>$this->mConnTimeOut));
            $this->mErrorInfo = $oLink->errorInfo();
            $this->mErrorCode = $oLink->errorCode();
        }
		$this->initMetadata($aOpt,$oLink);
        return $this->mLink = $oLink;
	}

	public function initMetadata($aOpt,$oLink){
		static $aMetadata;
		$sKey = md5("{$aOpt['host']}:{$aOpt['port']}.{$aOpt['dbname']}.{$aOpt['table']}");
		if(!isset($aMetadata[$sKey])){
			$sMetaSql = "show create table {$aOpt['table']}";
			$oStatement = $oLink->query($sMetaSql);
			$aTmp=$oStatement->fetchAll(\PDO::FETCH_ASSOC);
			//print_r($aTmp);
		}
	}


	public function setAttr($sName,$mVal){
		return $this->mLink->setAttribute($sName,$mVal);
	}

    public function getAttr($sName){
        return $this->mLink->getAttribute($sName);
    }

    private function _query($sSql){
    }

    private function execute($sSql){
    }
}