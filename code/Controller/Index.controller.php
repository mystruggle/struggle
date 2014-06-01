<?php
use struggle\libraries\core\Controller as Controller;

class IndexController extends Controller{
    public function actionIndex(){
        $this->assgin('a','');
        $this->assgin('b','我是B');
        $this->assgin('c','我是c');
        $this->display();
    }
}