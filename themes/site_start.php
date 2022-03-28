<?php
/**
 * Шаблон стартовой страницы сайта
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
		<section class="indexslider">
			<div class="mainslider-container">
				<div class="mainslider">
					<insert name="show_block" module="shop" template="mainslider" count="5" images="1" param="10=1">
				
				</div>
				<div class="mainarrows">
					<div class="container">
						<div class="ab-left"><i class="fa fa-angle-left" aria-hidden="true"></i></div>
						<div class="ab-right"><i class="fa fa-angle-right" aria-hidden="true"></i></div>
					</div>
				</div>
			</div>
		</section>		
		
		<section class="preim">
			<div class="container">
				<h2>Почему наш магазин?</h2>
				<div class="row">
					<div class="col-md-4 col-sm-4 col-xs-12">
						<div class="preim-item">
							<img src="/images/preim-1.png" alt="">
							<div class="preim-title">Лучшие цены</div>
							<div class="preim-descr">и гарантийное обслуживание</div>
						</div>
					</div>
					<div class="col-md-4 col-sm-4 col-xs-12">
						<div class="preim-item">
							<img src="/images/preim-2.png" alt="">
							<div class="preim-title">Бесплатная доставка</div>
							<div class="preim-descr">по России при покупке от 5 000 <span class="rouble">a</span></div>
						</div>
					</div>
					<div class="col-md-4 col-sm-4 col-xs-12">
						<div class="preim-item">
							<img src="/images/preim-3.png" alt="">
							<div class="preim-title">Прием звонков</div>
							<div class="preim-descr">каждый день без выходных</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		
	
		
		<section class="hits">
			<div class="container">
				<h2>Хиты продаж</h2>
					<div class="hitsindex-container">
					<div class="row">
						<div class="hitsindex">
							<insert name="show_block" module="shop" template="hitsindex" count="10" images="1" hits_only="1">
						
						</div>
					</div>
					<div class="mainarrows">
						<div class="container">
							<div class="ab-left-hits"><i class="fa fa-angle-left" aria-hidden="true"></i></div>
							<div class="ab-right-hits"><i class="fa fa-angle-right" aria-hidden="true"></i></div>
						</div>
					</div>
				</div>
				</div>
			</section>
		
		<section class="textindex">
			<div class="container">
				<article>
					<h1>Интернет Магазин часов и бижутерии Time Store</h1>
					<div>
						<p>Элегантные, дорогие часы давно уже перестали быть только средством измерения времени. Для кого-то - это элитное ювелирное украшение, для кого-то — символ статуса, для одного — важный деловой аксессуар, для другого — способ дополнить свой образ утонченным штрихом. Что вас интересует? Стильный современный дизайн или вечная классика, сталь или золото, эпатажная роскошь от знаменитого модного дома или сдержанный престиж швейцарского бренда? Любые наручные часы, соответствующие понятию luxury, вы найдете в магазине Bestwatch.ru.</p>
				
						<p>Здесь представлены модели с брендовыми именами. Это часы швейцарские, японские, французские — все значимые и известные на планете марки, в том числе классика часового искусства — оригинальные наручные швейцарские часы.</p>
				
						<p>Листая интернет-каталог нашего магазина, вы найдете кварцевые и электронные модели различных фирм. Но вершиной часового искусства остаются механические часы. Обратите внимание на классические модели, выполненные из дорогих металлов. Это символ роскошной, яркой жизни, истинное мастерство и непревзойденный дизайн. Это знаменитые швейцарские часы, которыми можно восхищаться и перед которыми благоговеть.</p>
				
					</div>
				</article>
			</div>
		</section>
		
		
	</main>
	<insert name="show__footer">

<!-- шаблонный тег show_js подключает JS-файлы. Описан в файле themes/functions/show_js.php. -->
<insert name="show_js">

<script type="text/javascript" asyncsrc="<insert name="custom" path="js/main.js" absolute="true" compress="js">" charset="UTF-8"></script>



<insert name="show_include" file="counters">

</body>
</html>
