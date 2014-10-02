<?php
namespace struggle\controller;
use struggle\Sle;
use struggle\libraries\Client;

class MenuController extends Controller{
    public function actionIndex(){
        //echo APP_NAME;
        $oMenu = \struggle\M('Menu');
        $this->assgin('model', $oMenu);
        Sle::app()->client->registerClient($this->_js(),Client::POS_BODY_BOTTOM);
        $this->layout();
    }
    
    
    public function actionView(){
        $oModel = \struggle\M('Menu');
        $aData = $oModel->field('id,name,icon,`desc`,parent_id,orderby,create_time')->findAll();
        $aResponseData = array();
        foreach ($aData as $data){
            $aResponseData[] = array('<div class="checker"><span class=""><input type="checkbox" class="checkboxes" value="1" /></span></div>',$data['id'],$data['name'],$data['icon'],$data['desc'],$data['parent_id'],$data['orderby'],$data['create_time']);
        }
        $aResponseData = array('aaData'=>$aResponseData);
        echo  json_encode($aResponseData);
        die;
    }
    
    
    private function _js(){
        $sJs = 'jQuery(document).ready(function() {
                    App.init();
                    TableManaged.init();
               });';
        return $sJs;
    }
}