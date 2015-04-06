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


class RoleUserModel extends Model{
    public $table    = 'role_user';
    public $name     = 'RoleUser';  //模型名称
    public $alias    = 'ru';
    public $priKey   = 'user_id,role_id';

	public $relation = array(
		       'Role'=>array(
		                   'forginKey'=>'role_id',
		                   'type'=>HAS_MANY,
		               ),
		       'User'=>array(
		                   'forginKey'=>'user_id',
		                   'type'=>HAS_MANY,
		               ),
		   );



	public function test(){ die(__METHOD__);}






}