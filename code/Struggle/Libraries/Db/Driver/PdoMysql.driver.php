<?php
namespace struggle\libraries\db\driver;
use struggle as sle;
use struggle\libraries\Debug;
use struggle\ctop;
use struggle\libraries\Exception;
/**
 * 模型运用例子
 * name='sys'
 * array('name'=>'sys')
 *
 * name>'sys'
 * array('`name`.gt'=>'sys')
 * 
 * (name='sys' and pwd>=1) or (desc<=2 and create_time in(2,3) )
 * array(array('name'=>'sys','`pwd`.ge'=>1),'_logic'=>'or',array('`desc`.le'=>2,'`create_time`.in'=>array(2,3)))
 *
 * $sys='sys';
 * $pwd=1;
 * $desc='2&#123;';
 * $create_time=array(2,3);
 * (name='sys' and pwd>=1) or (desc<='2{' and create_time in(2,3) )
 * (`name`={{$sys}} and `pwd`>={{$pwd}}) or(`desc`<={{$desc}} and `create_time` in ({{$create_time}}))  注意，需用双引号括起来；注意用反引用号括住表字段
 *
 * $this->bindValue(array(':name'=>'sys',':pwd'=>1,':a'=>'2{',':b'=>2,':c'=>3));
 * (name='sys' and pwd>=1) or (desc<='2{' and create_time in(2,3) )
 * (`name`=:name and `pwd` >=:pwd) or (`desc`<=:a and `create_time` in(:b,:c))
 *
 * $this->bindParam(array('sys',1,'2{',2,3));
 * (name='sys' and pwd>=1) or (desc<='2{' and create_time in(2,3) )
 * (name=? and pwd >=?) or (desc<=? and create_time in(?,?))
 */


class PdoMysqlDriver extends \struggle\libraries\db\Db{
	protected $mLink = null;
    protected $mErrorInfo = '';
    protected $mErrorCode = '';
    protected $mConnTimeOut = 30;  //连接超时
    protected $mConnErrMode = \PDO::ERRMODE_WARNING;  //错误报告模式ERRMODE_EXCEPTION
    protected $mTableInfo   = array(); //表结构信息
    protected $mTableFullName = '';
    protected $mAlias         = '';  //别名
	private   $mPriKey        = '';  //主键
	private   $mModel         = '';  //模型名称(模型类名称)
	//保存查询语句每个部分，如mSelectInfo['where']
    protected $mSelectInfo    = array();
    protected $mBindParam     = array();  //绑定的参数
	private   $mPdoStatement  = null;
	protected $mFetchMode     = \PDO::FETCH_BOTH;
	private   $mFieldRegexp      = '/`([^`]+)`(?:\.(gt|lt|ge|le|eq|neq|in|link|notin)$)?/i';
	private   $mFieldExpMap   = array("gt"=>">","lt"=>"<","ge"=>">=","le"=>"<=","eq"=>"=");
	private   $mDbIntegerType = array('tinyint','smallint','mediumint','int','integer','bigint',
		                              'float','double','decimal','numeric','bit');
	private   $mTablePrefix = '';
	private   $mTableSuffix = '';
	public    $count = null;//记录总数


    public function __construct($aOpt){
        parent::__construct();
		$this->mAlias = $aOpt['alias'];
		$this->mPriKey = $aOpt['priKey'];
		$this->mTablePrefix = sle\C('DB_TABLE_PREFIX');
		$this->mTableSuffix = sle\C('DB_TABLE_SUFFIX');
		$this->mModel  = $aOpt['model'];
        $this->connect($aOpt);
		$this->initTableMetadata($this->mTablePrefix.sle\ptoc($aOpt['model']).$this->mTableSuffix,$this->mAlias);
    }

    public function _init(){
    }


	public function connect($aOpt){
		static $oLink = null;
        if(!$oLink){
            $sConnCharset= "set character_set_client={$aOpt['charset']},character_set_connection={$aOpt['charset']},character_set_results=binary";
            $sDns = "{$aOpt['driver']}:host={$aOpt['host']};port={$aOpt['port']};dbname={$aOpt['dbname']}";
            $oLink = new \PDO($sDns,$aOpt['user'],$aOpt['pwd'],array(\PDO::ATTR_ERRMODE=>$this->mConnErrMode,
                                                                     \PDO::ATTR_TIMEOUT=>$this->mConnTimeOut,
                                                                     \PDO::MYSQL_ATTR_INIT_COMMAND=>$sConnCharset));
//            $oLink->exec('set names utf8');
            $this->mErrorInfo = $oLink->errorInfo();
            $this->mErrorCode = $oLink->errorCode();
            if (!$this->mErrorCode){
                //禁用模拟预处理语句
                $oLink->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
            }
        }
        return $this->mLink = $oLink;
	}

	public function initTableMetadata($sTab,$sAlias){
		static $aMetadata;
		$sKey = md5("{$sTab}.{$sAlias}");
		if(!isset($aMetadata[$sKey])){
			$sMetaSql = "desc {$sTab}";
			$oStatement = $this->mLink->query($sMetaSql);
			$aTmp=$oStatement->fetchAll(\PDO::FETCH_ASSOC);
            $aTableInfo=array();
			foreach($aTmp as $info){
                $sField = $info['Field'];
                unset($info['Field']);
                $aTableInfo[$sField] = $info;
            }
            $this->mTableFullName = $sTab;
            $this->mAlias         = $sAlias;
            $aMetadata[$sKey] = $aTableInfo;
		}
        $this->mTableInfo[$sTab] = $aMetadata[$sKey];
	}


	public function setAttr($sName,$mVal){
		return $this->mLink->setAttribute($sName,$mVal);
	}

    public function getAttr($sName){
        return $this->mLink->getAttribute($sName);
    }

	public function setFetchMode($mode){
		if($mode){
		    $this->mFetchMode = $mode;
			return false;
		}
		return true;
	}
	
	
	/**
	 * 获取表字段
	 * @param string   $table  表名
	 * @return  array
	 */
	public function getFields($table){
	    return isset($this->mTableInfo[$table])?array_keys($this->mTableInfo[$table]):array();
	}

    public function find($aOpt = array()){
        $this->parseOpt($aOpt);
		$this->prepare($this->buildSelect());
		$this->_beginBind();
		$this->execute();
		$aRecordset = $this->fetch();
        $this->isCount();
        return $aRecordset;
    }
    
    public function findAll($options = array()){
        $this->parseOpt($options);
        $this->prepare($this->buildSelect());
        $this->_beginBind();
        $this->execute();
        $aRecordset = $this->fetchAll();
        $this->isCount();
        return $aRecordset;
    }
    
    /**
     * 检查是否需要统计
     */
    private function isCount(){
        if (isset($this->mSelectInfo['count']) && $this->mSelectInfo['count']){
            $this->prepare($this->buildSelectCount());
            $this->execute();
            $aCount = $this->fetch();
            $this->count = $aCount['count'];
        }
    }
    
    
    public function reset(){
        $this->mSelectInfo = array();
    }

    /**
     * 参数绑定(占位符不能混合使用)
     * @throws \Exception
     */
	private function _beginBind(){
		if (empty($this->mBindParam))
		    return;
		//$iMatchNum问号占位符对个数；$this->mSelectInfo存放查询语句各部分
		if(is_numeric(key($this->mBindParam)) && ($iMatchNum = preg_match_all('#`([^`{]+)`(?:[^{`]+\?)+#i',implode(' ',$this->mSelectInfo),$arr3))){
			//问号占位符绑定
			$iNum = count($this->mBindParam);
            $aField = array();   //正则提取出来的字段
            $aFieldStr = array();  //正则子模式匹配的字符串
            $aValueNum = array();  //值的个数
			
            if($iMatchNum && isset($arr3[0])){
                $aFieldStr = $arr3[0];
            }

            if($iMatchNum && isset($arr3[1])){
                $aField = $arr3[1];  //提取出来的表字段名
                //计算每对占位符对中问号的个数
                foreach($aFieldStr as $index=>$str){
                    for ($ii=0;$ii<substr_count($str, '?');$ii++){
                        $aValueNum[]=$aField[$index];
                    }
                }
            }
            
			if($iNum>0 && (count($aValueNum) === $iNum)){
				for($i=0; $i < $iNum; $i++){
				    $sKey = $aValueNum[$i];
					if(!isset($this->mTableInfo[$this->mTableFullName][$sKey]['Type']))
						throw new \Exception("字段{$sKey}不存在!");
					$sFieldType = $this->mTableInfo[$this->mTableFullName][$sKey]['Type'];
					$sFieldType = strtolower(substr($sFieldType,0,strpos($sFieldType,'(')));
					$iDataType = \PDO::PARAM_STR;
					if(in_array($sFieldType,$this->mDbIntegerType)){
						$iDataType = \PDO::PARAM_INT;
					}
					$this->mPdoStatement->bindParam(($i+1),$this->mBindParam[$i],$iDataType);
				}
			}elseif($iNum>0){
				Debug::trace("SQL绑定参数个数错误:".print_r($this->mBindParam,true),Debug::SYS_ERROR);
			}
		}else{
			//其他占位符绑定
		    foreach($this->mBindParam as $name=>$value){
    			if(preg_match('/:(.+?)(?:\_\d+)?$/i',$name,$arr)){
    				$sField = $arr[1];
    				if(!isset($this->mTableInfo[$this->mTableFullName][$sField]['Type'])){
    					//当绑定的参数数组的键名中不包含表字段时,即命名没有“:表字段”
    					if(preg_match('/`([^`]+)`[^`]+(?=:'.$sField.')/i',implode(' ',$this->mSelectInfo),$arr2)){
    						$sField = $arr2[1];
    					}
    					if(!isset($this->mTableInfo[$this->mTableFullName][$sField]['Type']))
    					    throw new \Exception("字段{$sField}不存在!");
    				}
    				$sFieldType = $this->mTableInfo[$this->mTableFullName][$sField]['Type'];
    				$sFieldType = strtolower(substr($sFieldType,0,strpos($sFieldType,'(')));
    				$iDataType = \PDO::PARAM_STR;
    				if(in_array($sFieldType,$this->mDbIntegerType)){
    					$iDataType = \PDO::	PARAM_INT;
    				}
    				$this->mPdoStatement->bindValue($name,$value,$iDataType);
    			}
		    }
		}
		Debug::trace("SQL绑定参数:".print_r($this->mBindParam,true),Debug::SYS_NOTICE);
		//每次查询后清空绑定的参数
		$this->mBindParam = array();
	}
	
	/**
	 * 替换别名
	 * @param array $matchs  匹配数组
	 * @return string
	 * @author luguo@139.com
	 */
	private function tAlias($matchs){
	    $xRlt = array('status'=>true,'msg'=>'');
	    $sOrgi = $matchs[0];
	    $sSearch = $matchs[1];
	    $sReplace = $sSearch;
	    if($oModel = sle\M(sle\ctop($sSearch))){
	        if(property_exists($oModel, 'alias')){
	            $sReplace = $oModel->alias;
	        }else{
	            $xRlt['status'] = false;
	            $xRlt['msg'] = "模型{$sSearch}属性alias 不存在 ".__METHOD__.' line '.__LINE__;
	        }
	    } 
	    if (!$xRlt['status'])
	        Debug::trace($xRlt['msg'], Debug::SYS_ERROR);
	    $sOrgi = str_replace($sSearch, $sReplace, $sOrgi);
	    return $sOrgi;
	}

	private function buildSelect(){
		$sField = isset($this->mSelectInfo['field'])?$this->mSelectInfo['field']:'';
		$sWhere = isset($this->mSelectInfo['where'])?$this->mSelectInfo['where']:'';
		$sTable = $this->mTableFullName;
		$sAlias = $this->mAlias;
		$sJoin  = isset($this->mSelectInfo['join'])?$this->mSelectInfo['join']:'';
		$sGroupby = isset($this->mSelectInfo['groupby'])?$this->mSelectInfo['groupby']:'';
		$sHaving  = isset($this->mSelectInfo['having'])?$this->mSelectInfo['having']:'';
		$sOrderby = isset($this->mSelectInfo['orderby'])?$this->mSelectInfo['orderby']:'';
		$sLimit   = isset($this->mSelectInfo['limit'])?$this->mSelectInfo['limit']:'';
		$sSql = "SELECT {$sField} FROM {$sTable} AS {$sAlias} {$sJoin} {$sWhere} {$sGroupby} {$sHaving} {$sOrderby} {$sLimit}";
		Debug::trace("SQL语句拼接:{$sSql}",Debug::NOTICE);
		return $sSql;
	}
	
	private function buildSelectCount(){
		$sField = $this->mSelectInfo['count'];
		$sWhere = isset($this->mSelectInfo['where'])?$this->mSelectInfo['where']:'';
		$sTable = $this->mTableFullName;
		$sAlias = $this->mAlias;
		$sJoin  = isset($this->mSelectInfo['join'])?$this->mSelectInfo['join']:'';
		$sGroupby = isset($this->mSelectInfo['groupby'])?$this->mSelectInfo['groupby']:'';
		$sHaving  = isset($this->mSelectInfo['having'])?$this->mSelectInfo['having']:'';
		$sOrderby = isset($this->mSelectInfo['orderby'])?$this->mSelectInfo['orderby']:'';
		$sLimit   = 'LIMIT 1';
		$sSql = "SELECT {$sField} FROM {$sTable} AS {$sAlias} {$sJoin} {$sWhere} {$sGroupby} {$sHaving} {$sOrderby} {$sLimit}";
		Debug::trace("SQL语句拼接:{$sSql}",Debug::NOTICE);
		return $sSql;
	}


    /**
	 * 解析查询语句各部分
	 * 将会调用传递过来的参数以其键名命名的方法
	 * 如，_field,_join,_where,_groupby,_having,_orderby,_limit
	 * @param array $aOpt  传递的参数
	 * @return void
	 * @author luguo<luguo@139.com>
	 */
    private function parseOpt($aOpt){
        if(is_array($aOpt)){
            foreach($aOpt as $name=>$param){
                $sMethodName = "_{$name}";
                if(method_exists($this,$sMethodName)){
                    $this->$sMethodName($param);
                }
            }
        }
		//获取列处理
        if(!isset($this->mSelectInfo['field']) || empty($this->mSelectInfo['field'])){
            $this->mSelectInfo['field'] = "*";
        }
		Debug::trace("select 语句参数信息 : ".print_r($this->mSelectInfo,true).__METHOD__.' line '.__LINE__,Debug::SYS_NOTICE);
    }


    /**
	 * 选择列
	*/
    private function _field($sField){
		if(is_string($sField))
		    $this->mSelectInfo['field'] = $sField;
		return false;
    }
    
    private function _count($opt){
        $this->mSelectInfo['count'] = $opt;
    }


	/**
	 * 关联关系处理
	 * 只能关联查询跟参考模型的有关系的模型，即所有关联模型的外键都是与参考模型主键进行关联reference.key=join.key
	 * @param array $param  
	 *        array('model_name'=>array('table'=>'','joinType'=>'','forginKey'=>'','beReferKey'=>'','midModel'=>'','extOn'=>''),'on'=>'')
	 * @return
	 * @author luguo@139.com
	*/
    private function _join($param){
		$aJoin = array();
		$aJoinOn = array();
		$sKey  = '';
		$sForginKey   = '';
		$sAlias= '';
		$sJoinAlias = '';
		$aModelMapp = array();
		$xRlt = array('status'=>true,'msg'=>'执行'.__METHOD__);
		if (isset($param['on']) && $param['on']){
		    $aJoinOn[] = $param['on'];
		    unset($param['on']);
		}
		foreach($param as $model=>$relation){
			if($relation['type'] == HAS_AND_BELONGS_TO_MANY){
				$sMiddleTable = sle\ctop($relation['middleTable']);
				$oMiddleModel = sle\M($sMiddleTable);
				if($oMiddleModel){
					if(!property_exists($oMiddleModel,'alias') || empty($oMiddleModel->alias)){
					    $xRlt['status'] = false;
					    $xRlt['msg']    = "{$sMiddleTable} alias属性不存在或为空".__METHOD__.' line '.__LINE__;
					}

					if(!isset($oMiddleModel->relation[$this->mBeReferModel]['forginKey']) || empty($oMiddleModel->relation[$this->mBeReferModel]['forginKey'])){
					    $xRlt['status'] = false;
					    $xRlt['msg']    = "{$sMiddleTable} {$this->mBeReferModel}关系中forginKey不存在或为空 ".__METHOD__.' line '.__LINE__;
					}
					if($xRlt['status']){
					    $sMiddleAlias = $oMiddleModel->alias;
						$sMiddleForginKey = $oMiddleModel->relation[$this->mBeReferModel]['forginKey'];
					    $sJoin .= " INNER JOIN ".$oMiddleModel->getTableName()." AS {$sMiddleAlias} ON {$this->mAlias}.{$this->mBeReferKey} = {$sMiddleAlias}.{$sMiddleForginKey} ";
						$oModel = sle\M($model);
						if($oModel){
							if(!property_exists($oModel,'alias') || empty($oModel->alias)){
        					    $xRlt['status'] = false;
        					    $xRlt['msg']    = "{$model} alias属性不存在或为空".__METHOD__.' line '.__LINE__;
							}
							if(!property_exists($oModel,'priKey') || empty($oModel->priKey)){
        					    $xRlt['status'] = false;
        					    $xRlt['msg']    = "{$model} priKey属性不存在或为空".__METHOD__.' line '.__LINE__;
							}

							if(!isset($oMiddleModel->relation[$model]['forginKey']) || empty($oMiddleModel->relation[$model]['forginKey'])){
        					    $xRlt['status'] = false;
        					    $xRlt['msg']    = "{$sMiddleTable} {$model}关系中forginKey不存在或为空 ".__METHOD__.' line '.__LINE__;
							}

							if(!$xRlt){
								$sModelAlias = $oModel->alias;
								$sModelPriKey = $oModel->priKey;
								$sMiddleModelForginKey = $oMiddleModel->relation[$model]['forginKey'];
								$sJoin .= strtoupper($relation['joinType'])." JOIN ".$oModel->getTableName()." AS {$sModelAlias} ON {$sModelAlias}.{$sModelPriKey}={$sMiddleAlias}.{$sMiddleModelForginKey} ";
							}
						}else{
    					    $xRlt['status'] = false;
    					    $xRlt['msg']    = "模型不存在{$model} ".__METHOD__.' line '.__LINE__;
						}
					}

				}else{
				    $xRlt['status'] = false;
				    $xRlt['msg']    = "模型不存在,请检查模型名称 :{$sMiddleTable} ".__METHOD__.' line '.__LINE__;
				}
			}else{
				//除了多对多关系的其他关系处理
				$oModel = sle\M(sle\ctop($model));
				$aModelMapp[$model] = $oModel->alias;
				if (isset($relation['on'])){
				    $aJoinOn[] = $relation['on'];
				}
				//关联模型的别名
				$sJoinModelAlias = isset($relation['alias'])?$relation['alias']:$oModel->alias;
				//判断联系类型
				if ($relation['type'] == HAS_MANY || $relation['type'] == HAS_ONE){
				    //被参照表键赋值
				    $sKey = isset($relation['beReferKey'])?$relation['beReferKey']:$this->mPriKey;
				    $sForginKey = $relation['forginKey'];
				    $sAlias = $this->mAlias;
				    $sJoinAlias = $sJoinModelAlias;
				}elseif ($relation['type'] == BELONGS_TO){
				    $sKey = isset($relation['beReferKey'])?$relation['beReferKey']:$oModel->priKey;
				    $sForginKey = $relation['forginKey'];
				    $sAlias = $sJoinModelAlias;
				    $sJoinAlias = $this->mAlias;
				}else{
				    $xRlt['status'] = false;
				    $sRlt['msg']    = '联系类型不存在('.$model.') '.__METHOD__.' line '.__LINE__;
				}
				
				
                if($xRlt['status']){
						$aJoin[] = strtoupper($relation['joinType'])." JOIN ".($this->mTablePrefix.sle\ptoc($model).$this->mTableSuffix)." AS {$sJoinModelAlias} ON {$sAlias}.{$sKey} = {$sJoinAlias}.{$sForginKey}";
				}
			}



		}//end foreach
		

		
		
		if($xRlt['status']){
    		//去掉扩展on条件中的模型名称用模型的别名代替
    		$aModelMapp[$this->mModel] = $this->mAlias;
    		$sJoinOn = str_replace(array_keys($aModelMapp),array_values($aModelMapp), implode(' AND ', $aJoinOn));
    		$sJoin = implode(' ', $aJoin);
    		if ($sJoinOn){
    		    $sJoin .= ' AND '.$sJoinOn;
    		}
    		
		    Debug::trace('join拼接后的字符串 '.$sJoin.' '.__METHOD__.' line '.__LINE__, Debug::SYS_NOTICE);
		}else{
	        Debug::trace($xRlt['msg'],Debug::SYS_ERROR);
		}
		$this->mSelectInfo['join'] = $sJoin;
    }

    /**
	 * 解析WHERE条件语句参数，由parseOpt方法调用
	 * 不需要bind(自动绑定类型)
	 *   数组类型, 键名中使用``(标注表字段),ge(>=),in,like等等
	 *   字符串类型，使用``标记表字段，{{}}标记值
	 * 需要bind(自动判断类型绑定)
	 *   数组类型，主要是``标记表字段，占位符不含有表字段时
	 *   字符串类型，无
	 * 最后把所有没有占位符的自动添加占位符，格式如:name，然后预处理再执行操作
	 * 会调用_Where开头函数
	 * @param mixed $param  传入的参数 
	 * @return void
	 * @author luguo<luguo@139.com>
	 */
    private function _where($param){
        $aSql = array();
		$sepa  = 'AND';
        if(is_array($param)){
		    uasort($param,array($this,'btosSort'));
			Debug::trace("where参数排序后=>".print_r($param,true).__METHOD__.' line '.__LINE__,Debug::SYS_NOTICE);
            foreach($param as $name=>$p){
                $sMethod = "_Where".ucfirst($name);
                if(method_exists($this,$sMethod)){
                    $this->$sMethod($p);
                    continue;
                }
				if(is_numeric($name) && is_array($p)){
					$aSql[]=$this->traversalArr($p,true);
					Debug::trace("解析where数组参数后=>".print_r(end($aSql),true).__METHOD__.' line '.__LINE__,Debug::SYS_NOTICE);
				}else{
					if(strtolower($name) === '_logic'){
						$sepa = strtoupper($p);
						continue;
					}
                    //过滤array('value')这种情况
                    if(is_numeric($name) && is_string($p)){
                        Debug::trace("错误的WHERE参数=> {$name}=>{$p}".print_r($param,true).__METHOD__.' line '.__LINE__,Debug::SYS_ERROR);
                    }else{
					    $aSql[] = $this->reassoc($name,$p);
                    }
				}
            }//end foreach
			if($aSql){
				$this->mSelectInfo['where'] = 'WHERE '.implode(" {$sepa} ",$aSql);
			}
        }//end array  
        else{
			//没有`` [^`{] {}格式将不会调用fetchFieldValue函数
			$param = preg_replace_callback('#`([^`{]+)`(?:[^{`]+\{[^}`]+\})+#i',array($this,'fetchFieldValue'),$param);
            $this->mSelectInfo['where'] = "WHERE {$param}";
        }
		//替换表别名
		$this->mSelectInfo['where'] = preg_replace_callback('/([^\s]+)(?=\.`)/', array($this,'tAlias'), $this->mSelectInfo['where']); 
    }




    /**
     * 把占位符统一改成“:表字段”，绑定参数数组的键名也统一替换成表字段，如“array(':表字段'=>value)”
     * 替换键名中.ge、.gt、.le等特殊符号
     * @param string $key   数组键名
     * @param mixed  $val   数组值
     * @return void
     * @author luguo<luguo@139.com>
     */
    private function reassoc($key,$val){
        $sField = $key;
        $sOp    = 'eq';
        $sBindField = ":{$sField}";
        $aBindValues = array();
        //筛选操作符，替换操作符
        preg_match($this->mFieldRegexp,$key,$arr);
        if(isset($arr[1])){
            $sField = $arr[1];
            $sBindField = ":{$sField}";
        }
        //提取出来的操作符
        if(isset($arr[2])){
            $sOp = $arr[2];
        }
        
        //用表字段替换原绑定值的键
        $aBindValues[$sBindField] = $val;

        switch(strtolower($sOp)){
            case 'in':
            case 'notin':
                if(is_array($val)){
                   for($i=0;$i<count($val);$i++){
                       if($i){
                         $sTmpKey = ":{$sField}_{$i}";
                         $aBindValues[$sTmpKey] = $val[$i];
                         $sBindField .= ",{$sTmpKey}";
                       }else{
                         $aBindValues[":{$sField}"] = $val[$i];
                       }
                   }
                   $sBindField = "({$sBindField})";
                }elseif(is_string($val)){
                    $aBindValues[":{$sField}"] = $val[$i];
                    $sBindField = "({$sBindField})";
                }
                break;
            default:
        }
        isset($this->mFieldExpMap[$sOp]) && $sOp = $this->mFieldExpMap[$sOp];
        $this->mBindParam = array_merge($this->mBindParam,$aBindValues);
        return "{$this->mAlias}.`{$sField}` {$sOp} {$sBindField}";
    }

	/**
	 * 在字符串SQL语句中使用特殊字符进行解析和绑定参数
	 * 特殊符号反引号``用于界定表字段，大括号{}界定绑定参数的值
	 * 如,`name`={{$name}}
	 * @param array $matchs  提取含有特殊符号字符串片段，匹配格式如`name` like {{$name}}
	 * @return void
	 * @author luguo@139.com
 	 */
	private function fetchFieldValue($matchs){
		$aStr = array();
		$sField = $matchs[1];
		$iValStartPos = strpos($matchs[0],'{');
		$sPreVal = substr($matchs[0],0,$iValStartPos);
		$aStr[] = $sPreVal;
		$sVal = substr($matchs[0],$iValStartPos,strrpos($matchs[0],'}'));
		$iLeftSepPos = 0;
		$iRightSepPos = 0;
		$i = 1;
		while(true){
			$iLeftSepPos = strpos($sVal,'{',$iRightSepPos);
			if($iLeftSepPos === false)
				break;
			if($i>1){
				$aStr[] = substr($sVal,$iRightSepPos+1,$iLeftSepPos-$iRightSepPos-1);
			}
			$iRightSepPos = strpos($sVal,'}',$iLeftSepPos);
			if($i === 1)
			    $sKey = ":{$sField}";
			else
				$sKey = ":{$sField}_".($i-1);
			$aStr[] = $sKey;
			$sValue = substr($sVal,$iLeftSepPos+1,$iRightSepPos-$iLeftSepPos-1);
			$this->mBindParam[$sKey] =str_replace(array('&#123;','&#125;'),array('{','}'),$sValue);
			$i++;
		}
		return implode('',$aStr);
	}

	private function _groupby($param){
		$sGroupby = '';
		if($param){
		}
		$this->mSelectInfo['groupby'] = $sGroupby;
	}

	private function _having($param){
		$sHaving = '';
		if($param){
		}
		$this->mSelectInfo['having'] = $sHaving;
	}

	private function _orderby($param){
		$sOrderby = '';
		if($param){
			//
		}
		$this->mSelectInfo['orderby'] = $sOrderby;
	}



	private function _limit($param){
		if(is_string($param)){
		    $this->mSelectInfo['limit'] = "LIMIT {$param}";
	    }
	}



    /**
	 * 参数排序，让数组排在后面(由大到小排序)
	 */
	private function btosSort($param1,$param2){
		//param1<param2  return 1;
		//param1>param2  reutrn -1
		//param1=param2  return 0
		if(is_array($param1))return 1;
		if(is_array($param2))return -1;
		return 0;
	}

    /**
	 * 开始事务
	 * @return boolean 
	 */
	public function beginTransaction(){
		return $this->mLink->beginTransaction();
	}


    /**
	 * 事务提交
	 * @return boolean 
	 */
	public function commit(){
		return $this->mLink->commit();
	}


    /**
	 * 事务回滚
	 * @return boolean
	 */
	public function rollback(){
		return $this->mLink->rollBack();
	}

	/**
	 * 返回具体驱动错误码
	 */
	public function getErrorCode(){
		return $this->mErrorInfo[1];
	}

	/**
	 * 返回具体驱动错误信息
	 */
	public function getErrorInfo(){
		return $this->mErrorInfo[2];
	}

	public function execute($param = array()){
		if(!$this->mPdoStatement->execute()){
		    $this->mErrorInfo = $this->mPdoStatement->errorInfo();
			$this->mErrorCode = $this->mPdoStatement->errorCode();
		}
	}

	public function bindValue($name,$value){
		if(is_string($name)){
		    $this->mBindParam[$name] = $value;
			return true;
		}elseif(is_array($name)){
            foreach($name as $key=>$val){
                $this->mBindParam[$key] = $val;
            }
            return true;
        }
		return false;
	}

    /**
	 * 按索引绑定参数
	 * @param mixed $value  需要绑定的参数，数组或字符串
	 * @return void
	 * @author luguo<luguo@139.com>
	 */
    public function bindParam($value){
		if($this->mBindParam){
			$this->mBindParam = array();
		}
		if(is_array($value)){
			$this->mBindParam = array_merge($this->mBindParam,$value);
		}else{
			$this->mBindParam[] = $value;
		}
	}

	public function fetch(){
		return $this->mPdoStatement->fetch($this->mFetchMode);
	}

	public function fetchAll(){
		return $this->mPdoStatement->fetchAll($this->mFetchMode);
	}





    private function _query($sSql){
    }

	public function prepare($sSql,$aParam = array()){
		$this->mPdoStatement = $this->mLink->prepare($sSql);
	}

    /**
     * 实现用数组拼接SQL语句
     * 如，WHERE条件里元素为一个索引数组，则用一个括号把该数组解析后的字符串括起来
     */
	private function traversalArr($aVal,$isNew = false){
		uasort($aVal,array($this,'btosSort'));
		static $sSql = '';
		$isNew && $sSql = '';
		$sSql .= '(';
		$sepa = 'AND';
		if(isset($aVal['_logic'])){
            $sepa = strtoupper($aVal['_logic']);
            unset($aVal['_logic']);
        }
		foreach($aVal as $key=>$value){
			if(is_numeric($key) && is_array($value)){
				$this->traversalArr($value);
			}elseif(is_string($key)){
				$sSql .="{$this->reassoc($key,$value)} {$sepa} ";
			}else{
				Debug::trace("解析where参数错误=> {$key}:{$value} in ".print_r($aVal,true).__METHOD__.' line '.__LINE__,Debug::SYS_ERROR);
			}
		}
		$sSql = substr($sSql,0,strrpos($sSql,$sepa));
		$sSql .= ')';
		return $sSql;

	}






}