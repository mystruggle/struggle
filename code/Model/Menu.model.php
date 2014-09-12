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
		$aResult['dep'] = '';   // 记录元素维度路径,每个路径用|分隔，节点之间用,分隔
		$iSelectId = 0;
        foreach ($aMenu as $index=>$value){
            if (intval($value['parent_id']) === 0){
                if (empty($value['ctl_name']) || empty($value['act_name'])){
                    $value['link'] = 'javascript:;';
                }else{
					$value['link'] = $this->_genLink($value['ctl_name'],$value['act_name']);
				}
				$this->_isSelected($value['ctl_name'], $value['act_name']) && $iSelectId = $value['id'];
                $aResult[$value['id']] = $value;
				$aResult['dep']        .= $value['id'].'|';
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
				$this->_isSelected($value['ctl_name'], $value['act_name']) && $iSelectId = $value['id'];
				$aResult[$value['parent_id']]['submenu'][$value['id']] = $value;
				$aResult['dep']  = str_replace($value['parent_id'],"{$value['parent_id']},{$value['id']}",$aResult['dep']);
				unset($aMenu[$index]);
			}
		}
		$aResult['dep'] = explode(',',$this->_getMenuDep($iSelectId,$aResult['dep']));
        return $aResult;
    }

    /**
	 * 依据控制器名称和动作名称生成链接
	 */
	private function _genLink($ctlName,$actName){
		return \struggle\Sle::getInstance()->Route->genUrl("{$ctlName}/{$actName}");
    }

    /**
	 * 获取菜单节点位置
	 * @param integer $id 搜索的节点，用于定位在那个节点链上
	 * @param string  $dep 全部的节点链
	 * @param integer $pos  需要返回的节点，为0返回链
	 */
	private function _getMenuDep($id,$dep,$pos = 0){
		$dep ='|'.ltrim($dep,'|');
		$iIdPos = strpos($dep,$id);
		$iVerticalPos = strpos($dep,'|',$iIdPos);
		$dep = substr($dep,0,$iVerticalPos);
		$iVerticalPos = strrpos($dep,'|');
		$dep = substr($dep,$iVerticalPos+1);
		$aDep = explode(',',$dep);
		if($pos && isset($aDep[$pos-1]))
			return $aDep[$pos-1];
		return $dep;
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