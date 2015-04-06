<?php
namespace Struggle\Admin\Controller;

use Struggle\Sle;
use Struggle\Libraries\Client;
use Struggle\Libraries\Debug;
use Struggle\Libraries\Validate;
use Struggle\Libraries\Core\Controller;
use Struggle\Libraries\Core\Route;

class ActionManage extends Controller{
    public function actionIndex(){
        if (isset($_GET['act']) && $_GET['act']){
            $sMethod = '_'.$_GET['act'];
            $this->$sMethod();
            return ;
        }
        $oMenu = \struggle\M('Action');
        $this->assgin('model', $oMenu);
        $this->layout();
    }
    
    
    private function _getListData(){
        $oModel = \struggle\M('Action');
		$aSearchField = explode(',',$_POST['sColumns']);
		$aData = array();
		foreach($aSearchField as $index=>$field){
			if(empty($field) || (!is_numeric($_POST["sSearch_".$index]) && empty($_POST["sSearch_".$index])))continue;
			if($index == 2 || $index == 4){
				$field = "`{$field}`.like";
				$_POST["sSearch_".$index] = '%'.$_POST["sSearch_".$index].'%';
			}else{
			}
			$aData[$field] = $_POST["sSearch_".$index];
		}
		if($aData){
			$oModel->where($aData);
		}
        $aData = $oModel->count()->field('id,name,title,`desc`')->limit($_POST['iDisplayStart'],$_POST['iDisplayLength'])->findAll();
        $iCount = $oModel->getCount();
        $aResponseData = array();
        foreach ($aData as $data){
            $sEditUrl = Route::self()->genUrl('actionManage/update?id='.$data['id']);
            $sDelUrl  = Route::self()->genUrl('actionManage/delete?id='.$data['id']);
            $aResponseData[] = array('',$data['id'],$data['name'],$data['title'],$data['desc'],'{"edit":"'.$sEditUrl.'","del":"'.$sDelUrl.'"}');
        }
        $aResponseData = array('iTotalRecords'=>$iCount,'sEcho'=>$_POST['sEcho'],'iTotalDisplayRecords'=>$iCount,'iDisplayStart'=>$_POST['iDisplayStart'],'aaData'=>$aResponseData);
        echo  json_encode($aResponseData);
        exit;
    }
    
    public function actionAdd(){
		//Validate::self()
        $oActModel = \struggle\M('Action');
        $oActModel->data = $_POST;
        if (isset($_REQUEST['act']) && $_REQUEST['act'] == 'save'){
            $this->save();
            return true;
        }
        $this->assgin('data', $oActModel->data);
        $this->layout();
    }
    
    private function save(){
        $oActModel = \struggle\M('Action');
        if(!$oActModel->validate()){
            $this->redirect($oActModel->error);
			return ;
        }
        $aData = $oActModel->create($_POST);
        //$aData['create_time'] = time();
        $this->redirect('新增'.($oActModel->save($aData)?'成功':'失败'));        
    }
    

    public function actionDelete(){
        $oMenu = \struggle\M('Action');
        $retval = $oMenu->delete(array('id'=>$_GET['id']));
        die(json_encode(array('status'=>$retval,'message'=>($retval?'删除成功！':'删除失败'))));
    }

    
    public function actionUpdate(){
        $oMenu = \struggle\M('Action');
        if ($_GET['act'] == 'save'){
            $data = $oMenu->create($_POST);
            unset($data['id']);
            //$data['create_time'] = time();
            $bStat = $oMenu->where(array('id'=>$_POST['id']))->update($data);
            $this->redirect('更新'.($bStat?'成功':'失败'));
            return;
        }
        $aRow = $oMenu->where(array('id'=>$_GET['id']))->find();
        $this->layout('',array('data'=>$aRow));
    }

    
   
    public function _beforeAction(){
        $aFile = array(
					'js'=>array(
						'select2.min.js',
						'app.js?theme=__THEME_NAME__&themePath=__THEME_PATH__',
					),
					'css'=>array(
			            'select2_metro.css',
			        ),
			     );
		if(__ACTION__ == 'Index'){
			$aFile['js'] = array_merge($aFile['js'],array('jquery.dataTables.js','DT_bootstrap.js','fnMultiFilter.js','table-managed.js'));
			$aFile['css'] = array_merge($aFile['css'],array('DT_bootstrap.css'));
		}elseif(in_array(__ACTION__,array('Update','Add'))){
			$aFile['js'] = array_merge($aFile['js'],array('jquery.validate.min.js','form-validation.js','additional-methods.min.js','chosen.jquery.min.js'));
			$aFile['css'] = array_merge($aFile['css'],array('chosen.css'));
		}

        $sJs = 'jQuery(document).ready(function() {
                    App.init();
					TableManaged.init({"formName":"#action_form",
                    "columns":[{"bSortable":false},
					{"bSortable":false,"sName":"id"},
					{"bSortable":false,"sName":"name"},
					{"bSortable":false,"sName":"title"},
					{"bSortable":false,"sName":"desc"},
					{"bSortable":false}],
					"dataUrl":"'.urlencode(Route::self()->genUrl('ActionManage/index?act=getListData')).'",
					"searchField":["","id","name","title","desc"]});
               });';

	    foreach($aFile as $type=>$files){
			foreach($files as $file){
				$tType = 'js';
				strtolower($type) == 'css' && $tType = 'css';
				$tPos = 'body,bottom';
				$tType == 'css' && $tPos = 'head,bottom';
                Client::self()->register(array('content'=>$file,'pos'=>$tPos,'isFile'=>true,'type'=>$tType));
			}
		}
		if(__ACTION__ == 'Index')
		    Client::self()->register(array('content'=>$sJs,'pos'=>'body,bottom','isFile'=>false,'type'=>'js'));
		elseif(in_array(__ACTION__,array('Update','Add'))){
			$sJs = 'jQuery(document).ready(function(){
                        App.init();
                        FormValidation.init({"formName":"#form_action_1"});
                    });';
			Client::self()->register(array('content'=>$sJs,'pos'=>'body,bottom','isFile'=>false,'type'=>'js'));
		}
    }















}