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
        $this->layout();
    }
    
    
    private function _getListData(){
        $oModel = \struggle\M('Menu');
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
        $aData = $oModel->count()->field('id,name,icon,`desc`,parent_id,orderby,create_time')->limit($_POST['iDisplayStart'],$_POST['iDisplayLength'])->findAll();
        $iCount = $oModel->getCount();
        $aResponseData = array();
        foreach ($aData as $data){
            $sEditUrl = Sle::app()->route->genUrl('menu/update?id='.$data['id']);
            $sDelUrl  = Sle::app()->route->genUrl('menu/delete?id='.$data['id']);
            $aResponseData[] = array('',$data['id'],$data['name'],$data['icon'],$data['desc'],$data['parent_id'],$data['orderby'],date('Y-m-d H:i:s',$data['create_time']),'{"edit":"'.$sEditUrl.'","del":"'.$sDelUrl.'"}');
        }
        $aResponseData = array('iTotalRecords'=>$iCount,'sEcho'=>$_POST['sEcho'],'iTotalDisplayRecords'=>$iCount,'aaData'=>$aResponseData);
        echo  json_encode($aResponseData);
        exit;
    }
    
    public function actionAdd(){
        if (isset($_REQUEST['act']) && $_REQUEST['act'] == 'save'){
            $this->save();
            return true;
        }
        $this->layout();
    }
    
    private function save(){
        $oMenu = \struggle\M('Menu');
        $aData = $oMenu->create($_POST);
        $aData['create_time'] = time();
        $this->redirect('新增'.($oMenu->save($aData)?'成功':'失败'));        
    }
    

    public function actionDelete(){
        $oMenu = \struggle\M('Menu');
        $retval = $oMenu->delete(array('id'=>$_GET['id']));
        die(json_encode(array('status'=>$retval,'message'=>($retval?'删除成功！':'删除失败'))));
    }

    
    public function actionUpdate(){
        $oMenu = \struggle\M('Menu');
        if ($_GET['act'] == 'save'){
            $data = $oMenu->create($_POST);
            unset($data['id']);
            $data['create_time'] = time();
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
						'jquery.validate.min.js',
						'form-validation.js',
						'additional-methods.min.js',
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
			$aFile['js'] = array_merge($aFile['js'],array('chosen.jquery.min.js'));
			$aFile['css'] = array_merge($aFile['css'],array('chosen.css'));
		}


        $sJs = 'jQuery(document).ready(function() {
                    App.init();
					TableManaged.init({"formName":"#menu_1",
					"columns":[{"bSortable":false},
					{"bSortable":false,"sName":"id"},
					{"bSortable":false,"sName":"name","sClass":"center"},
					{"bSortable":false,"bSearch":""},
					{"bSortable":false,"sName":"desc"},
					{"bSortable":false,"sName":"parent_id"},
					{"bSortable":false,"bSearch":""},
					{"bSortable":false,"sName":"create_time"},
					{"bSortable":false,"bSearch":""}],
					"dataUrl":"'.urlencode(Sle::app()->route->genUrl('menu/index?act=getListData')).'",
					"searchField":["","id","name","","desc","parent_id","","create_time"]});
               });';

	    foreach($aFile as $type=>$files){
			foreach($files as $file){
				$tType = 'js';
				strtolower($type) == 'css' && $tType = 'css';
				$tPos = 'body,bottom';
				$tType == 'css' && $tPos = 'head,bottom';
                Sle::app()->client->register(array('content'=>$file,'pos'=>$tPos,'isFile'=>true,'type'=>$tType));
			}
		}
		if(__ACTION__ == 'Index')
		    Sle::app()->client->register(array('content'=>$sJs,'pos'=>'body,bottom','isFile'=>false,'type'=>'js'));
		elseif(in_array(__ACTION__,array('Update','Add'))){
			$sJs = 'jQuery(document).ready(function(){
							App.init();
							FormValidation.init({"formName":"#form_menu_1"});
							FormValidation.addValidMethod("menuName",["[\u4e00-\u9fa5a-z_0-9]","ig"],"只能包含汉字、a-z、_、0-9");
				    });';
			Sle::app()->client->register(array('content'=>$sJs,'pos'=>'body,bottom','isFile'=>false,'type'=>'js'));
		}
    }
}