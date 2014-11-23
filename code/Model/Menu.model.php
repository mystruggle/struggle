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

		//三级菜单
		foreach($aMenu as $index=>$value){
		    if(in_array($value['parent_id'],array_keys($aDep[1]))){
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
		$aMenuChain = array();
		$iDepLen = count($aDep)-1;
		for ($i=$iDepLen;$i>=0;$i--){
		    foreach ($aDep[$i] as $value){
		        if ($i == $iDepLen){
		            $value['selected'] = false;//&& Sle::app()->route->action == $value['act_name']
		            if (Sle::app()->route->module == $value['ctl_name'] ){
		                $value['selected'] = true;
		                $iSelectId = $value['parent_id'];
		                $aMenuChain[] = $value['id'];
		            }
		        }else{
		            $value['selected'] = false;//&& Sle::app()->route->action == $value['act_name']
		            if (is_null($iSelectId) && Sle::app()->route->module == $value['ctl_name'] ){
		                $value['selected'] = true;
		                $iSelectId = $value['parent_id'];
		                $aMenuChain[] = $value['id'];
		            }elseif ($iSelectId == $value['id']){
		                $value['selected'] = true;
		                $iSelectId = $value['parent_id'];
		                $aMenuChain[] = $value['id'];
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
		$aResult = $aDep[0];
		//菜单链处理
		$aMenuChainInfo = array();
		$aNodeInfo = array();
		$aMenuChain = array_reverse($aMenuChain);
		foreach ($aMenuChain as $index=>$id){
		    $aNodeInfo = empty($aNodeInfo)?$aResult[$id]:$aNodeInfo['submenu'][$id];
		    if ($aNodeInfo['parent_id'])
		      $aMenuChainInfo[$index]['name'] = $aNodeInfo['ctl_title'];
		    else 
		      $aMenuChainInfo[$index]['name'] = $aNodeInfo['name'];
			$aMenuChainInfo[$index]['id'] = $id;

		    $aMenuChainInfo[$index]['link']   = $this->_genLink($aNodeInfo['ctl_name'], $aNodeInfo['act_name']);
		    empty($aMenuChainInfo[$index]['link']) && $aMenuChainInfo[$index]['link'] = 'javascript:;';
		}
		Sle::app()->controller->assgin('actionInfo',$aMenuChainInfo[count($aMenuChainInfo)-1]);
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
            'menu.id',
            'menu.name',
            'menu.icon',
            'menu.desc',
            'menu.parent_id',
            'menu.orderby',
            'menu.create_time'
        );
    }










}