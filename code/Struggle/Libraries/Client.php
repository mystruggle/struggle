<?php
namespace struggle\libraries;

class Client extends Object{
    private $js = array();
    const POS_HEAD_TOP = 1;
    const POS_HEAD_BOTTOM = 2;
    const POS_BODY_BEFORE = 3;
    const POS_BODY_AFTER  = 4;
    public function registerClientJs($file,$pos = self::POS_HEAD_BOTTOM){die($file);
        if (file_exists($file)){
            //
        }
    }
}