<?php
namespace struggle\controller;

class IndexController extends Controller{
    public function actionIndex(){
        //$this->assgin('a','');
        //$this->assgin('b','我是B');
        //$this->assgin('c','我是c');
		//$a=\struggle\M('User');
		//print_r($a);
		//$b=\struggle\M();
		//print_r($b);
		$c=\struggle\M('User');//print_r($c);
//$c->setAttr(\PDO::ATTR_ERRMODE,\PDO::ERRMODE_EXCEPTION);
//		var_dump($c->getAttr(\PDO::ATTR_ERRMODE));
//select feild join where groupby   having orderby limit
//echo $c->alias,'end';
//(a=1 and b=2) or (c>=3 and d<4)
//$c->bindValue(array(':a'=>'sys',':1'=>10,':2'=>20,':p'=>'123456'));
/*
name='sys'
array('name'=>'sys')

name>'sys'
array('`name`.gt'=>'sys')

(name='sys' and pwd>=1) or (desc<=2 and create_time in(2,3) )
array(array('name'=>'sys','`pwd`.ge'=>1),'_logic'=>'or',array('`desc`.le'=>2,'`create_time`.in'=>array(2,3)))
----------- end array --------------
$this->bindValue(array(':name'=>'sys',':pwd'=>1,':a'=>'2{',':b'=>2,':c'=>3));
(name='sys' and pwd>=1) or (desc<='2{' and create_time in(2,3) )
(`name`=:name and `pwd` >=:pwd) or (`desc`<=:a and `create_time` in(:b,:c))

$sys='sys';
$pwd=1;
$desc='2&#123;';
$create_time=array(2,3);
(name='sys' and pwd>=1) or (desc<='2{' and create_time in(2,3) )
(`name`={{$sys}} and pwd>={{$pwd}}) or(`desc`<={{$desc}} and create_time in ({{$create_time}}))


$this->bindParam(array('sys',1,'2{',2,3));
(name='sys' and pwd>=1) or (desc<='2{' and create_time in(2,3) )
(name=? and pwd >=?) or (desc<=? and create_time in(?,?))

*/
//$c->bindParam(array('123455','sys'));
//$c->where('`name`=?')->find();
//$c->join('Role')->where(array('`pwd`'=>'123455','User.`name`'=>'sys'))->find();
$this->assgin('test', array('fuck','shit'));
$c->bindValue(array('sys','111',1,2,'111'));
$c->where("(`name`=? and `pwd`>=?) or ( `id` in (?,?) and `pwd`=?)")->find();


//$c->find(array('field'=>'id,name,name   AS n,pwd','join'=>'belong_to_role','where'=>array('id'=>2),'groupby'=>'id','having'=>'','orderby'=>'id','limit'=>""));
        $this->display();
    }
}