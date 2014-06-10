<?php
namespace struggle\libraries\core;
use struggle as sle;

class BaseModel extends \struggle\libraries\Object{
    public  $alias = '';
    public  $mName = '';
    /** 数据库连接 */
    private $mLink  = array();

    public function __construct(){
        parent::__construct();
        $this->_init();
        static $aLink = array();
        if(!$this->mLink){
            
        }
    }

    private function _init(){
        //var_dump('|',property_exists($this,'test'),'|');
    }

    public function start(){
        $this->itsDefaultModule = 'index';
        $this->itsDefaultAction = 'index';
        
        sle\C('DISPATCHER_DEFAULT_MODULE') && $this->itsDefaultModule = struggle\C('DISPATCHER_DEFAULT_MODULE');
    }

    public function find(){
        if(property_exists($this,'Db')){
        }
    }
    public function findAll(){}
    public function findBySql(){}
    public function findAllBySql(){}
}





namespace  struggle\model;
class Model extends \struggle\libraries\core\BaseModel{}