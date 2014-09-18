<?php
use struggle\libraries\Client;
//项目配置文件
echo Client::POS_BODY_BOTTOM;die;
return array(
        //配置加载的js,键名格式为项目名_模块名_方法名
        'JS'=>array(
		     'admin_Menu_index'=>array(
			     'Client::POS_BODY_BOTTOM' => 'select2.min.js',
			     'Client::POS_BODY_AFTER' => 'jquery.dataTables.js',
		     ),
		),
);