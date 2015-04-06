<?php
namespace Model;
use Struggle\Sle;
use Struggle\Libraries\Debug;
use Struggle\Libraries\Core\Model;

class Controller extends Model{
    public $name ='Controller';
    public $table = 'controller';
    public $alias = 'cltr'; 
    public $priKey = 'id';
	/*
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
	*/


    public function getAllCtl(){
        $aCtl = $this->field('id,name,title')->findAll();
        return $aCtl?$aCtl:array();
    }
	

    /**
	 * 依据控制器名称和动作名称生成链接
	 */
	private function _genLink($ctlName,$actName){
	    if (empty($ctlName))
	        return '';
		return Route::self()->genUrl("{$ctlName}/{$actName}");
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
        if ($ctl == Route::self()->module && Route::self()->action){
            return true;
        }
        return false;
    }
    
    public function _listField(){
        return array(
            'controller.id',
            'controller.name',
            'controller.title',
            'controller.desc'
        );
    }










}