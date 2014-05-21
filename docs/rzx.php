<?php
//+---------------------------------------------------------------------------------------+
//+   任子行身份证判断
//+---------------------------------------------------------------------------------------+
     //功能：用户判断所输入身份证是否合法,身份证年限设置为1900-2100年
	//对18和15位的身份证都进行日期的校验,对18位的身份证加最后一位验证码的校验
	//参数：$InputNum  需要判断的内容
	//返回值：如果身份证号码非法则返回true，否则返回false
	private function ChkNum($InputNum)
	{
		$InputNum=rtrim($InputNum);
		if($InputNum=="" || empty($InputNum))//是否为空
		{
			return true;
		}
		$length=strlen($InputNum);
		if($length=="15" || $length=="18")//判断长度是否等于15位或18位
		{
				$lengthl=$length-1;
				if($length=="18")//对18位证件号码判断日期和最后一位验证码是否正确
				{
					//对最后一位进行判断,是否为数字或为"x","X","*"还要符合身份证的验证规则
					$tmpcharlast=substr($InputNum,$lengthl,1);
					$check=array(0=>7,1=>9,2=>10,3=>5,4=>8,5=>4,6=>2,7=>1,8=>6,9=>3,10=>7,11=>9,12=>10,13=>5,14=>8,15=>4,16=>2);
					$checkback=array(0=>"1",1=>"0",2=>array(1=>"X",2=>"x",3=>"*"),3=>"9",4=>"8",5=>"7",6=>"6",7=>"5",8=>"4",9=>"3",10=>"2");
					$sum=0;
					for($j=0;$j<$lengthl;$j++)
					{

						$tmpchar=substr($InputNum,$j,1);
						if($tmpchar >="0" && $tmpchar <="9")
						{
							//return flase;
						}
						else
						{
							return true;
						}

						//$tmpsumchar=substr($InputNum,$j,1);
						$sum1=$tmpchar*$check[$j];
						$sum+=$sum1;
					}
					$mod=$sum%11;
					if($mod==2)//为"x"或"X"或"*"时
					{
						$last1=$checkback[2][1];
						$last2=$checkback[2][2];
						$last3=$checkback[2][3];
					}
					else
					{
						$last=$checkback[$mod];
					}
					if($tmpcharlast==$last || $tmpcharlast==$last1 || $tmpcharlast==$last2 || $tmpcharlast==$last3)
					{
						//return false;
					}
					else
					{
						return true;
					}
					//校验日期
					$tmpyear=substr($InputNum,6,4);
					$tmpmonth=substr($InputNum,10,2);
					$tmpday=substr($InputNum,12,2);
					if(!@checkdate($tmpmonth,$tmpday,$tmpyear))
					{
						return true;
					}
				}

				if($length=="15")//对15位证件号码判断日期是否合法,是否为数字,15位的没有验证码
				{


					for($i=0;$i<$length;$i++)//对15位是否是数字进行判断
					{
						$tmpchar=substr($InputNum,$i,1);
						if($tmpchar >="0" && $tmpchar <="9")
						{
							//return false;
						}
						else
						{
							return true;
						}
					}
					//验证日期
					$tmpyear=substr($InputNum,6,2);
					if($tmpyear=="11")
						return true;
					$tmpyear="19".$tmpyear;
					$tmpmonth=substr($InputNum,8,2);
					$tmpday=substr($InputNum,10,2);
					$year=date("Y");
					if(!@checkdate($tmpmonth,$tmpday,$tmpyear) || ($year-$tmpyear)<=10 ||$year-$tmpyear>=90)
					{
						return true;
					}
				}
		}
		else
		{
			return true;
		}
		return false;
	}