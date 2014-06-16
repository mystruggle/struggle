<?php
namespace struggle\libraries\db;
abstract class Db extends \struggle\libraries\Object{


	abstract public function setAttr($name,$value);
	abstract public function getAttr($name);
    abstract public function connect($option);
	abstract public function find($aOpt = array());
	//abstract public function findAll();
	//abstract public function findBySql();
	//abstract public function findAllSql();
}