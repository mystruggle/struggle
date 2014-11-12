<?php
namespace struggle\controller;
use struggle\Sle;
use struggle\libraries\Client;

class IndexController extends Controller{
    public function actionIndex(){
        $oMenu = \struggle\M('Menu');
        $this->assgin('model', $oMenu);
        Sle::app()->client->registerClient($this->_indexJs(),Client::POS_BODY_BOTTOM);
        
        $this->layout();
    }
    
    public function actionLogin(){
        $this->display();
    }
    
    
    
    
    public function actionUser(){
        $this->layout();
    }
    
    
    
    /**
     * 返回当前首页js
     * @return string
     */
    private function _indexJs(){
        $sJs = 'jQuery(document).ready(function() {
                    App.init(); 
                    Index.init();
                    Index.initJQVMAP(); 
                    Index.initCalendar(); 
                    Index.initCharts(); 
                    Index.initChat();
                    Index.initMiniCharts();
                    Index.initDashboardDaterange();
                    Index.initIntro();
              });';
        return $sJs;
        
        
    }





}


