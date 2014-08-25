<?php
namespace struggle\controller;
use struggle as sle;

class IndexController extends Controller{
    public function actionIndex(){
        $d = sle\M('User');
        $this->layout();
    }
    
    public function actionLogin(){
        $this->display();
    }
    
    
    
    
    public function actionUser(){
        
        
        
        
        echo '这是用户管理处理程序';
    }





}


