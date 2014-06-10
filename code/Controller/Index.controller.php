<?php
namespace struggle\controller;

class IndexController extends Controller{
    public function actionIndex(){
        //$this->assgin('a','');
        //$this->assgin('b','我是B');
        //$this->assgin('c','我是c');
		//$a=\struggle\M('User');
		//print_r($a);
		//$b=\struggle\M();
		//print_r($b);
		$c=\struggle\M();
		$c->find();
        $this->display();
    }
}