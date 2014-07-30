<?php
namespace struggle\model;
/******************************************************************************
 * 
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
*/


class RoleModel extends Model{
    public $table    = 'role';
    public $name     = 'Role';  //模型名称
    public $alias    = 'r';
    public $suffix   = '';
    public $prefix   = 'sle_';
    public $priKey   = 'id';

	public $relation = array(
		       'User'=>array(
						   'forginKey'=>'',
		                   'type'=>HAS_MANY,
		               ),
		   );



	public function test(){ die(__METHOD__);}






}