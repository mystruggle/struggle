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
		$this->setFetchMode(\PDO::FETCH_ASSOC);
        $aMenu = $this->field('Menu.id,Menu.name,icon,Controller.name as ctl_name,Action.name as act_name,parent_id')
			          ->join('LEFT Controller LEFT Action')->findAll();
        $aResult = array();
        foreach ($aMenu as $index=>$value){
            if (intval($value['parent_id']) === 0){
                if (empty($value['ctl_name']) || empty($value['act_name'])){
                    $value['link'] = 'javascript:;';
                }else{
					$value['link'] = $this->_genlink($value['ctl_name'],$value['act_name']);
				}
                $aResult[$value['id']] = $value;
				unset($aMenu[$index]);
            }
        }
		foreach($aMenu as $index=>$value){
			if(in_array($value['parent_id'],array_keys($aResult))){
				$aResult[$value['parent_id']]['submenu'] = $value;
				unset($aMenu[$index]);
			}
		}
		print_r($aResult);
        return $aResult;
    }


	private function _genLink($ctlName,$actName){
		return namespce\Sle::getInstance()->Route->genUrl("{$ctlName}/{$actName}");
    }










}