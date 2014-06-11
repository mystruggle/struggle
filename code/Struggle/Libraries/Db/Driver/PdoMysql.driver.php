<?php
namespace struggle\libraries\db\driver;
class PdoMysqlDriver extends \struggle\libraries\db\Db{
	public function connect($sType,$sDrv,$sHost,$iPort,$sDbName,$sUser,$sPwd,$aOpt=array()){
        $sDns = "{$sDrv}:host={$sHost};port={$iPort};dbname={$sDbName}";
        return new \PDO($sDns,$sUser,$sPwd);
        echo $sDns;
	}
}