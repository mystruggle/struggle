<?php
/**
 * 模型的基类
 * 
 * @author luguo <luguo@139.com>
 * 
 * @note
 * - 主键所在的表叫被参照表(被引用表、主表)，主键在另一表中做外键时所在的表叫做参照表(引用表、子表)。在参照完整性中有描述
 *
 */

namespace  struggle\model;
class Model extends \struggle\libraries\core\BaseModel{}

namespace struggle\libraries\core;
use struggle\Sle;
use struggle\libraries\Debug;
use struggle\ctop;

define('HAS_ONE',1);
define('BELONGS_TO',2);
define('HAS_MANY',3);
define('HAS_AND_BELONGS_TO_MANY',4);
define('MYSELF',10);

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
		                              'groupby'=>'','having'=>'','orderby'=>'','limit'=>'','count'=>'');
	private   $mAlias      = '';    //当前模型别名
	private   $mPriKey     = '';   //当前模型的主键
	private   $mCurModel = '';     //当前调用的模型


    public function __construct(){
        parent::__construct();
    }



    protected function _init(){
		$this->mType   = \struggle\C('DB_TYPE')?\struggle\C('DB_TYPE'):'pdo';
		$this->mDriver = \struggle\C('DB_DRIVER')?\struggle\C('DB_DRIVER'):'mysql';
		$this->mDbName = \struggle\C('DB_NAME')?\struggle\C('DB_NAME'):null;
		$this->mHost   = \struggle\C('DB_HOST')?\struggle\C('DB_HOST'):'127.0.0.1';
		$this->mPort   = \struggle\C('DB_PORT')?\struggle\C('DB_PORT'):'330';
		$this->mUser   = \struggle\C('DB_USER')?\struggle\C('DB_USER'):'root';
		$this->mPwd    = \struggle\C('DB_PWD')?\struggle\C('DB_PWD'):'';
		$this->mDns    = \struggle\C('DB_DNS')?\struggle\C('DB_DNS'):'';
        $this->mCharset = \struggle\C('LANG_CHARACTER_SET')?\struggle\C('LANG_CHARACTER_SET'):'utf8';
        $this->mCharset = str_replace('-','',$this->mCharset);

        $sModelName = str_replace('Model','',basename(str_replace(array('/','\\'),'/',get_class($this))));
        if($sModelName){
            if(property_exists($this,'alias') && $this->alias){
                $this->mAlias = $this->alias;
            }else{
                $this->mAlias = $sModelName;
            }
            $this->mCurModel = $sModelName;
			$this->mPriKey   = $this->priKey;
        }
    }

    public function __get($name){
        $sAttrName = 'm'.strtoupper($name[0]).substr($name, 1);
        if (property_exists($this, $sAttrName) && !is_null($this->$sAttrName))
            return $this->$sAttrName;
        $sMethodName = '_'.strtolower($name[0]).substr($name, 1);
        if (method_exists($this, $sMethodName))
            return $this->$sMethodName();
        return false;
    }
    
    
	protected function _db(){
        static $aDb = array();
		$sFileName = $this->mDriver;
		if(strtolower($this->mType) != strtolower($this->mDriver)){
			$sFileName = "{$this->mType}_{$this->mDriver}";
		}
		$sClassName = \struggle\ctop($sFileName);
		$sFileName = LIB_PATH."Db/Driver/{$sClassName}{$this->mDrvFileSuffix}";
		if(!\struggle\require_cache($sFileName)){
			Debug::trace("目标文件不存在或不可读{$sFileName} 在".__METHOD__.' line '.__LINE__,Debug::SYS_ERROR);
		}
		$sClassName = $this->mDrvNameSpace.$sClassName.$this->mDrvClassSuffix;
		if(!class_exists($sClassName)){
			Debug::trace("当前类不存在{$sClassName} 在".__METHOD__.' line '.__LINE__,Debug::SYS_ERROR);
		}
        $sKey = md5($sClassName.$this->mType.$this->mDriver.$this->mDbIdent);
        $aOpt = array('driver'=>$this->mDriver,
                      'type'=>$this->mType,
                      'host'=>$this->mHost,
                      'port'=>$this->mPort,
                      'dbname'=>$this->mDbName,
                      'user'=>$this->mUser,
                      'pwd'=>$this->mPwd,
                      'charset'=>$this->mCharset,
			          'alias'  =>$this->mAlias,
			          'model'  =>$this->mCurModel,
			          'priKey'=>$this->mPriKey,
                );
        if(!isset($aDb[$sKey]))
		    $aDb[$sKey] = new $sClassName($aOpt);
        //初始化表，读取表的元数据
        //if($this->mReferModel && $this->mAlias)
            //$aDb[$sKey] ->initTableMetadata($this->mTablePrefix.sle\ptoc($this->mReferModel.$this->mTableSuffix),$this->mAlias);
        return $this->mDb = $aDb[$sKey];
	}
	
	

    public function getAttr($name){
        return $this->db->getAttr($name);
    }


    /**
	 */
    public function setAttr($name,$value){
        return $this->db->setAttr($name,$value);
    }


    /**
	 * 设置获取模式
	 */
	public function setFetchMode($mode){
		return $this->db->setFetchMode($mode);
	}

    public function bindValue($name,$value = null){
        $this->db->bindValue($name,$value);
    }

	public function bindParam($value){
		$this->db->bindParam($value);
	}

    public function find($aOpt = array()){
		$this->mSelectElement['limit'] = '1';
        $aRecordset = $this->db->find($this->initOption($aOpt));
        $this->resetElement();
        return $aRecordset;
    }

    /**
	 * 查询条件解析
	 * 当传入的参数为字符串类型，字符串中可以使用反引号``标记一个表字段，用{}字符包裹值，
	   {}不能嵌套，如需要嵌套，则用&#123;表示{,&#125;表示}；数组的键名可以使用``标记表字段
	 * @param mixed $condition 数组类型或字符类型
	 * @return object
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
			Debug::trace("WHERE参数只能为数组或字符串类型".__METHOD__.' line '.__LINE__,Debug::SYS_ERROR);
		}
		$this->mSelectElement['where']=$where;
		return $this;
	}

    /**
	 * 关联查询
	 * @param string $name 关联名称  ($relation属性下的键名，该键名为关联模型名称)
	 * @param string $on   关联额外条件(用模型名称指明字段归属)
	 * @return mixed 成功返回模型对象resource 失败返回false 
	 * @author luguo@139.com
	 * @example join('User')
	 *          join('LEFT User RIGHT Role','User.id = Role.user_id')
	 *          join('User,Role')
	 * explain  - relation属性中的设置只保证表之间关联的条件成立，至于left join 还是right join 抑或 full join
	 *          等可在join表达式中(relation 中的名称)加入关键字如，LEFT、RIGHT、FULL,如join('LEFT User')默认INNER
	 *          - $on 变量针对每个表达式添加额外的关联条件
	 *          - 中间表也为一个类
	 *          - 两个表之间的所有类型关系，都可以用一对多关系处理，如一对一为一对多的一个特例；多对一为一对多的反向关系，多对多可以拆分成两个一对多。
	 *            一对一      一对多  的特列
	 *            一对多      一对多
	 *            多对一      一对多反向管理
	 *            多对多      拆分成两个一对多关系
	 *          - forginKey  两表之间只有一个表有forginKey，所以relation属性中forginKey都只表示HAS_MANY的forginKey
	 *            如，user与class是一对多的关系，user为HAS_MANY；class为BELONGS_TO; 
	 *            //这里的forginKey表示User中的class外键,beReferKey表示class中的id
	 *            relation=array('User'=>array('type'=>HAS_MANY,'forginKey'=>'class_id','beReferKey'=>'id'))
	 *            //这里的forginKey亦表示User中的class外键,beReferKey亦表示class中的id
	 *            relation=array('Class'=>array('type'=>BELONGS_TO,'forginKey'=>'class_id','beReferKey'=>'id'))
	 *          - 如果relation中的type为BELONGS_TO表明该表为被参考表，在上面所述中class为被参考表（即主表）
	 *          - 关联属性包含的元素有mSelectElement['join'] = array(
	 *                                                        'ModelName'=>array(
	 *                                                            'table'=>'',
	 *                                                            'type'=>,      //HAS_MANY,..
	 *                                                            'beReferKey'=>'',
	 *                                                            'forginKey'=>'',
	 *                                                            'midModel'=>'',
	 *                                                            'joinType'=>'',//INNER,..
	 *                                                            'alias'=>'',
	 *                                                            'extOn'=>'')
	 *                                                    )
	 *          
	*/
    public function join($name, $on = ''){
        $xRlt = array('status'=>true,'msg'=>'join方法');
		$name = str_replace(',',' inner ',$name);
		$aRelation = preg_split('/\s+/',$name);
		array_walk($aRelation ,create_function('&$item,$key','$item=trim($item);'));
		if(!in_array(strtolower($aRelation[0]),array('inner','full','left','right'))){
			array_unshift($aRelation,'inner');
		}
		for($i=0; $i < count($aRelation); $i+=2){
			if(!isset($this->relation[$aRelation[$i+1]]) || empty($this->relation[$aRelation[$i+1]])){
			    $xRlt['status'] = false;
			    $xRlt['msg']    = "关联关系不存在或为空{$aRelation[$i+1]} ".__METHOD__.' line '.__LINE__;
			    break;
			}
			$this->mSelectElement['join'][$aRelation[$i+1]] = $this->relation[$aRelation[$i+1]];
			$this->mSelectElement['join'][$aRelation[$i+1]]['table'] = $aRelation[$i+1];
			$this->mSelectElement['join'][$aRelation[$i+1]]['joinType'] = $aRelation[$i];

		}
		//on条件
		if ($xRlt['status'] && $on){
		    if (is_string($on)){
		        $this->mSelectElement['join']['on'] = $on;
		    }else{
		        $xRlt['status'] = false;
		        $xRlt['msg']    = 'on条件参数只能传递字符串形式 '.var_export($on,true).' '.__METHOD__.' line '.__LINE__;
		    }
		}
		
		if (!$xRlt['status']){
		    Debug::trace($xRlt['msg'],Debug::SYS_ERROR);
		}else{
			Debug::trace('join参数 '.print_r($this->mSelectElement,true).' '.__METHOD__.' line '.__LINE__,Debug::SYS_NOTICE);
		}
        return $this;
    }



    //获取列处理
	public function field($sField){
	    $xRlt = array('status'=>true,'msg'=>'');
	    $aField = explode(',', $sField);
	    $aRlt   = array();
	    $sAlias = '';
	    foreach ($aField as $field){
	        if ($iPos = strpos($field, '.')){
	            $sModelName = substr($field, 0,$iPos);
	            if(!$oModel = \struggle\M(\struggle\ctop($sModelName))){
	                $xRlt = $xRlt['status'] = false;
	                $xRlt = $xRlt['msg']    = "模型不存在{$sModelName}".__METHOD__.' line '.__LINE__;
	            }
	            if ($xRlt['status'] && property_exists($oModel, 'alias') && empty($oModel->alias)){
	                $xRlt = $xRlt['status'] = false;
	                $xRlt = $xRlt['msg']    = "模型{$sModelName}属性alias不存在或为空".__METHOD__.' line '.__LINE__;
	            }
	            if ($xRlt['status']){
	                $aRlt[] = str_replace($sModelName, $oModel->alias, $field);
	            }
	        }else{
	            $aRlt[] = $field;
	        }
	    }
	    if ($xRlt['status'])
		    $this->mSelectElement['field'] = implode(',', $aRlt);
	    else 
	        Debug::trace($xRlt['msg'], Debug::SYS_ERROR);
		return $this;
	}


	public function groupby(){
	}
	
	public function limit($start,$length){
	    $this->mSelectElement['limit'] = "{$start},{$length}";
	    return $this;
	}


	/**
	 * 解析以数组形式查询语句各部分
	 * @param array $aOpt
	 */
	private function initOption($aOpt){
        static $aSqlElement = null;
        $aSelectElemet = array();
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
        
        //如果条件空则从构造查询语句中去除
        foreach ($aSqlElement as $cond){
            if (isset($this->mSelectElement[$cond]) && $this->mSelectElement[$cond])
                $aSelectElemet[$cond] = $this->mSelectElement[$cond];
        }
        return $aSelectElemet;
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

	public function getTableName(){
		return \struggle\C('DB_TABLE_PREFIX').\struggle\ptoc($this->name).\struggle\C('DB_TABLE_SUFFIX');
	}




	/**
	 * 获取全部结果集
	 * @param array $options
	 * @return array
	 * @author luguo@139.com
	 */
    public function findAll($options = array()){
        $aResult = array();
        $aResult = $this->db->findAll($this->initOption($options));
        $this->resetElement();
        return $aResult;
    }
    
    public function count($opt = ''){
        $this->mSelectElement['count'] = 'count(*) as count';
        if (is_string($opt) && $opt)
            $this->mSelectElement['count'] = "count({$opt}) as count";
        return $this;
    }
    
    public function getCount(){
        return $this->db->count;
    }
    
    private function resetElement(){
        foreach ($this->mSelectElement as $name=>$value){
            $this->mSelectElement[$name] = '';
        }
        $this->db->reset();
    }
    
    
    public function findBySql(){}
    public function findAllBySql(){}








}




