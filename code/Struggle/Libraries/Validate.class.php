<?php
namespace Struggle\Libraries;
class Validate extends Object{
	public $error = '';
	public $code = '';

    //验证错误信息
	private    $errorField = '';
	protected $ruleReg = array(
		    'required' => true,
            'number'   => true,
		);



	public function __construct(){
	}





	public static function self($new = false){
		static $oSelf = null;
		if(is_null($oSelf) || $new)
			$oSelf = new self;
		return $oSelf;
	}






    /**
     * 内置验证方法
     * 模型中验证格式为array(数据库字段名称,校验方法名称,错误提示,校验方法类型)
     * 数据字段名称、校验方法名称为必须的；
	 * 校验方法类型，如inner说明校验方法为内置型，match说明校验方法为正则表达式，默认inner,
	 * 错误提示为校验不通过时的提示
     */
	public function validate($data, $rules){
		$aRule = $rules;
		$aData = $data;
		if(isset($aRule) && $aRule){
			if(!is_array($aRule)){
				$this->error = '参数有误,校验规则须为数组类型';
				return false;
			}
			foreach($aRule as $rule){
                if(!isset($rule[0]) || empty($rule[0]) || !isset($rule[1]) || empty($rule[1])) {
                    $this->error = '参数非法';
                    return false;
                }
                if(!isset($aData[$rule[0]])) {
                    //不需要验证的字段
                    continue;
                }

				$sField = $rule[0];
				$sRule = $rule[1];
				//验证方法类型,默认内置类型
				$sRuleType = 'inner';
				if(isset($rule[3]) && !empty($rule[3])){
					$sRuleType = $rule[3];
				}

				//错误提示
				$sValidError = '';
				if(isset($rule[2]) && !empty($rule[2])){
					$sValidError = $rule[2];
				}

                //内置验证规则
                if($sRuleType == 'inner'){
                    if(!isset($this->ruleReg[$sRule])){
                        $this->error = '该验证方法不支持';
                        return false;
                    }
                    switch(strtolower($sRule)){
                        case 'required':
							if(empty($aData[$sField])){
                                $this->error = '不能为空';
								$this->mValidField = $sField;
								return false;
						    }
                            return true;
                            break;
                        case 'number'://合法的有效数字
                            if(!@preg_match('/^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:\.\d+)?$/',$aData[$sField])){
                                $this->error = $sValidError;
								$this->errorField = $sField;
                                return false;
                            }
                            return true;
                            break;
                        case 'digits'://整数
                            return !empty($aData[$sField]);
                            break;
                    }
                }else{
                    //用户自定义规则
                    return $this->_UserRule($sField,$sRule,$sValidError,$sRuleType);
                }
			}
		}
        return false;
	}



    /**
     * 处理用户自定义规则
     */
    private function _UserRule($value,$rule,$type,$message = ''){
        if(strtolower($type) == 'match'){
            return @preg_match($rule,$value);
        }
        $this->mValidError = '不支持该类型匹配';
        return false;
    }



    public function getValidError(){
        return $this->mValidError;
    }




    public function getValidMessage(){
        return $this->mValidMessage;
    }



















}