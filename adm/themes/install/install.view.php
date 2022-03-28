<?php
/**
 * Шаблон установки DIAFAN.CMS
 * 
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

if (! defined('DIAFAN'))
{
	$path = __FILE__;
	while(! file_exists($path.'/includes/404.php'))
	{
		$parent = dirname($path);
		if($parent == $path) exit;
		$path = $parent;
	}
	include $path.'/includes/404.php';
}

?>
<!DOCTYPE HTML>
<html>
<head>
	<meta charset="UTF-8">
	<title>DIAFAN.CMS - установка. <?php echo $this->view->name;?> - from diafan.ru</title>
	<meta name="HandheldFriendly" content="True">
	<meta name="viewport" content="width=device-width, initial-scale=-0.2, minimum-scale=-0.2, maximum-scale=3.0">
	<meta name="format-detection" content="telephone=no">
	
	<link rel="stylesheet" href="<?php echo BASE_PATH;?>css/jquery-ui.css" media="all">
	<link rel="stylesheet" href="<?php echo BASE_PATH;?>css/jquery.formstyler.css" media="all">
	<link rel="stylesheet" href="<?php echo BASE_PATH;?>adm/css/main.css" media="all">
		
	<!--[if lt IE 9]><script src="//yandex-st.ru/jquery/1.10.2/jquery.min.js"></script><![endif]-->
	<!--[if gte IE 9]><!-->
	<script type="text/javascript" src="//yandex-st.ru/jquery/2.0.3/jquery.min.js" charset="UTF-8"><</script><!--<![endif]-->
	<script src="<?php echo BASE_PATH;?>js/jquery.formstyler.js"></script>
	<script type="text/javascript" src="//yandex.st/jquery-ui/1.10.3/jquery-ui.min.js" charset="UTF-8"></script>
	<script src="<?php echo BASE_PATH;?>adm/js/main.js"></script>
	
	<!--[if lte IE 8]>
		<link rel="stylesheet" href="<?php echo BASE_PATH;?>adm/css/ie/ie.css" media="all" />
		<script src="<?php echo BASE_PATH;?>adm/js/ie/html5shiv.js"></script>
	<![endif]-->
	
	<!--[if !IE]><!-->
		<script>if(/*@cc_on!@*/false){document.documentElement.className+=' ie10';}</script>
	<!--<![endif]-->
</head>
<body>
	<div id="wrapper">
		<!-- |===============| header start |===============| -->
		<header class="header">
			<a href="<?php echo BASE_PATH;?>" class="logo">
				<img src="<?php echo BASE_PATH;?>adm/img/logo.png" alt="">
				<span class="logo__title">Система управления</span>
				<span class="logo__link"><?php echo getenv('SERVER_NAME'); ?></span>
			</a>

			
		</header>
		<!-- |===============| header end |===============| -->
		
		<!-- |===============| wrap start |===============| -->
		<div class="wrap">
			<!-- |===============| col-right start |===============| -->
			<div class="col-right col-right_ins">
				<?php $this->view('menu');?>
				
				<div class="heading">
					<div class="heading__unit">
						Установка
					</div>
				</div>
				
				<div class="content">
					<h3><?php echo $this->view->name;?></h3>
					<?php $this->view($this->view->rewrite);?>
				</div>
				
				<footer class="footer">
					<div class="footer__links">
						<a href="https://user.diafan.ru/support/">Техническая поддержка</a>
						|
						<a href="http://www.diafan.ru/dokument/">Документация модуля</a>
					</div>
					
					<div class="footer__copy">
						© 2003-<?php echo date("Y");?> <a href="http://www.diafan.ru/">www.diafan.ru</a><br>
						DIAFAN.CMS версия <?php echo $this->view->version;?>
					</div>
				</footer>
			</div>
			<!-- |===============| col-right end |===============| -->
			
		</div>
		<!-- |===============| wrap end |===============| -->
		
	</div>
</body>
</html>