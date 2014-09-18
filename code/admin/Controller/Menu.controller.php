<?php
namespace struggle\controller;
use struggle\Sle;

class MenuController extends Controller{
    public function actionIndex(){
        //echo APP_NAME;
        //Sle::getInstance()->Client->registerClientJs($file)
        $this->layout();
    }
}