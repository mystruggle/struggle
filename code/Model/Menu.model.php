<?php
namespace struggle\model;
use struggle\Sle;
use struggle\libraries\Debug;

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
		$aDep = array();   
		$iSelectId = 0;
		
		//一级菜单
        foreach ($aMenu as $index=>$value){
            if (intval($value['parent_id']) === 0){
                if (empty($value['ctl_name']) || empty($value['act_name'])){
                    $value['link'] = 'javascript:;';
                }else{
					$value['link'] = $this->_genLink($value['ctl_name'],$value['act_name']);
				}
				$this->_isSelected($value['ctl_name'], $value['act_name']) && $iSelectId = $value['id'];
                $aResult[$value['id']] = $value;
				unset($aMenu[$index]);
            }
        }
        $aDep[] = $aResult;
        $aResult = array();
        
        //二级菜单
		foreach($aMenu as $index=>$value){
			if(in_array($value['parent_id'],array_keys($aDep[0]))){
                if (empty($value['ctl_name']) || empty($value['act_name'])){
                    $value['link'] = 'javascript:;';
                }else{
					$value['link'] = $this->_genLink($value['ctl_name'],$value['act_name']);
				}
				$this->_isSelected($value['ctl_name'], $value['act_name']) && $iSelectId = $value['id'];
				$aResult[$value['id']] = $value;
				unset($aMenu[$index]);
			}
		}
		$aDep[] = $aResult;
		$aResult = array();
		
		//处理菜单上下级关系
		$iSelectId = null;
		$iDepLen = count($aDep)-1;
		for ($i=$iDepLen;$i>=0;$i--){
		    foreach ($aDep[$i] as $value){
		        if ($i == $iDepLen){
		            $value['selected'] = false;
		            if (Sle::app()->route->module == $value['ctl_name'] && Sle::app()->route->action == $value['act_name']){
		                $value['selected'] = true;
		                $iSelectId = $value['parent_id'];
		            }
		        }else{
		            $value['selected'] = false;
		            if ($iSelectId == $value['id']){
		                $value['selected'] = true;
		                $iSelectId = $value['parent_id'];
		            }
		        }
		        if ($i>0)
		            $aDep[$i-1][$value['parent_id']]['submenu'][$value['id']] = $value;
		        else 
		            $aDep[$i][$value['id']]['selected'] = $value['selected'];
		    }
		    if ($i>0)
		        unset($aDep[$i]);
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
		$aDepOfSort = array();
		foreach (explode('|', $dep) as $value){
		    if(empty($value)) continue;
		    $aChin = explode(',', $value);
		    $iParent = array_shift($aChin);
		    $i = 0;
		    do{
		        if (intval(substr($aChin[$i], 0,strlen($iParent))) == intval($iParent)){
		            $aDepOfSort[$iParent][] = substr($aChin[$i],strlen($iParent)+1);
		        }
		    }while($aChin);
		}
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
	
	public function getAllMenu(){
	    $aMenu = $this->field('id,name')->findAll();
	    return $aMenu?$aMenu:array();
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
    
    public function _listField(){
        return array(
            'id',
            'name',
            'icon',
            'desc',
            'parent_id',
            'orderby',
            'create_time'
        );
    }










}