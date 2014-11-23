<?php
use struggle\libraries\Client;
use struggle\Sle;
return array(
    'global'=>array(
        Client::POS_HEAD_BOTTOM => array(
                //'fullcalendar.css',
        ),
        Client::POS_BODY_BOTTOM => array(
            //
        )
    ),
    'index'=>array(
            'index'=>array(
                    Client::POS_BODY_BOTTOM=>array(
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
                            'app.js?theme=__THEME_NAME__&themePath=__THEME_PATH__//.js',
                            'index.js',
                    ),
                    Client::POS_HEAD_BOTTOM=>array(
                            'jquery.gritter.css',
                            'daterangepicker.css',
                            'fullcalendar.css',
                            'jqvmap.css',
                            'jquery.easy-pie-chart.css',
                    ),
            ),
    ),
    'menu'=>array(
            'global' =>array(
                Client::POS_BODY_BOTTOM =>array(
                    'jquery.validate.min.js',
                    'form-validation.js',
                    'additional-methods.min.js',
                    'select2.min.js',
                    'app.js?theme=__THEME_NAME__&themePath=__THEME_PATH__//.js',
                ),
                Client::POS_HEAD_BOTTOM =>array(
                    'select2_metro.css',
                ),
            ),
            'index'=>array(
                Client::POS_BODY_BOTTOM =>array(
                        'jquery.dataTables.js',
                        'DT_bootstrap.js',
                        'fnMultiFilter.js',
                        'table-managed.js',
						'jQuery(document).ready(function() {
							App.init();
							TableManaged.init({"formName":"#menu_1","columns":[{"bSortable":false},{"bSortable":false,"sName":"id"},{"bSortable":false,"sName":"name","sClass":"center"},{"bSortable":false,"bSearch":""},{"bSortable":false,"sName":"desc"},{"bSortable":false,"sName":"parent_id"},{"bSortable":false,"bSearch":""},{"bSortable":false,"sName":"create_time"},{"bSortable":false,"bSearch":""}],"dataUrl":"'.urlencode(Sle::app()->route->genUrl('menu/index?act=getListData')).'","searchField":["","id","name","","desc","parent_id","","create_time"]});
						});',
                ),
                Client::POS_HEAD_BOTTOM=>array(
                        'DT_bootstrap.css',
                ),
            ),
            'add'=>array(
               Client::POS_BODY_BOTTOM=>array(
                    'chosen.jquery.min.js',
                ),
               Client::POS_HEAD_BOTTOM=>array(
                    'chosen.css',
                ),
            ),
            'update'=>array(
               Client::POS_BODY_BOTTOM=>array(
                    'chosen.jquery.min.js',
                ),
               Client::POS_HEAD_BOTTOM=>array(
                    'chosen.css',
                ),
            ),
    ),
    'moduleManage'=>array(
            'global' =>array(
                Client::POS_BODY_BOTTOM =>array(
                    'jquery.validate.min.js',
                    'form-validation.js',
                    'additional-methods.min.js',
                    'select2.min.js',
                    'app.js?theme=__THEME_NAME__&themePath=__THEME_PATH__//.js',
                ),
                Client::POS_HEAD_BOTTOM =>array(
                    'select2_metro.css',
                ),
            ),
            'index'=>array(
                Client::POS_BODY_BOTTOM =>array(
                        'jquery.dataTables.js',
                        'DT_bootstrap.js',
                        'fnMultiFilter.js',
                        'table-managed.js',
					    'jQuery(document).ready(function(){
						App.init();
						TableManaged.init({"formName":"#controller_form","columns":[{"bSortable":false},{"bSortable":false,"sName":"id"},{"bSortable":false,"sName":"name"},{"bSortable":false,"sName":"title"},{"bSortable":false,"sName":"desc"},{"bSortable":false}],"dataUrl":"'.urlencode(Sle::app()->route->genUrl('ModuleManage/index?act=getListData')).'","searchField":["","id","name","title","desc"]});
						FormValidation.init();
						});//.js',
                ),
                Client::POS_HEAD_BOTTOM=>array(
                        'DT_bootstrap.css',
                ),
            ),
            'add'=>array(
               Client::POS_BODY_BOTTOM=>array(
                    'chosen.jquery.min.js',
                ),
               Client::POS_HEAD_BOTTOM=>array(
                    'chosen.css',
                ),
            ),
            'update'=>array(
               Client::POS_BODY_BOTTOM=>array(
                    'chosen.jquery.min.js',
                ),
               Client::POS_HEAD_BOTTOM=>array(
                    'chosen.css',
                ),
            ),
    ),
);











