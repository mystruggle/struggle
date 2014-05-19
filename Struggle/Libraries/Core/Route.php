<?php
namespace struggle\libraries;
class Route extends Object{
    public  $url  = '';
    private $msMode = '';
	
    public function __construct($sUrl){
        $this->msMode = \C('ROUTE_MODE');
        $this->url    = $sUrl;
        $this->debug("路由模式{$this->msMode}", E_USER_NOTICE);
    }
    
    
    public function exec(){
        if ($this->msMode == 'normal'){
            print_r(parse_url($this->url));
        }
    }
}