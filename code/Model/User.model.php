<?php
namespace struggle\model;
class UserModel extends Model{
    public $table    = 'user';
    public $name     = 'User';  //模型名称
    public $alias    = '';
    public $suffix   = '';
    public $prefix   = 'sle_';
    public $priKey   = 'id';

    public $relation = array(
            'belong_to_role'=>array(
                    'model'=>'role',
                    'type'=>'',
                    'refer_key'=>'',
                    'foreign_key'=>'',
                    'middle_table'=>'',
                    'reverse'=>'',
                    'ext_limit'=>'',
                    'dependent'=>'',
                ),
        );

	public function test(){ die(__METHOD__);}
}