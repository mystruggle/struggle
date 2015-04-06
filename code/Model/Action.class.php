<?php
namespace Model;
use Struggle\Libraries\Core\Model;

class Action extends Model{
    public $priKey = 'id';
    public $name   = 'Action';
    public $table  = 'action';
    public $alias  = 'actn';
    public $relation = array(
        //
    );

	protected $rules = array(
		          array('name','number','控制器名称不能为非数字！'),
		      );
    
    public function getAllAct(){
        $aAct = $this->field('id,name,title')->findAll();
        return $aAct?$aAct:array();
    }
    
    public function _listField(){
        return array(
            'action.id',
            'action.name',
            'action.title',
            'action.desc'
        );
    }







}