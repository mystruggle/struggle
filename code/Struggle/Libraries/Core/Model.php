<?php
namespace struggle\libraries\core;
use struggle as sle;

abstract class Model extends \struggle\libraries\Object{
    abstract public $table = '';
    abstract public $alias = '';
    abstract public $mName = '';
    public function start(){
        $this->itsDefaultModule = 'index';
        $this->itsDefaultAction = 'index';
        
        sle\C('DISPATCHER_DEFAULT_MODULE') && $this->itsDefaultModule = struggle\C('DISPATCHER_DEFAULT_MODULE');
    }

    abstract public function find(){}
    abstract public function findAll(){}
    abstract public function findBySql(){}
    abstract public function findAllBySql(){}
}