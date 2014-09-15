<?php
namespace struggle\controller;
use struggle as sle;
use struggle\libraries\Client;

class IndexController extends Controller{
    public function actionIndex(){
        $d = sle\M('User');
        sle\Sle::getInstance()->Client->registerClientJs('jquery.vmap.js',Client::POS_BODY_BEFORE);
        $this->layout();
    }
    
    public function actionLogin(){
        $this->display();
    }
    
    
    
    
    public function actionUser(){
        $this->layout();
    }





}


