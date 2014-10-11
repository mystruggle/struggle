<?php
namespace struggle\model;

class ControllerModel extends Model{
    public $name = 'Controller';
    public $table = 'controller';
    public $priKey = 'id';
    public $alias  = 'cltr';
    public $relation = array(
        //
    );
    
    public function getAllCtl(){
        $aCtl = $this->field('id,name,title')->findAll();
        return $aCtl?$aCtl:array();
    }
}