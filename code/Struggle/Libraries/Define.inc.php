<?php

//如果是后台必须设置该常量SLE_FRONTEND
$sFrontend = '';
if (defined('SLE_FRONTEND')){
    $sFrontend = SLE_FRONTEND;
}

defined('APP_NAME') or define('APP_NAME', basename(dirname($_SERVER['SCRIPT_NAME'])));
defined('APP_ROOT') or define('APP_ROOT',rtrim(dirname($_SERVER['SCRIPT_FILENAME']),'/').'/');



defined('APP_PATH')      or define('APP_PATH','./');
defined('APP_CACHE')     or define('APP_CACHE', $sFrontend.'Caches/');
defined('APP_RUNTIME')   or define('APP_RUNTIME', $sFrontend.'Caches/Runtime/');
defined('APP_CONTROLLER') or define('APP_CONTROLLER', 'Controller/');
defined('APP_MODEL')     or define('APP_MODEL', $sFrontend.'Model/');
defined('APP_THEME')     or define('APP_THEME','Themes/');
defined('APP_PUBLIC')    or define('APP_PUBLIC',$sFrontend.'Public/');
defined('APP_LIB')       or define('APP_LIB','AddOnes/');
defined('APP_CONF')      or define('APP_CONF','Config/');


defined('LIB_PATH')       or define('LIB_PATH',SLE_PATH.'Libraries/');
defined('CONF_PATH')      or define('CONF_PATH',SLE_PATH.'Config/');
defined('PUBLIC_PATH')    or define('PUBLIC_PATH',SLE_PATH.'Public/');


