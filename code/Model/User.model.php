<?php
namespace struggle\model;
class UserModel extends Model{
    public $table    = 'user';
    public $name     = 'User';  //模型名称
    public $alias    = '';
    public $suffix   = '';
    public $prefix   = 'sle_';
    public $relation = array();

	public function test(){}
}