<?php
/**
 * Шаблонный тег: формирует часть HTML-шапки сайта. Включает в себя в том числе теги: show_title, show_description, show_keywords.
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
<header id="header">
		<div class="topline">
			<div class="container">
				<div class="row">
						<div class="col-md-10 col-sm-9 col-xs-6">
							<div class="topmenu">								
								<?=$this->diafan->_parser_theme->get_function_in_theme('<insert name="show_block" module="menu" id="3" template="topline">'); ?>					
							</div>
						</div>
						<div class="col-md-2 col-sm-3 col-xs-6">
											
							<?=$this->diafan->_parser_theme->get_function_in_theme('<insert name="show_login" module="registration" defer="emergence" defer_title="Профиль" template="header">');?>						
						</div>
				</div>
			</div>
		</div>
		<div class="mainheader">
			<div class="container">
				<div class="row">
					<div class="col-md-3 col-sm-3 col-xs-12"><div class="logo">                        
						<?=$this->diafan->_parser_theme->get_function_in_theme('<insert name="show_href" img="/images/logo.png" alt="Timestore">');?></div></div>
					<div class="col-md-5 col-md-offset-1 col-sm-5 col-xs-12">
						<div class="phones">
							<a href="tel:88004308091" class="phone"><span class="gray">+7 (800) </span>430 80 91</a>
							<a href="tel:84993324232" class="phone"><span class="gray">+7 (499) </span>332 42 32</a>
							<a href="#" data-toggle="modal" data-target="#myModal_zakazat_zvonok" class="callback">Заказать звонок</a>
						</div>
						<div class="header-search">
							<?=$this->diafan->_parser_theme->get_function_in_theme('<insert name="show_search" module="search" >  ');?>

						</div>
					</div>
					<div class="col-md-3 col-sm-4 col-xs-12">
						<?=$this->diafan->_parser_theme->get_function_in_theme('<insert name="show_block" module="cart">');?>
					</div>
				</div>
			</div>
		</div>
		<nav class="mainmenu">	
			<div class="container">
				<?=$this->diafan->_parser_theme->get_function_in_theme('<insert name="show_block" module="menu" id="1" template="topmenu">'); ?>
			</div>
		</nav>

	</header>