<?php
namespace struggle\libraries\core;
use struggle as sle;

class BaseModel extends \struggle\libraries\Object{
    public  $alias = '';
    public  $mName = '';
    /* 数据库连接 */
    protected $mLink  = array();
	protected $mType = '';   //数据库类型
	protected $mDriver = '';   //数据库驱动
	protected $mDb     = '';  //数据库名
	protected $mUser   = '';   //数据库用户名
	protected $mPwd    = '';   //数据库用户密码
	protected $mDns    = '';   //type为pdo时的连接dns
	private   $mDrvFileSuffix = '.driver.php';
	private   $mDrvClassSuffix = 'Driver';
	private   $mDrvNameSpace = '\struggle\libraries\db\driver\\';

    public function __construct(){
        parent::__construct();
        static $aLink = array();
        if(!$this->mLink){
            
        }
    }



    protected function _init(){
		$this->mType   = sle\C('DB_TYPE')?sle\C('DB_TYPE'):'pdo';
		$this->mDriver = sle\C('DB_DRIVER')?sle\C('DB_DRIVER'):'mysql';
		$this->mDb     = sle\C('DB_NAME')?sle\C('DB_NAME'):null;
		$this->mUser   = sle\C('DB_USER')?sle\C('DB_USER'):'root';
		$this->mPwd    = sle\C('DB_PWD')?sle\C('DB_PWD'):'';
		$this->mDns    = sle\C('DB_DNS')?sle\C('DB_DNS'):'';
    }


	protected function _Link(){
		$sFileName = $this->mDriver;
		if(strtolower($this->mType) != strtolower($this->mDriver)){
			$sFileName = "{$this->mType}_{$this->mDriver}";
		}
		$sClassName = sle\ctop($sFileName);
		$sFileName = LIB_PATH."Db/Driver/{$sClassName}{$this->mDrvFileSuffix}";
		if(!sle\require_cache($sFileName)){
			$this->debug("目标文件不存在或不可读{$sFileName} 在".__METHOD__.' line '.__LINE__,E_USER_ERROR,sle\Sle::SLE_SYS);
		}
		$sClassName = $this->mDrvNameSpace.$sClassName.$this->mDrvClassSuffix;
		if(!class_exists($sClassName)){
			$this->debug("当前类不存在{$sClassName} 在".__METHOD__.' line '.__LINE__,E_USER_ERROR,sle\Sle::SLE_SYS);
		}
		$oDb = new $sClassName();
		$this->mLink = $oDb;
	}



    public function start(){
        $this->itsDefaultModule = 'index';
        $this->itsDefaultAction = 'index';
        
        sle\C('DISPATCHER_DEFAULT_MODULE') && $this->itsDefaultModule = struggle\C('DISPATCHER_DEFAULT_MODULE');
    }

    public function find(){
        if(property_exists($this,'Db')){
        }
    }
    public function findAll(){}
    public function findBySql(){}
    public function findAllBySql(){}
}





namespace  struggle\model;
class Model extends \struggle\libraries\core\BaseModel{}