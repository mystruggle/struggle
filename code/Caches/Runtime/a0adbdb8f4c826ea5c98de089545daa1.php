<?xml version="1.0" encoding="UTF-8" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>添加用户</title>
</head>
<body>

<form action='?m=show&a=shit&act=add&id=<?php if(isset($id))echo $id;?>' method="post" >
用户名<input type='text' name='username' /><br>
密码<input type='password' name='pwd' /><br>
<input type='submit' name='submit' value='提交' />
</form>






</body>
</html>