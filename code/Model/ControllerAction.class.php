<?php
namespace  Struggle\Model;
class ControllerAction extends Model{
    public $name = 'ControllerAction';
    public $table = 'controller_action';
    public $priKey = 'id';
    public $alias  = 'ca';
    public $relation = array(
        'Controller'=>array(
            'forginKey'=>'ctl_id',
        ),
    );
}