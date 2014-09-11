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
					$value['link'] = $this->_genLink($value['ctl_name'],$value['act_name']);
				}
				$value['selected'] = $this->_isSelected($value['ctl_name'], $value['act_name']);
                $aResult[$value['id']] = $value;
				unset($aMenu[$index]);
            }
        }
		foreach($aMenu as $index=>$value){
			if(in_array($value['parent_id'],array_keys($aResult))){
                if (empty($value['ctl_name']) || empty($value['act_name'])){
                    $value['link'] = 'javascript:;';
                }else{
					$value['link'] = $this->_genLink($value['ctl_name'],$value['act_name']);
				}
				$value['selected'] = $this->_isSelected($value['ctl_name'], $value['act_name']);
				$aResult[$value['parent_id']]['submenu'][] = $value;
				unset($aMenu[$index]);
			}
		}
        return $aResult;
    }


	private function _genLink($ctlName,$actName){
		return \struggle\Sle::getInstance()->Route->genUrl("{$ctlName}/{$actName}");
    }
    
    
    
    /**
     * 判断菜单是否是当前选中
     * @param string $ctl
     * @param string $act
     * @return boolean
     */
    private function _isSelected($ctl,$act){
        $oSle = \struggle\Sle::getInstance();
        if ($ctl == $oSle->Route->module && $oSle->Route->action){
            return true;
        }
        return false;
    }










}