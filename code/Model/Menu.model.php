<?php
namespace struggle\model;
use struggle\Sle;

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
        $aMenu = $this->field('Menu.id,Menu.name,Controller.title as ctl_title,Action.title as act_title,icon,Controller.name as ctl_name,Action.name as act_name,parent_id')
			          ->join('LEFT Controller LEFT Action')->findAll();
        $aResult = array();
		$sDep = '';   // 记录菜单元素维度路径,每个路径用|分隔，节点之间用,分隔
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
				$sDep        .= $value['id'].'|';
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
				$sDep  = str_replace($value['parent_id'],"{$value['parent_id']},{$value['id']}",$sDep);
				unset($aMenu[$index]);
			}
		}
		//菜单链处理
		$aMenuChain = explode(',',$this->_getMenuDep($iSelectId,$sDep));
		Sle::app()->controller->assgin('menuChain',$aMenuChain);
		$aMenuChainInfo = array();
		$aNodeInfo = array();
		foreach ($aMenuChain as $id){
		    $aNodeInfo = empty($aNodeInfo)?$aResult[$id]:$aNodeInfo['submenu'][$id];
		    if ($aNodeInfo['parent_id'])
		      $aMenuChainInfo[$id]['name'] = $aNodeInfo['ctl_title'];
		    else 
		      $aMenuChainInfo[$id]['name'] = $aNodeInfo['name'];
		    $aMenuChainInfo[$id]['link']   = $this->_genLink($aNodeInfo['ctl_name'], $aNodeInfo['act_name']);
		}
		Sle::app()->controller->assgin('menuChainInfo',$aMenuChainInfo);
        return $aResult;
    }

    /**
	 * 依据控制器名称和动作名称生成链接
	 */
	private function _genLink($ctlName,$actName){
	    if (empty($ctlName))
	        return '';
		return Sle::app()->route->genUrl("{$ctlName}/{$actName}");
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
        $oSle = Sle::app();
        if ($ctl == $oSle->route->module && $oSle->route->action){
            return true;
        }
        return false;
    }










}