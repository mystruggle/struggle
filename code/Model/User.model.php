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
                    'type'=>MANY_TO_MANY,
                    'refer_key'=>'id',
                    'foreign_key'=>'',
                    'middle_table'=>'role_user',
                    'reverse'=>'',
                    'ext_limit'=>'',
                    'dependent'=>'',
					'alias'=>'',
                ),
        );

	public function test(){ die(__METHOD__);}
}