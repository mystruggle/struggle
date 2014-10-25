<?php
use struggle\libraries\Client;
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
                            'app.js',
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
                    'app.js',
                ),
                Client::POS_HEAD_BOTTOM =>array(
                    'select2_metro.css',
                ),
            ),
            'index'=>array(
                Client::POS_BODY_BOTTOM =>array(
                        //'select2.min.js',
                        'jquery.dataTables.js',
                        'DT_bootstrap.js',
                        'table-managed.js',
                        //'app.js',
                        //'jquery.validate.min.js',
                        //'additional-methods.min.js',
                        //'form-validation.js',
                ),
                Client::POS_HEAD_BOTTOM=>array(
                        //'select2_metro.css',
                        'DT_bootstrap.css',
                ),
            ),
            'add'=>array(
               Client::POS_BODY_BOTTOM=>array(
                    //'jquery.validate.min.js',
                    //'additional-methods.min.js',
                    //'select2.min.js',
                    'chosen.jquery.min.js',
                    //'app.js',
                    //'form-validation.js',
                ),
               Client::POS_HEAD_BOTTOM=>array(
                    //'select2_metro.css',
                    'chosen.css',
                ),
            ),
            'update'=>array(
               Client::POS_BODY_BOTTOM=>array(
                    //'jquery.validate.min.js',
                    //'additional-methods.min.js',
                    //'select2.min.js',
                    'chosen.jquery.min.js',
                    //'app.js',
                    //'form-validation.js',
                ),
               Client::POS_HEAD_BOTTOM=>array(
                    //'select2_metro.css',
                    'chosen.css',
                ),
            ),
    ),
);











