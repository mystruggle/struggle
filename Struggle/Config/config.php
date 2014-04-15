<?php 
/*
 * 全局配置文件
 */
return array(
    //调度器
    'DISPATCHER_DEFAULT_MODULE' => 'index',
    'DISPATCHER_DEFAULT_ACTION' => 'index',

    //缓存设置
    'CACHE_ENGINE'           =>'file',
    'CACHE_DIR'              =>'',
    //文件设置
//  'file_max_size'          =>'1024',  //kb
    //日志设置
    'LOG_TYPE'               =>'file',
    'LOG_BASE_PATH'          =>APP_CACHE,
    'LOG_PATH'               =>'Runtime/',
    'LOG_NAME'               =>'application',
    'LOG_EXT'                =>'log',
    'LOG_MAX_SIZE'           =>2000,  //kb
    //视图
    'VIEW_THEME_PATH'		 =>'',     //模板根目录
    'VIEW_THEME'             =>'default',
    'VIEW_SUFFIX'            =>'htm',
    //语言
    'LANG_NAME'              =>'zh_cn',
    //调试                                                            
    'DEBUG_CLASS'            =>'\struggle\libraries\Debug',
    'DEBUG_RECORD_METHOD'    =>'log',
    //路由
    'ROUTE_MODE'             =>'normal',//normal 正常模式、pathinfo pathinfo模式、rewrite 伪静态模式 、compat兼容模式/?s=/name/vlaue
);



define('URL_', 0);
define('URL_PATHINFO', 1);
define('URL_REWRITE', 2);
define('URL_COMPAT', 3);






