<?php
namespace struggle\model;

class ActionModel extends Model{
    public $priKey = 'id';
    public $name   = 'Action';
    public $table  = 'action';
    public $alias  = 'actn';
    public $relation = array(
        //
    );
    
    public function getAllAct(){
        $aAct = $this->field('id,name,title')->findAll();
        return $aAct?$aAct:array();
    }
}