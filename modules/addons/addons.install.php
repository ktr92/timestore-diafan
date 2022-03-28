<?php
/**
 * Установка модуля
 * 
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

if ( ! defined('DIAFAN'))
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

class Addons_install extends Install {

	/**
	 * @var string название
	 */
	public $title = "Дополнения";

	/**
	 * @var array таблицы в базе данных
	 */
	public $tables = array(
		array(
			"name" => "addons",
			"comment" => "Дополнения для CMS",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "addon_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор дополнения",
				),
				array(
					"name" => "custom_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор из таблицы {custom}",
				),
				array(
					"name" => "name",
					"type" => "VARCHAR(100) NOT NULL DEFAULT ''",
					"comment" => "название",
				),
				array(
					"name" => "anons",
					"type" => "TEXT",
					"comment" => "анонс",
				),
				array(
					"name" => "text",
					"type" => "TEXT",
					"comment" => "описание",
				),
				array(
					"name" => "install",
					"type" => "TEXT",
					"comment" => "описание установки дополнения",
				),
				array(
					"name" => "link",
					"type" => "VARCHAR( 255 ) NOT NULL DEFAULT ''",
					"comment" => "внешняя ссылка",
				),
				array(
					"name" => "image",
					"type" => "VARCHAR( 255 ) NOT NULL DEFAULT ''",
					"comment" => "внешняя ссылка на изображение",
				),
				array(
					"name" => 'author',
					"type" => "TEXT",
					"comment" => "данные об авторе",
				),
				array(
					"name" => 'author_link',
					"type" => "TEXT",
					"comment" => "ссылка на страницу автора",
				),
				array(
					"name" => "downloaded",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "количество скачиваний",
				),
				array(
					"name" => "timeedit",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "время последнего изменения в формате UNIXTIME",
				),
				array(
					"name" => "custom_timeedit",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "время последнего изменения в формате UNIXTIME в таблице {custom}",
				),
				array(
					"name" => "import_update",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "метка обновления записи: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY addon_id (`addon_id`)",
				"KEY custom_id (`custom_id`)",
			),
		),
	);

	/**
	 * @var array записи в таблице {modules}
	 */
	public $modules = array(
		array(
			"name" => "addons",
			"admin" => true,
			"site" => false,
			"site_page" => false,
		),
	);

	/**
	 * @var array меню административной части
	 */
	public $admin = array(
		array(
			"name" => "Дополнения для CMS",
			"rewrite" => "addons",
			"group_id" => 3,
			"sort" => 39,
			"act" => true,
			"children" => array(
				array(
					"name" => "Каталог дополнений",
					"rewrite" => "addons",
					"sort" => "1",
					"act" => true,
				),
			)
		),
	);

	/**
	 * Выполняет действия при установке модуля после основной установки
	 *
	 * @return void
	 */
	public function action_post()
	{
		if(! IS_DEMO)
		{
			$this->diafan->_addons->update(true);
		}
	}
}