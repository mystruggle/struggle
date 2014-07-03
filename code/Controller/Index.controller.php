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
$c->find(array('field'=>'name,pwd','where'=>array('`name`'=>'sys'),'orderby'=>'name desc','limit'=>"0,2"));


//$c->find(array('field'=>'id,name,name   AS n,pwd','join'=>'belong_to_role','where'=>array('id'=>2),'groupby'=>'id','having'=>'','orderby'=>'id','limit'=>""));
        $this->display();
    }
}