<?xml version="1.0" encoding="UTF-8" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Insert title here</title>
</head>
<body>
{include html/header2}
<?php $this->_widget_('index/index');?>


<?php if(($a==2 and $b==$d) or $k>=1):?>
<?php echo $a;?>
<?php elseif($a==$c):?>
<?php echo trim($b);?>
<?php else:?>
<?php echo $c;?>
<?php endif;?>



</body>
</html>