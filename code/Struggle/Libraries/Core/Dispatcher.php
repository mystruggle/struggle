<?php
namespace struggle\libraries\core;
use struggle as sle;

class Dispatcher{
    private $itsDefaultModule = '';
    private $itsDefaultAction = '';
    public function start(){
        $this->itsDefaultModule = 'index';
        $this->itsDefaultAction = 'index';
        
        sle\C('DISPATCHER_DEFAULT_MODULE') && $this->itsDefaultModule = struggle\C('DISPATCHER_DEFAULT_MODULE');
    }
}