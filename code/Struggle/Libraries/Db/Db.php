<?php
namespace struggle\libraries\db;
abstract class Db extends \struggle\libraries\Object{
    abstract public function connect($type,$driver,$host,$port,$dbname,$user,$pwd,$opt);
}