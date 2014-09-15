<?php
namespace struggle\libraries;

use struggle\Sle;
class Client extends Object{
    protected $mJs = array();
    private $mJsBasePath = '';
    const POS_HEAD_TOP = 1;
    const POS_HEAD_BOTTOM = 2;
    const POS_BODY_BEFORE = 3;
    const POS_BODY_AFTER  = 4;
    
    public function __construct(){
        parent::__construct();
        $sTheme = Sle::getInstance()->View->Theme;
        $this->mJsBasePath = APP_PUBLIC.$sTheme.'/js/';
    }
    public function registerClientJs($file,$pos = self::POS_HEAD_BOTTOM){
        $sFile = $file;
        $xRlt = array('status'=>true,'msg'=>'执行'.__METHOD__);
        !file_exists($sFile) && $sFile = $this->mJsBasePath.$file;
        if (file_exists($sFile) && is_string($sFile)){
            $this->mJs[$pos][] = "<script type='text/javascript' src='{$sFile}'></script>";
        }elseif ($file && is_string($sFile)){
            $this->mJs[$pos][] = "<script type='text/javascript'>\n{$sFile}\n</script>";
        }else{
            $xRlt['status'] = false;
            $xRlt['msg']    = '传递参数有误'.var_export($file,true).' '.__METHOD__.' line '.__LINE__;
        }
        $this->debug($xRlt['msg'], $xRlt['status']?E_USER_NOTICE:E_USER_ERROR,Sle::SLE_SYS);
        return $xRlt['status'];
    }









}