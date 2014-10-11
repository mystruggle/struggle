<?php
namespace struggle\libraries\db;
abstract class Db extends \struggle\libraries\Object{

	public function handleFieldScope(){
	}


	abstract public function setAttr($name,$value);
	abstract public function getAttr($name);
    abstract public function connect($option);
	abstract public function find($aOpt = array());
	abstract public function findAll($aOpt = array());
	abstract public function prepare($sql,$param);
    abstract public function bindValue($name,$value);
    abstract public function bindParam($value);
    abstract public function getFields($table);
    abstract public function reset();
	//abstract public function findAll();
	//abstract public function findBySql();
	//abstract public function findAllSql();
}
/*
1、sql语句拼接
2、prepare
3、bindvalue
4、execute
5、fetch
*/