<?php
namespace struggle\controller;

use struggle as sle;
class IndexController extends Controller{
    public function actionIndex(){
        $d = sle\M('User');
        $this->display();
    }
    
    public function actionLogin(){
        $this->display();
    }





}


