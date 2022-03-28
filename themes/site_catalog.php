<?php
/**
 * Основной шаблон сайта
 * 
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */
 
if(! defined("DIAFAN"))
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
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>

<!-- шаблонный тег show_head выводит часть HTML-шапки сайта. Описан в файле themes/functions/show_head.php. -->
<insert name="show_head">
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="shortcut icon" href="<insert name="path">favicon.ico" type="image/x-icon">
<!-- шаблонный тег show_css подключает CSS-файлы. Описан в файле themes/functions/show_css.php. -->
<insert name="show_css" files="default.css, style.css">

</head>

<body>
	<insert name="show__header">
	
	<main>
		
		<div class="container">
			<div class="catalog">		
			<div class="row">
				<div class="col-md-3 col-sm-3 col-xs-12">
				<aside>
						<!-- шаблонный тег вывода навигации "Хлебные крошки"-->
						<insert name="show_breadcrumb" current="true">  
						<insert name="show_search" module="shop" cat_id="current" ajax="true">
					</aside>
				</div>
				<div class="col-md-9 col-sm-9 col-xs-12">
						<!-- шаблонный тег вывода основного контента сайта -->
						<insert name="show_body">
				</div>
			</div>
			</div>
		</div>		
	</main>
	
	<insert name="show__footer">


<!-- шаблонный тег show_js подключает JS-файлы. Описан в файле themes/functions/show_js.php. -->
<insert name="show_js">

<script type="text/javascript" asyncsrc="<insert name="custom" path="js/main.js" absolute="true" compress="js">" charset="UTF-8"></script>
<insert name="show_include" file="counters">

</body>
</html>