<?php
namespace struggle\controller;
use struggle as sle;
use struggle\libraries\Client;

class IndexController extends Controller{
    public function actionIndex(){
        $d = sle\M('User');
        $this->assgin('isHome', true);
        $this->layout();
    }
    
    public function actionLogin(){
        $this->display();
    }
    
    
    
    
    public function actionUser(){
        $this->layout();
    }





}


