<?php
namespace struggle\libraries\core;
use struggle as sle;

class BaseModel extends \struggle\libraries\Object{
    /* 数据库连接 */
    protected $mDb  = null;
	protected $mType = '';   //数据库类型
	protected $mDriver = '';   //数据库驱动
	protected $mDbName     = '';  //数据库名
	protected $mHost   = '';  //数据库服务器ip
	protected $mPort   = '';  //数据库端口
	protected $mUser   = '';   //数据库用户名
	protected $mPwd    = '';   //数据库用户密码
	protected $mDns    = '';   //type为pdo时的连接dns
    protected $mDbIdent = '';  //驱动类标识，用于扩展多线程
    protected $mNewLink = false;
    protected $mCharset = '';
	private   $mDrvFileSuffix = '.driver.php';
	private   $mDrvClassSuffix = 'Driver';
	private   $mDrvNameSpace = '\struggle\libraries\db\driver\\';
	private   $mSelectElement = array('field'  =>'','join'=>'','where'=>'',
		                              'groupby'=>'','having'=>'','orderby'=>'','limit'=>'');


    public function __construct(){
        parent::__construct();
    }



    protected function _init(){
		$this->mType   = sle\C('DB_TYPE')?sle\C('DB_TYPE'):'pdo';
		$this->mDriver = sle\C('DB_DRIVER')?sle\C('DB_DRIVER'):'mysql';
		$this->mDbName = sle\C('DB_NAME')?sle\C('DB_NAME'):null;
		$this->mHost   = sle\C('DB_HOST')?sle\C('DB_HOST'):'127.0.0.1';
		$this->mPort   = sle\C('DB_PORT')?sle\C('DB_PORT'):'330';
		$this->mUser   = sle\C('DB_USER')?sle\C('DB_USER'):'root';
		$this->mPwd    = sle\C('DB_PWD')?sle\C('DB_PWD'):'';
		$this->mDns    = sle\C('DB_DNS')?sle\C('DB_DNS'):'';
        $this->mCharset = sle\C('LANG_CHARACTER_SET')?sle\C('LANG_CHARACTER_SET'):'utf8';
        $this->mCharset = str_replace('-','',$this->mCharset);

        $sModelName = str_replace(sle\C('MODEL.CLASS.SUFFIX'),'',basename(str_replace(array('/','\\'),'/',get_class($this))));
        if($sModelName){
            $sTableName = sle\ptoc($sModelName);
            $sTablePrefix = sle\C('DB_TABLE_PREFIX');
            $sTableSuffix = sle\C('DB_TABLE_SUFFIX');
            $sTableName = $sTablePrefix.$sTableName.$sTableSuffix;
            if(property_exists($this,'alias') && $this->alias){
                $this->mSelectElement['alias'] = $this->alias;
            }else{
                $this->mSelectElement['alias'] = strtolower($sModelName[0]);
            }
            $this->mSelectElement['table'] = $sTableName;
        }
    }


	protected function _Db(){
        static $aDb = array();
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
        $sKey = md5($sClassName.$this->mType.$this->mDriver.$this->mDbIdent);
        $aOpt = array('driver'=>$this->mDriver,
                      'type'=>$this->mType,
                      'host'=>$this->mHost,
                      'port'=>$this->mPort,
                      'dbname'=>$this->mDbName,
                      'user'=>$this->mUser,
                      'pwd'=>$this->mPwd,
                      'charset'=>$this->mCharset
                );
        if(!isset($aDb[$sKey]))
		    $aDb[$sKey] = new $sClassName($aOpt);
        //初始化表，读取表的元数据
        if($this->mSelectElement['table'] && $this->mSelectElement['alias'])
            $aDb[$sKey] ->initTableMetadata($this->mSelectElement['table'],$this->mSelectElement['alias']);
        return $this->mDb = $aDb[$sKey];
	}



    public function getAttr($name){
        return $this->Db->getAttr($name);
    }

    public function setAttr($name,$value){
        return $this->Db->setAttr($name,$value);
    }

    public function bindValue($name,$value = null){
        $this->Db->bindValue($name,$value);
    }

	public function bindParam($value){
		$this->Db->bindParam($value);
	}

    public function find($aOpt = array()){
		$this->initOption($aOpt);
		$this->mSelectElement['limit'] = '1';		
		echo print_r($this->mSelectElement,true),__METHOD__,'<br><br>';
        $this->Db->find($this->mSelectElement);
    }

    /**
	 * 查询条件解析
	 * 当传入的参数为字符串类型，字符串中可以使用反引号``标记一个表字段，用{}字符包裹值，
	   {}不能嵌套，如需要嵌套，则用&#123;表示{,&#125;表示}；数组的键名可以使用``标记表字段
	 * @param mixed $condition 数组类型或字符类型
	 * @return void
	 * @author luguo<luguo@139.com>
 	 */
	public function where($condition){
        $where = '';
		if(is_array($condition)){
			foreach($condition as $name=>$value){
				$where[$name] = $value;
			}
		}elseif(is_string($condition)){
            $where = $condition;
        }else{
			$this->debug("WHERE参数只能为数组或字符串类型".__METHOD__.' line '.__LINE__,E_USER_ERROR,sle\Sle::SLE_SYS);
		}
        $this->mSelectElement['where']=$where;
	}


    public function join($name){
        $aRelation = $this->relation;
        print_r($aRelation);
        return $this;
    }

    //获取列处理
	public function field($sField){
		$this->mSelectElement['field'] = $sField;
	}


	public function groupby(){
	}


	private function initOption($aOpt){
        static $aSqlElement = null;
		if(!$aSqlElement)
			$aSqlElement = array_keys($this->mSelectElement);
		if(is_array($aOpt)){
			foreach($aOpt as $name=>$option){
				if(in_array($name,$aSqlElement) && method_exists($this,$name)){
					$this->$name($option);
				}
			}
		}
        if(isset($aOpt['join']) && !empty($aOpt['join'])){
            if(isset($this->relation[$aOpt['join']]) && !empty($this->relation[$aOpt['join']])){
                $this->mSelectElement['join'] = $this->relation[$aOpt['join']];
            }
        }
	}

	/**
	 * 属性函数
	 * 可以使$obj->where 的效果跟$obj->where()一样的
	*/
	protected function _mWhere($param){
		$this->where($param);
	}

    //属性函数
	protected function _mField($param){
		$this->field($param);
	}









    public function findAll(){}
    public function findBySql(){}
    public function findAllBySql(){}








}





namespace  struggle\model;
class Model extends \struggle\libraries\core\BaseModel{}