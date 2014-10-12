<?php
namespace struggle\controller;
use struggle\Sle;
use struggle\libraries\Client;

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
        $iPageStart = $_GET['iDisplayStart']?$_GET['iDisplayStart']-1:0;
        $aData = $oModel->count()->field('id,name,icon,`desc`,parent_id,orderby,create_time')->limit($iPageStart*$_GET['iDisplayLength'],$_GET['iDisplayLength'])->findAll();
        $aCount = $oModel->getCount();
        $aResponseData = array();
        foreach ($aData as $data){
            $aResponseData[] = array('',$data['id'],$data['name'],$data['icon'],$data['desc'],$data['parent_id'],$data['orderby'],date('Y-m-d H:i:s',$data['create_time']),'');
        }
        $aResponseData = array('iTotalRecords'=>$aCount['count'],'sEcho'=>$_GET['sEcho'],'iTotalDisplayRecords'=>$aCount['count'],'aaData'=>$aResponseData);
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
        header('content-Type:text/html;charset=UTF-8');
        echo $_POST['name'];die('end');
        print_r($_REQUEST);
    }

    
    private function _js(){
        $sJs = 'jQuery(document).ready(function() {
                    App.init();
                    TableManaged.init();
               });';
        return $sJs;
    }
}