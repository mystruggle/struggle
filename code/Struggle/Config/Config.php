<?php 
/*
 * 全局配置文件
 */
return array(
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
    'VIEW_TPL_SUFFIX'        =>'html',
    //语言
    'LANG_NAME'              =>'zh_cn',
    //调试                                                            
    'DEBUG_LOG_TYPE'      =>'file',
    'DEBUG_LOG_FILE_NAME' =>'application',
    'DEBUG_LOG_FILE_DIR' =>APP_RUNTIME,
    'DEBUG_LOG_FILE_PATH' =>'',
    'DEBUG_LOG_FILE_EXT'  =>'log',
    'DEBUG_LOG_FILE_MODE' =>'ab',
    'DEBUG_LOG_FILE_SIZE' =>2000,  //kb
    'DEBUG_LOG_FILE_NUM'  =>3,
    /* 是否启用调试日志 */
    'DEBUG_ENABLED'          =>false,
    /* 是否展示调试页面  */
    'DEBUG_PAGE'             =>false,
    /* 是否保存调试信息 */
    'DEBUG_STORAGE'          =>false,
    /* 性能调试，程序执行时间  */
    'DEBUG_DISPLAY_TIME'     =>false,
    /* 错误等级
     * all 显示或记录来自系统平台和应用的所有信息;
     * sys 显示或记录来自框架信息;
     * app 显示或记录来自项目信息
     * error 显示或记录错误信息;
     * warning 显示或记录警告信息;
     * notice  显示或记录通知信息
     * other   显示或记录其他信息
     * 可以组合使用如sys,error 
     * */
    'DEBUG_LEVEL'            =>'all',
    //路由
    'ROUTE_MODE'             => 0 ,//0 普通模式、1 pathinfo模式、2 伪静态模式(rewrite) 、3 兼容模式(compat)/?s=/name/vlaue
    'ROUTE_DEFAULT_MODULE'   =>'index',
    'ROUTE_DEFAULT_ACTION'   =>'index',
    'ROUTE_MODULE_TAG'       =>'m',
    'ROUTE_ACTION_TAG'       =>'a',
    //自动包含跟目录设置
    'AUTOLOAD_DIR'           =>APP_LIB.','.LIB_PATH.','.LIB_PATH.'Cache/Driver/,'.APP_CONTROLLER,
    //语言设置
    'LANG_NAME'              =>'zh-cn',
    'LANG_CHARACTER_SET'     =>'utf-8',
    //数据库设置
    'DB_TYPE'                =>'pdo',    //数据库类型，pdo,mysql,
    'DB_DRIVER'              =>'mysql', //数据库驱动类型,sqlite ,mysql,sql server,oracle
    'DB_NAME'                =>'sle',       //数据库名
    'DB_USER'                =>'root',       //数据库用户名
    'DB_PWD'                 =>'',      //数据库用户密码
    'DB_HOST'                =>'127.0.0.1',      //数据库地址localhost
    'DB_PORT'                =>'3306',      //数据库端口
    'DB_DNS'                 =>'',      //数据库dns
    'DB_TABLE_SUFFIX'        =>'',      //表后缀
    'DB_TABLE_PREFIX'        =>'sle_',  //表前缀
);



define('URL_', 0);
define('URL_PATHINFO', 1);
define('URL_REWRITE', 2);
define('URL_COMPAT', 3);






