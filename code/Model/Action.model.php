<?php
namespace struggle\model;

class ActionModel extends Model{
    public $priKey = 'id';
    public $name   = 'Action';
    public $table  = 'action';
    public $alias  = 'act';
    public $relation = array(
        //
    );
}