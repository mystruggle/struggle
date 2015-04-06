<?php
namespace Struggle\Libraries\cache;

class Cache{
    private $itsCacheDir = '';
    private $itsCacheType = '';
    
    public static function getInstance($sType = '', $aOpt = array()){
        static $aStorage = array();
        empty($sType) && $sType = \Struggle\C('cache_engine');
        $sClassName = ucfirst($sType);
        if (!isset($aStorage[$sClassName])){
            if(!class_exists("Struggle\\Libraries\\Cache\\Driver\\{$sClassName}")){
                $sFileStorage = CORE_PATH.'Libraries/Cache/Driver/'.$sType.'.php';
                include $sFileStorage;
            }
            //字符串类名，不能自动添加命名空间前缀，如果直接new test();可以
            $sClassName = 'Struggle\\Libraries\\Cache\\Driver\\'.$sClassName;
            $aStorage[$sClassName] = new $sClassName($aOpt);
        }
        return $aStorage[$sClassName];
    }


    public function save(){
    }


    public function get(){
    }


    public function update(){
    }


    public function remove(){
    }




}







