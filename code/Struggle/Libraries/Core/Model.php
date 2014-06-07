<?php
namespace struggle\libraries\core;
use struggle as sle;

abstract class BaseModel extends \struggle\libraries\Object{
    public $alias = '';
    public $mName = '';
    public function start(){
        $this->itsDefaultModule = 'index';
        $this->itsDefaultAction = 'index';
        
        sle\C('DISPATCHER_DEFAULT_MODULE') && $this->itsDefaultModule = struggle\C('DISPATCHER_DEFAULT_MODULE');
    }

    public function find(){echo 'find';}
    public function findAll(){}
    public function findBySql(){}
    public function findAllBySql(){}
}





namespace  struggle\model;
class Model extends \struggle\libraries\core\BaseModel{}