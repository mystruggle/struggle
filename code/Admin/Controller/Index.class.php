<?php
namespace Admin\Controller;
use Struggle\Libraries\Core\Controller;
use Struggle\Sle;
use Struggle\Libraries\Client;

class Index extends Controller{
    public function actionIndex(){
        $oMenu = \Struggle\M('Menu');
        $this->assgin('model', $oMenu);
        
        $this->layout();
    }
    
    public function actionLogin(){
        $this->display();
    }
    
    
    
    
    public function actionUser(){
        $this->layout();
    }
    
    
    
    /**
     * 预处理前端文件
     * @return void
     */
    public function _beforeAction(){
        $aFile = array(
			        'js'=>array(
						'jquery.vmap.js',
						'jquery.vmap.russia.js',
						'jquery.vmap.world.js',
						'jquery.vmap.europe.js',
						'jquery.vmap.germany.js',
						'jquery.vmap.usa.js',
						'jquery.vmap.sampledata.js',
						'jquery.flot.js',
						'jquery.flot.resize.js',
						'jquery.pulsate.min.js',
						'date.js',
						'daterangepicker.js',
						'jquery.gritter.js',
						'fullcalendar.min.js',
						'jquery.easy-pie-chart.js',
						'jquery.sparkline.min.js',
						'app.js?theme=__THEME_NAME__&themePath=__THEME_PATH__',
						'index.js'
		            ),
					'css'=>array(
						'jquery.gritter.css',
						'daterangepicker.css',
						'fullcalendar.css',
						'jqvmap.css',
						'jquery.easy-pie-chart.css',
					),
        );
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
	    foreach($aFile as $type=>$files){
			foreach($files as $file){
				$tType = 'js';
				strtolower($type) == 'css' && $tType = 'css';
				$tPos = 'body,bottom';
				$tType == 'css' && $tPos = 'head,bottom';
                Client::self()->register(array('content'=>$file,'pos'=>$tPos,'isFile'=>true,'type'=>$tType));
			}
		}
		Client::self()->register(array('content'=>$sJs,'pos'=>'body,bottom','isFile'=>false,'type'=>'js'));
        
        
    }





}


