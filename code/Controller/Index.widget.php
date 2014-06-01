<?php
use struggle\libraries\core\Controller as Controller;
class IndexWidget extends Controller{
    public function actionIndex(){
		$this->assgin('b','widget b');
        $this->output();
    }
    public function actionShow(){
        $this->output();
    }
}