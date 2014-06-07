<?php
namespace struggle\libraries\db\driver;
class PdoMysqlDriver extends \struggle\libraries\db\Db{
	protected $mLink = null;
	public function connect($sType,$sDrv,$sHost,$iPort,$sDbName,$sUser,$sPwd,$aOpt=array()){
		static $aLink = null;
        $sDns = "{$sDrv}:host={$sHost};port={$iPort};dbname={$sDbName}";
        $oTmpLink = new \PDO($sDns,$sUser,$sPwd);
		print_r(func_get_args());
        echo $sDns;
	}


	public function setAttr($sName,$mVal){
		$this->mLink = '';
	}
}