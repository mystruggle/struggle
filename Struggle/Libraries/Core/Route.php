<?php
namespace struggle\libraries;
class Route extends Object{
    private $msUrl  = '';
    private $msMode = '';
	
    public function __construct($sUrl){
        $this->msMode = \C('ROUTE_MODE');
        $this->msUrl  = $sUrl;
        $this->doLog($sUrl, E_USER_NOTICE);
        $this->doLog("路由模式 {$this->msMode}", E_USER_NOTICE);
    }
    
    
    public function exec(){
        if ($this->msMode == 'normal'){
            print_r(parse_url($this->msUrl));
        }
    }
}