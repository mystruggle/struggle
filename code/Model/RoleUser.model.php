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
    public $alias    = '';
    public $priKey   = 'user_id,role_id';

	public $relation = array(
		       'role'=>array(
		                   'forginKey'=>'role_id',
		                   'type'=>HAS_MANY,
		               ),
		       'user'=>array(
		                   'forginKey'=>'user_id',
		                   'type'=>HAS_MANY,
		               ),
		   );



	public function test(){ die(__METHOD__);}






}