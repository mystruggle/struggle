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


class UserModel extends Model{
    public $table    = 'user';
    public $name     = 'User';  //模型名称
    public $alias    = 'u';
    public $suffix   = '';
    public $prefix   = 'sle_';
    public $priKey   = 'id';

	public $relation = array( 
		       'Role'=>array(
		                   'middleTable'=>'role_user',
		                   'type'=>HAS_AND_BELONG_TO_MANY,
		               ),
		       'Menu'=>array(
						   'type'=>HAS_MANY,
		               ),
		       'RoleUser'=>array(
						   'type'=>HAS_MANY,
		                   'forginKey'=>'user_id',
		               ),
		   );

	public function getTableName(){
		
	}



	public function test(){ die(__METHOD__);}






}