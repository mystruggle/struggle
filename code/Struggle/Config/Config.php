<?php 
/*
 * 全局配置文件
 */
return array(
    //调度器
    'DISPATCHER_DEFAULT_MODULE' => 'index',
    'DISPATCHER_DEFAULT_ACTION' => 'index',
    'DISPATCHER_MODULE_TAG'     => 'm',
    'DISPATCHER_ACTION_TAG'     => 'a',

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
    'VIEW_THEME_PATH'		 =>APP_THEME,     //模板根目录
    'VIEW_THEME'             =>'Default',
    'VIEW_TPL_SUFFIX'        =>'htm',
    //语言
    'LANG_NAME'              =>'zh_cn',
    //调试                                                            
    'DEBUG_RECORD_TYPE'      =>'file',
    'DEBUG_RECORD_FILE_NAME' =>'application',
    'DEBUG_RECORD_FILE_PATH' =>APP_RUNTIME,
    'DEBUG_RECORD_FILE_EXT'  =>'log',
    'DEBUG_RECORD_FILE_MODE' =>'ab',
    'DEBUG_RECORD_FILE_SIZE' =>2000,  //kb
    'DEBUG_RECORD_FILE_NUM'  =>3,
    /* all显示和记录来自系统平台和应用的所有信息;sys只显示和记录来自系统平台信息;sys_err只显示和记录来自系统平台错误信息;sys_other只显示和记录来自系统平台除错误的其他信息;app只显示和记录来自应用信息;app_err只显示和记录来自应用错误信息;app_other只显示和记录来自应用除错误的其他信息     */
    'DEBUG_LEVEL'            =>'all',
    //路由
    'ROUTE_MODE'             =>'normal',//normal 正常模式、pathinfo pathinfo模式、rewrite 伪静态模式 、compat兼容模式/?s=/name/vlaue
    //自动包含跟目录设置
    'AUTOLOAD_DIR'           =>APP_LIB.','.LIB_PATH.','.LIB_PATH.'Cache/Driver/',
    //语言设置
    'LANG_NAME'              =>'zh-cn',
);



define('URL_', 0);
define('URL_PATHINFO', 1);
define('URL_REWRITE', 2);
define('URL_COMPAT', 3);






