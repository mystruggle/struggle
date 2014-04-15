<?php
namespace struggle\libraries\core;

class Dispatcher{
    private $itsDefaultModule = '';
    private $itsDefaultAction = '';
    public function start(){
        $this->itsDefaultModule = 'index';
        $this->itsDefaultAction = 'index';
        
        struggle\C('DISPATCHER_DEFAULT_MODULE') && $this->itsDefaultModule = struggle\C('DISPATCHER_DEFAULT_MODULE');
    }
}