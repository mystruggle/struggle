<?php
namespace struggle\controller;
use struggle\Sle;
use struggle\libraries\Client;
use struggle\libraries\Debug;

class MenuController extends Controller{
    public function actionIndex(){
        if (isset($_GET['act']) && $_GET['act']){
            $sMethod = '_'.$_GET['act'];
            $this->$sMethod();
            return ;
        }
        $oMenu = \struggle\M('Menu');
        $this->assgin('model', $oMenu);
        Sle::app()->client->registerClient($this->_js(),Client::POS_BODY_BOTTOM);
        $this->layout();
    }
    
    
    private function _getListData(){
        $oModel = \struggle\M('Menu');
        $aData = $oModel->count()->field('id,name,icon,`desc`,parent_id,orderby,create_time')->limit($_GET['iDisplayStart'],$_GET['iDisplayLength'])->findAll();
        $iCount = $oModel->getCount();
        $aResponseData = array();
        foreach ($aData as $data){
            $sEditUrl = Sle::app()->route->genUrl('menu/update?id='.$data['id']);
            $sDelUrl  = Sle::app()->route->genUrl('menu/delete?id='.$data['id']);
            $aResponseData[] = array('',$data['id'],$data['name'],$data['icon'],$data['desc'],$data['parent_id'],$data['orderby'],date('Y-m-d H:i:s',$data['create_time']),'{"edit":"'.$sEditUrl.'","del":"'.$sDelUrl.'"}');
        }
        $aResponseData = array('iTotalRecords'=>$iCount,'sEcho'=>$_GET['sEcho'],'iTotalDisplayRecords'=>$iCount,'aaData'=>$aResponseData);
        echo  json_encode($aResponseData);
        exit;
    }
    
    public function actionAdd(){
        if (isset($_REQUEST['act']) && $_REQUEST['act'] == 'save'){
            $this->save();
            return true;
        }
        Sle::app()->client->registerClient('jQuery(document).ready(function(){App.init();FormValidation.init();});',Client::POS_BODY_BOTTOM);
        //\struggle\M('Menu')->Field;
        //$this->assgin($sKey, $mValue)
        $this->layout();
    }
    
    private function save(){
        $oMenu = \struggle\M('Menu');
        $a = $oMenu->create($_POST);
        $this->redirect('','新增'.($oMenu->save($a)?'成功':'失败'));        
    }
    
    
    public function actionDelete(){
        $oMenu = \struggle\M('Menu');
        $retval = $oMenu->delete(array('id'=>$_GET['id']));
        die(json_encode(array('status'=>$retval,'message'=>($retval?'删除成功！':'删除失败'))));
    }

    
    private function _js(){
        $sJs = 'jQuery(document).ready(function() {
                    App.init();
                    TableManaged.init();
               });';
        return $sJs;
    }
}