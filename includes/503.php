<?php
/**
 * Ошибка 503. Сервис недоступен
 * 
 * Модель страницы, выводимой на сайте для всех посетителей, когда включен параметр "Сайт на техобслуживании".
 * Оформление страницы в файле шаблона: /themes/tech.php
 * Администратор при этом может ходить по сайту как обычно.
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

global $diafan;

Custom::inc('includes/controller.php');
$diafan->_site->theme = '503.php';

header('HTTP/1.0 503 Service Unavailable');
header('Content-Type: text/html; charset=utf-8');

$mod = new Controller($diafan);
$diafan->_parser_theme->show_theme($mod);

exit;