<?php
namespace struggle\model;
class MenuModel extends Model{
    public $name ='Menu';
    public $table = 'menu';
    public $alias = 'm'; 
    public $priKey = 'id';
    public $relation = array(
        'Controller'=>array( 
           'forginKey'=>'ctl_id',
           'type'     =>BELONGS_TO,
        ),
        'Action'=>array(
            'forginKey'=>'act_id',
            'type'     =>BELONGS_TO,
        ),
    );
                    
    public function getSidebarMenus(){
        $aMenu = $this->join('LEFT Controller LEFT Action','Controller.name=Action.name')->findAll();print_r($aMenu);
        $aResult = array();
        foreach ($aMenu as $value){
            if (intval($value['parent_id']) === 0){
                if (empty($value['ctl_id']) || empty($value['act_id'])){
                    $value['link'] = 'javascript:;';
                    unset($value['ctl_id']);
                    unset($value['act_id']);
                }
                $aResult[] = $value;
            }
        }
        return $aResult;
    }
}