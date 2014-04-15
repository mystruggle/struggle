<?php
namespace struggle\libraries\cache;

class Cache{
    private $itsCacheDir = '';
    private $itsCacheType = '';
    
    public static function getInstance($sType = '', $aOpt = array()){
        static $aStorage = array();
        empty($sType) && $sType = \struggle\C('cache_engine');
        $sClassName = ucfirst($sType);
        if (!isset($aStorage[$sClassName])){
            if(!class_exists("struggle\\libraries\\cache\\driver\\{$sClassName}")){
                $sFileStorage = CORE_PATH.'libraries/cache/driver/'.$sType.'.php';
                include $sFileStorage;
            }
            //字符串类名，不能自动添加命名空间前缀，如果直接new test();可以
            $sClassName = 'struggle\\libraries\\cache\\driver\\'.$sClassName;
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







