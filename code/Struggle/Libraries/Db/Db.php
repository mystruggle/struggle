<?php
namespace struggle\libraries\db;
abstract class Db extends \struggle\libraries\Object{

	abstract public function setAttr($name,$value);



    abstract public function connect($type,$driver,$host,$port,$dbname,$user,$pwd,$opt);
	//abstract public function find();
	//abstract public function findAll();
	//abstract public function findBySql();
	//abstract public function findAllSql();
}