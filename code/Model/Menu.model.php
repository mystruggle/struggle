<?php
namespace struggle\model;
class MenuModel extends Model{
    public $name ='menu';
    public $alias = 'm'; 
                    
    public function getSidebarMenus(){
        return array('a','b');
    }
}