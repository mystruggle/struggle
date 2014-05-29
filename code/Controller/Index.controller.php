<?php
use struggle\libraries\core\Controller as Controller;

class IndexController extends Controller{
    public function actionIndex(){
        $this->assgin('c',99999999999);
        $this->display();
    }
}