<?php
/**
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

define('DIAFAN', 1);
define('ABSOLUTE_PATH', dirname(__FILE__).'/');

include_once(ABSOLUTE_PATH."config.php");


if (!defined('VERSION_CMS') || VERSION_CMS != '5.3')
{
	exit('Обновление работает только для версии 5.3');
}

define('IS_ADMIN', 1);

include_once ABSOLUTE_PATH.'includes/custom.php';
include_once(ABSOLUTE_PATH.'includes/developer.php');
include_once(ABSOLUTE_PATH.'includes/diafan.php');
include_once(ABSOLUTE_PATH.'includes/file.php');

Dev::init();

try
{
if(! File::is_writable("config.php"))
{
	throw new Exception('Установите права 777 для config.php');
}
File::create_dir('return', true);
File::create_dir('custom');

if(! defined('CACHE_MEMCACHED'))
{
	define('CACHE_MEMCACHED', false);
}
define("VERSION", "6.0");


include_once(ABSOLUTE_PATH.'includes/core.php');
include_once ABSOLUTE_PATH.'includes/init.php';
$diafan = new Init();

global $langs;

$lang_base_site = false;
$lang_base_admin = false;

$languages = array();
$langs = array();
$result = DB::query("SELECT * FROM {languages}");
while ($row = DB::fetch_array($result))
{
	$languages[] = $row["id"];
	$langs[] = $row;
	if($row["base_site"])
	{
		$lang_base_site = $row["id"];
	}
	if($row["base_admin"])
	{
		$lang_base_admin = $row["id"];
	}
}
// проблема с таблицей {languages}
if(! $lang_base_site)
{
	DB::query("UPDATE {languages} SET base_site='1' WHERE id=%d", $languages[0]);
}
if(! $lang_base_admin)
{
	DB::query("UPDATE {languages} SET base_admin='1' WHERE id=%d", $languages[0]);
}

$modules = array();
$result = DB::query("SELECT name FROM {modules}");
while ($row = DB::fetch_array($result))
{
    $modules[] = $row["name"];
}

DB::query("RENAME TABLE {adminsite} TO {admin}");
DB::query("RENAME TABLE {adminsite_parents} TO {admin_parents}");

DB::query("UPDATE {admin} SET rewrite='admin' WHERE rewrite='adminsite'");
DB::query("UPDATE {modules} SET name='admin' WHERE name='adminsite'");

DB::query("ALTER TABLE {users} CHANGE `start_adminsite` `start_admin` VARCHAR(30) NOT NULL DEFAULT ''");


// сортировка ASC на DESC
$array = array(
	'core' => array('admin', 'images', 'site', 'users_param', 'users_param_select'),
	'forum' => array('forum_category'),
	'ab' => array('ab_param', 'ab_param_select'),
	'comments' => array('comments_param', 'comments_param_select'),
	'feedback' => array('feedback_param', 'feedback_param_select'),
	'shop' => array('shop_additional_cost', 'shop_delivery', 'shop_import', 'shop_order_param', 'shop_order_param_select', 'shop_order_status', 'shop_param', 'shop_param_select', 'shop_payment'),
	'tags' => array('tags_name'),
	'votes' => array('votes'),
);
foreach($array as $module => $tables)
{
	if(in_array($module, $modules) || $module == 'core')
	{
		foreach($tables as $table)
		{
			$sort = 0;
			$result = DB::query("SELECT id FROM {".$table."} ORDER BY sort DESC");
			while($row = DB::fetch_array($result))
			{
				$sort++;
				DB::query("UPDATE {".$table."} SET sort=%d WHERE id=%d", $sort, $row["id"]);
			}
		}
	}
}

if(in_array('shop', $modules))
{
	//модуль Оплата
	DB::query("INSERT INTO {modules} (name, module_name, site, site_page, admin, title) VALUES ('payment', 'payment', '1', '0', '1', 'Оплата')");

	DB::query("ALTER TABLE {shop_payment} RENAME {payment}");

	DB::query("ALTER TABLE {shop_pay_history} RENAME {payment_history}");
	DB::query("ALTER TABLE {payment_history} DROP COLUMN payment");
	DB::query("ALTER TABLE {payment_history} ADD payment_id INT(10) NOT NULL DEFAULT 0, ADD module_name ENUM('cart', 'balance') NOT NULL DEFAULT 'cart', CHANGE `order_id` `element_id` INT(11) UNSIGNED NOT NULL DEFAULT '0', ADD `code` VARCHAR(32) NOT NULL;");

	DB::query("ALTER TABLE {shop_order} DROP `payment_id`");
	DB::query("ALTER TABLE {shop_order} DROP `code`");

	DB::query("UPDATE {payment_history} SET `code`=RAND()");


	$adminsite_id = DB::query_result("SELECT id FROM {admin} WHERE rewrite='shop/payment' AND parent_id=0");
	DB::query("UPDATE {admin} SET rewrite='shop/delivery' WHERE id=%d", $adminsite_id);

	DB::query("DELETE FROM {admin} WHERE rewrite='shop/payment'");

	DB::query("DELETE FROM {admin} WHERE rewrite='shop/payhistory'");

	
	$adminsite_id = DB::query("INSERT INTO {admin} (parent_id, group_id, name, rewrite, act, count_children) VALUES (0, '4', 'Оплата', 'payment', '1', 2)");

	$last_adminsite_id = DB::query("INSERT INTO {admin} (parent_id, group_id, name, rewrite, act, sort) VALUES (%d, '4', 'Методы оплаты', 'payment', '1', 1)", $adminsite_id);
	DB::query("INSERT INTO {admin_parents} (parent_id, element_id) VALUES (%d, %d)", $adminsite_id, $last_adminsite_id);

	$last_adminsite_id = DB::query("INSERT INTO {admin} (parent_id, group_id, name, rewrite, act, sort) VALUES (%d, '4', 'История платежей', 'payment/history', '1', 2)", $adminsite_id);
	DB::query("INSERT INTO {admin_parents} (parent_id, element_id) VALUES (%d, %d)", $adminsite_id, $last_adminsite_id);

	DB::query("UPDATE {config} SET name='desc_order' WHERE name='desc_payment'");
	
	$rows = DB::query_fetch_all("SELECT * FROM {config} WHERE name='desc_payment'");
	foreach($rows as $row)
	{
		foreach($languages as $l)
		{
			DB::query("UPDATE {config} SET value".$l."='%s' WHERE id=%d", str_replace('%id', '%order', $row["value".$l]), $row["id"]);
		}
	}
}

if(in_array('forum', $modules))
{
	DB::query("RENAME TABLE {forum} TO {forum_messages}");
	DB::query("RENAME TABLE {forum_parents} TO {forum_messages_parents}");

	DB::query("CREATE TABLE {forum_blocks} (
		id SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
		name VARCHAR(250) NOT NULL DEFAULT '',
		sort INT(11) UNSIGNED NOT NULL DEFAULT '0',
		act ENUM('0', '1') NOT NULL DEFAULT '0',
		trash ENUM('0', '1') NOT NULL DEFAULT '0',
		PRIMARY KEY  (id)
	) CHARSET=utf8");

	DB::query("CREATE TABLE {forum} (
		id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		user_id INT(11) UNSIGNED NOT NULL DEFAULT '0',
		cat_id INT(11) UNSIGNED NOT NULL DEFAULT '0',
		created INT(10) UNSIGNED NOT NULL DEFAULT '0',
		name VARCHAR(250) NOT NULL DEFAULT '',
		date_update INT(10) UNSIGNED NOT NULL DEFAULT '0',
		user_update INT(11) UNSIGNED NOT NULL DEFAULT '0',
		timeedit INT(11) UNSIGNED NOT NULL DEFAULT '0',
		counter_view INT(11) UNSIGNED NOT NULL DEFAULT '0',
		act ENUM('0', '1') NOT NULL DEFAULT '0',
		prior ENUM('0', '1') NOT NULL DEFAULT '0',
		close ENUM('0', '1') NOT NULL DEFAULT '0',
		trash ENUM('0', '1') NOT NULL DEFAULT '0',
		PRIMARY KEY  (id),
		KEY `user_id` (`user_id`)
	) CHARSET=utf8");

	DB::query("DELETE FROM {forum_show} WHERE table_name='forum_category'");
	DB::query("ALTER TABLE {forum_show} DROP `table_name`");
	DB::query("ALTER TABLE {forum_show} ADD trash ENUM('0', '1') NOT NULL DEFAULT '0'");

	DB::query("ALTER TABLE {forum_messages} CHANGE `cat_id` `forum_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
	CHANGE `author` `user_id` INT(11) UNSIGNED NOT NULL DEFAULT '0'");

	DB::query("ALTER TABLE {forum_category} CHANGE `author` `user_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
	CHANGE `message_update` `timeedit` INT(10) UNSIGNED NOT NULL DEFAULT '0'");

	$ids = array();
	$rows = DB::query_fetch_all("SELECT * FROM {forum_category} WHERE parent_id=0");
	foreach ($rows as $row)
	{
		if(! $row["trash"])
		{
			DB::query("INSERT INTO {forum_blocks} (id, name, act, sort) VALUES (%d, '%s', '%d', %d)", $row["id"], $row["name"], $row["act"], $row["sort"]);
		}
		$ids[] = $row["id"];
	}
	DB::query("ALTER TABLE {forum_category} CHANGE `parent_id` `block_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0'");
	if($rows)
	{
		DB::query("DELETE FROM {forum_category} WHERE block_id=0");
		$rows = DB::query_fetch_all("SELECT * FROM {forum_category} WHERE block_id NOT IN (%s) AND trash='0'", implode(",", $ids));
		foreach ($rows as $row)
		{
			DB::query("INSERT INTO {forum} (id, user_id, created, name, date_update, user_update, timeedit, counter_view, act, prior, close, cat_id) VALUES (%d, %d, %d, '%s', %d, %d, %d, %d, '%d', '%d', '%d', %d)", $row["id"], $row["user_id"], $row["created"], $row["name"], $row["date_update"], $row["user_update"], $row["timeedit"], $row["counter_view"], $row["act"], $row["prior"], $row["close"], $row["block_id"]);
		}
		DB::query("DELETE FROM {forum_category} WHERE block_id NOT IN (%s)", implode(",", $ids));
	}

	DB::query("ALTER TABLE {forum_category} DROP `count_children`, DROP `close`, DROP `user_id`, DROP `date_update`, DROP `user_update`, DROP `created`, DROP `prior`");
	
	DB::query("DROP TABLE {forum_category_parents}");

	$adminsite_id = DB::query_result("SELECT id FROM {admin} WHERE rewrite='forum' AND parent_id=0");

	$last_adminsite_id = DB::query("INSERT INTO {admin} (parent_id, group_id, name, rewrite, act, sort) VALUES (%d, '2', 'Темы', 'forum', '1', 1)", $adminsite_id);
	DB::query("INSERT INTO {admin_parents} (parent_id, element_id) VALUES (%d, %d)", $adminsite_id, $last_adminsite_id);

	$last_adminsite_id = DB::query("INSERT INTO {admin} (parent_id, group_id, name, rewrite, act, sort) VALUES (%d, '2', 'Сообщения', 'forum/messages', '1', 2)", $adminsite_id);
	DB::query("INSERT INTO {admin_parents} (parent_id, element_id) VALUES (%d, %d)", $adminsite_id, $last_adminsite_id);

	$last_adminsite_id = DB::query("INSERT INTO {admin} (parent_id, group_id, name, rewrite, act, sort) VALUES (%d, '2', 'Блоки', 'forum/blocks', '1', 3)", $adminsite_id);
	DB::query("INSERT INTO {admin_parents} (parent_id, element_id) VALUES (%d, %d)", $adminsite_id, $last_adminsite_id);

	$last_adminsite_id = DB::query("INSERT INTO {admin} (parent_id, group_id, name, rewrite, act, sort) VALUES (%d, '2', 'Категории', 'forum/category', '1', 4)", $adminsite_id);
	DB::query("INSERT INTO {admin_parents} (parent_id, element_id) VALUES (%d, %d)", $adminsite_id, $last_adminsite_id);

	DB::query("UPDATE {admin} SET count_children=count_children+4 WHERE id=%d", $adminsite_id);
}

//канонический тег
$array = array(
	'core' => array('site'),
	'ab' => array('ab_category', 'ab'),
	'clauses' => array('clauses', 'clauses_category'),
	'faq' => array('faq', 'faq_category'),
	'files' => array('files', 'files_category'),
	'news' => array('news', 'news_category'),
	'photo' => array('photo', 'photo_category'),
	'shop' => array('shop', 'shop_category'),
);
foreach($array as $module => $tables)
{
	if(in_array($module, $modules) || $module == 'core')
	{
		foreach($tables as $table)
		{
			foreach($languages as $l)
			{
				DB::query("ALTER TABLE {".$table."} ADD `canonical".$l."` VARCHAR(100) NOT NULL DEFAULT ''");
			}
		}
	}
}

DB::query("DROP TABLE {update};");
DB::query("DELETE FROM {admin} WHERE rewrite='update/list'");

$adminsite_id = DB::query_result("SELECT id FROM {admin} WHERE rewrite='update' AND parent_id=0");

DB::query("DELETE FROM {admin_parents} WHERE parent_id=%d", $adminsite_id);
DB::query("UPDATE {admin} SET count_children=0 WHERE id=%d", $adminsite_id);

//update_return

DB::query("CREATE TABLE {update_return} (
	id SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
	created INT(10) UNSIGNED NOT NULL DEFAULT '0',
	name VARCHAR(100) NOT NULL DEFAULT '',
	current ENUM('0', '1') NOT NULL DEFAULT '0',
	hash VARCHAR(100) NOT NULL DEFAULT '',
	`text` text NOT NULL DEFAULT '',
	`version` VARCHAR(32) NOT NULL DEFAULT '',
	PRIMARY KEY  (id)
) CHARSET=utf8");

DB::query("UPDATE {modules} SET site='1' WHERE name='update'");

$diafan->_update->first_return();
$diafan->configmodules("hash", "update", 0, false, "5ab09819b61d5a692b86e6873b9fa6dbf922d207");

//custom
DB::query("INSERT INTO {admin} (group_id, name, rewrite, act, sort) VALUES ('3', 'Темы и дизайн', 'custom', '1', 4)");

DB::query("CREATE TABLE {custom} (
	id SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
	created INT(10) UNSIGNED NOT NULL DEFAULT '0',
	name VARCHAR(100) NOT NULL DEFAULT '',
	current ENUM('0', '1') NOT NULL DEFAULT '0',
	`text` text NOT NULL DEFAULT '',
	PRIMARY KEY  (id)
) CHARSET=utf8");

DB::query("INSERT INTO {modules} (name, module_name, admin, site) VALUES ('custom', 'core', '1', '1')");


//update => service
DB::query("UPDATE {admin} SET rewrite='service' WHERE rewrite='update/install'");
DB::query("UPDATE {admin} SET rewrite='service/db' WHERE rewrite='update/importexport'");
DB::query("UPDATE {admin} SET rewrite='service/repair' WHERE rewrite='update/repair'");
DB::query("INSERT INTO {modules} (name, module_name, admin, site) VALUES ('service', 'core', '1', '1')");

if(in_array('shop', $modules))
{
	DB::query("ALTER TABLE {shop_order_goods_param} CHANGE `order_good_id` `order_goods_id` INT(11) UNSIGNED NOT NULL DEFAULT '0'");
}

DB::query("RENAME TABLE {site_block_rel} TO {site_blocks_site_rel}");

if(in_array('search', $modules))
{
	DB::query("DELETE FROM {access} WHERE module_name='search'");
	DB::query("ALTER TABLE {search_results} DROP `name`, DROP `url`, DROP `snippet`;");
}


$adminsite_id = DB::query_result("SELECT id FROM {admin} WHERE rewrite='site' AND parent_id=0");
DB::query("UPDATE {admin} SET count_children=count_children+1 WHERE id=%d", $adminsite_id);
$last_adminsite_id = DB::query("INSERT INTO {admin} (parent_id, name, rewrite, act, sort) VALUES (%d, 'Динамические блоки', 'site/dynamic', '1', 3)", $adminsite_id);
DB::query("INSERT INTO {admin_parents} (parent_id, element_id) VALUES (%d, %d)", $adminsite_id, $last_adminsite_id);

DB::query("CREATE TABLE {site_dynamic} (
	id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	".multilang("nameLANG VARCHAR(100) NOT NULL DEFAULT '',")."
	`text` text NOT NULL DEFAULT '',
	".multilang("actLANG ENUM('0', '1') NOT NULL DEFAULT '0',")."
	type VARCHAR(20) NOT NULL DEFAULT '',
	access ENUM('0', '1') NOT NULL DEFAULT '0',
	title_no_show ENUM('0', '1') NOT NULL DEFAULT '0',
	date_start INT(10) UNSIGNED NOT NULL DEFAULT '0',
	date_finish INT(10) UNSIGNED NOT NULL DEFAULT '0',
	admin_id INT(11) UNSIGNED NOT NULL DEFAULT '0',
	sort INT(11) UNSIGNED NOT NULL DEFAULT '0',
	timeedit INT(10) UNSIGNED NOT NULL DEFAULT '0',
	trash ENUM('0', '1') NOT NULL DEFAULT '0',
	PRIMARY KEY  (id)
) CHARSET=utf8");

DB::query("CREATE TABLE {site_dynamic_element} (
	id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	dynamic_id INT(11) UNSIGNED NOT NULL DEFAULT '0',
	module_name VARCHAR(20) NOT NULL DEFAULT '',
	element_id INT(11) UNSIGNED NOT NULL DEFAULT '0',
	element_type VARCHAR(20) NOT NULL DEFAULT '',
	".multilang("valueLANG text NOT NULL DEFAULT '',")."
	parent ENUM('0', '1') NOT NULL DEFAULT '0',
	category ENUM('0', '1') NOT NULL DEFAULT '0',
	trash ENUM('0', '1') NOT NULL DEFAULT '0',
	PRIMARY KEY  (id),
	KEY dynamic_id (`dynamic_id`),
	KEY element_id (`element_id`),
	KEY element_type (`element_type`)
) CHARSET=utf8");

DB::query("CREATE TABLE {site_dynamic_module} (
	id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	dynamic_id INT(11) UNSIGNED NOT NULL DEFAULT '0',
	module_name VARCHAR(20) NOT NULL DEFAULT '',
	element_type VARCHAR(20) NOT NULL DEFAULT '',
	trash ENUM('0', '1') NOT NULL DEFAULT '0',
	PRIMARY KEY  (id),
	KEY dynamic_id (`dynamic_id`)
) CHARSET=utf8");

if(in_array('shop', $modules))
{
	DB::query("CREATE TABLE {shop_brand} (
		id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		".multilang("nameLANG TEXT DEFAULT '',")."
		".multilang("actLANG ENUM('0', '1') NOT NULL DEFAULT '0',")."
		map_no_show ENUM( '0', '1' ) NOT NULL DEFAULT '0',
		changefreq ENUM( 'always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never' ) NOT NULL,
		priority VARCHAR( 3 ) NOT NULL,
		site_id INT(11) UNSIGNED NOT NULL DEFAULT '0',
		".multilang("keywordsLANG VARCHAR(250) NOT NULL DEFAULT '',")."
		".multilang("descrLANG text NOT NULL DEFAULT '',")."
		".multilang("canonicalLANG VARCHAR(100) NOT NULL DEFAULT '',")."
		".multilang("title_metaLANG VARCHAR(250) NOT NULL DEFAULT '',")."
		".multilang("textLANG text NOT NULL DEFAULT '',")."
		import ENUM( '0', '1' ) NOT NULL DEFAULT '0',
		import_id VARCHAR(100) NOT NULL DEFAULT '',
		sort INT(11) UNSIGNED NOT NULL DEFAULT '0',
		timeedit INT(10) UNSIGNED NOT NULL DEFAULT '0',
		admin_id INT(11) UNSIGNED NOT NULL DEFAULT '0',
		theme VARCHAR(50) NOT NULL DEFAULT '',
		view VARCHAR(50) NOT NULL DEFAULT '',
		trash ENUM('0', '1') NOT NULL DEFAULT '0',
		PRIMARY KEY  (id),
		KEY site_id (`site_id`)
	) CHARSET=utf8");

	DB::query("CREATE TABLE {shop_brand_category_rel} (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'идентификатор',
	`element_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'идентификатор производителя из таблицы {shop_brand}',
	`cat_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'идентификатор категории из таблицы {shop_category}',
	`trash` ENUM('0', '1') NOT NULL DEFAULT '0' COMMENT 'запись удалена в корзину: 0 - нет, 1 - да',
	PRIMARY KEY (id),KEY cat_id (`cat_id`)
	) CHARSET=utf8 COMMENT 'Связи производителей и категорий';");

	DB::query("ALTER TABLE {shop} ADD `brand_id` INT(11) UNSIGNED NOT NULL DEFAULT '0';");
	DB::query("ALTER TABLE {shop} ADD INDEX (`brand_id`)");
	
	$adminsite_id = DB::query_result("SELECT id FROM {admin} WHERE rewrite='shop' AND parent_id=0");
	DB::query("UPDATE {admin} SET count_children=count_children+1 WHERE id=%d", $adminsite_id);
	$last_adminsite_id = DB::query("INSERT INTO {admin} (parent_id, group_id, name, rewrite, act, sort) VALUES (%d, '3', 'Производители', 'shop/brand', '1', 3)", $adminsite_id);
	DB::query("INSERT INTO {admin_parents} (parent_id, element_id) VALUES (%d, %d)", $adminsite_id, $last_adminsite_id);
}

DB::query("ALTER TABLE {images} ADD `element_type` ENUM('element', 'cat', 'brand') NOT NULL DEFAULT 'element';");
DB::query("ALTER TABLE {images} ADD INDEX (`element_type`)");
DB::query("UPDATE {images} SET element_type='element'");

$rows = DB::query_fetch_all("SELECT * FROM {images} WHERE module_name LIKE '%cat'");
foreach($rows as $row)
{
	$row["module_name"] = preg_replace('/cat$/', '', $row["module_name"]);
	DB::query("UPDATE {images} SET element_type='cat', module_name='%s' WHERE id=%d", $row["module_name"], $row["id"]);
}

DB::query("ALTER TABLE {rewrite} ADD `element_type` ENUM('element', 'cat', 'brand', 'param') NOT NULL DEFAULT 'element';");

$rows = DB::query_fetch_all("SELECT * FROM {rewrite}");
foreach($rows as $row)
{
	if($row["module_name"] == 'site')
	{
		DB::query("UPDATE {rewrite} SET element_type='element', element_id=%d WHERE id=%d", $row["site_id"], $row["id"]);
	}
	elseif($row["param_id"])
	{
		DB::query("UPDATE {rewrite} SET element_type='param', element_id=%d WHERE id=%d", $row["param_id"], $row["id"]);
	}
	elseif($row["element_id"])
	{
		DB::query("UPDATE {rewrite} SET element_type='element' WHERE id=%d", $row["id"]);
	}
	elseif($row["cat_id"])
	{
		DB::query("UPDATE {rewrite} SET element_type='cat', element_id=%d WHERE id=%d", $row["cat_id"], $row["id"]);
	}
}

DB::query("ALTER TABLE {rewrite} DROP `cat_id`, DROP `site_id`, DROP `param_id`;");

DB::query("ALTER TABLE {redirect} ADD `element_type` ENUM('element', 'cat', 'brand', 'param') NOT NULL DEFAULT 'element';");

$rows = DB::query_fetch_all("SELECT * FROM {redirect}");
foreach($rows as $row)
{
	if($row["module_name"] == 'site')
	{
		DB::query("UPDATE {redirect} SET element_type='element', element_id=%d WHERE id=%d", $row["site_id"], $row["id"]);
	}
	elseif($row["param_id"])
	{
		DB::query("UPDATE {redirect} SET element_type='param', element_id=%d WHERE id=%d", $row["param_id"], $row["id"]);
	}
	elseif($row["element_id"])
	{
		DB::query("UPDATE {redirect} SET element_type='element' WHERE id=%d", $row["id"]);
	}
	elseif($row["cat_id"])
	{
		DB::query("UPDATE {redirect} SET element_type='cat', element_id=%d WHERE id=%d", $row["cat_id"], $row["id"]);
	}
}

DB::query("ALTER TABLE {redirect} DROP `cat_id`, DROP `site_id`, DROP `param_id`;");

DB::query("ALTER TABLE {access} ADD `element_type` VARCHAR( 20 ) NOT NULL DEFAULT 'element';");

DB::query("UPDATE {access} SET element_type='blocks', module_name='site' WHERE module_name='site_blocks'");

$rows = DB::query_fetch_all("SELECT * FROM {access} WHERE element_type<>'blocks'");
foreach($rows as $row)
{
	if($row["element_id"])
	{
		DB::query("UPDATE {access} SET element_type='element' WHERE id=%d", $row["id"]);
	}
	elseif($row["cat_id"])
	{
		DB::query("UPDATE {access} SET element_type='cat', element_id=%d WHERE id=%d", $row["cat_id"], $row["id"]);
	}
}

DB::query("ALTER TABLE {access} DROP `cat_id`;");

DB::query("ALTER TABLE {menu} ADD `element_type` ENUM('element', 'cat', 'brand', 'param') NOT NULL DEFAULT 'element';");

$rows = DB::query_fetch_all("SELECT * FROM {menu}");
foreach($rows as $row)
{
	if($row["module_name"] == 'site')
	{
		DB::query("UPDATE {menu} SET element_type='element', element_id=%d WHERE id=%d", $row["site_id"], $row["id"]);
	}
	elseif($row["param_id"])
	{
		DB::query("UPDATE {menu} SET element_type='param', element_id=%d WHERE id=%d", $row["param_id"], $row["id"]);
	}
	elseif($row["element_id"])
	{
		DB::query("UPDATE {menu} SET element_type='element' WHERE id=%d", $row["id"]);
	}
	elseif($row["module_cat_id"])
	{
		DB::query("UPDATE {menu} SET element_type='cat', element_id=%d WHERE id=%d", $row["module_cat_id"], $row["id"]);
	}
}

DB::query("ALTER TABLE {menu} DROP `module_cat_id`, DROP `site_id`, DROP `param_id`;");

// формат ссылок
$array = array(
	'core' => array(
		'site' => array('text'),
		'site_blocks' => array('text'),
		'users_param' => array('text'),
		'users_param_element' => array('value')
	),
	'ab' => array(
		'ab' => array('text', 'anons'),
		'ab_category' => array('text', 'anons'),
		'ab_param' => array('text'),
		'ab_param_element' => array('value'),
	),
	'clauses' => array(
		'clauses' => array('text', 'anons'),
		'clauses_category' => array('text', 'anons'),
	),
	'comments' => array(
		'comments_param' => array('text'),
		'comments_param_element' => array('value'),
	),
	'faq' => array(
		'faq' => array('text'),
		'faq_category' => array('text', 'anons'),
	),
	'feedback' => array(
		'feedback_param' => array('text'),
		'feedback_param_element' => array('value'),
	),
	'files' => array(
		'files' => array('text', 'anons'),
		'files_category' => array('text', 'anons'),
	),
	'news' => array(
		'news' => array('text', 'anons'),
		'news_category' => array('text', 'anons'),
	),
	'photo' => array(
		'photo' => array('text', 'anons'),
		'photo_category' => array('text', 'anons'),
	),
	'shop' => array(
		'shop' => array('text', 'anons'),
		'shop_category' => array('text', 'anons'),
		'shop_additional_cost' => array('text'),
		'shop_delivery' => array('text'),
		'shop_order_param' => array('text'),
		'shop_order_param_element' => array('value'),
		'shop_param' => array('text'),
		'shop_param_element' => array('text'),
		'payment' => array('text')
	),
	'subscribtion' => array(
		'subscribtion_category' => array('text')
	)
);

foreach($array as $module => $tables)
{
	if(in_array($module, $modules) || $module == 'core')
	{
		foreach($tables as $table => $fields)
		{
			$result = DB::query("SELECT * FROM {".$table."}");
			while($row = DB::fetch_array($result))
			{
				$set = '';
				$values = array();
				foreach($fields as $field)
				{
					if(isset($row[$field]))
					{
						$res = replace_href($row[$field]);
						if($res)
						{
							$set .= ($set ? ',' : '').$field."='%s'";
							$values[] = $res;
						}
					}
					else
					{
						foreach($languages as $l)
						{
							if(isset($row[$field.$l]))
							{
								$res = replace_href($row[$field.$l]);
								if($res)
								{
									$set .= ($set ? ',' : '').$field.$l."='%s'";
									$values[] = $res;
								}
							}
						}
					}
				}
				if($set)
				{
					$values[] = $row["id"];
					DB::query("UPDATE {".$table."} SET ".$set." WHERE id=%d", $values);
				}
			}
		}
	}
}

DB::query("ALTER TABLE {log_note} ADD `element_type` ENUM('element', 'cat') NOT NULL DEFAULT 'element';");

if(in_array('rating', $modules))
{
	DB::query("ALTER TABLE {rating} ADD `element_type` ENUM('element', 'cat') NOT NULL DEFAULT 'element';");

	DB::query("UPDATE {rating} SET element_type='element'");
	$rows = DB::query_fetch_all("SELECT * FROM {rating} WHERE module_name LIKE '%_category'");
	foreach($rows as $row)
	{
		$row["module_name"] = str_replace('_category', '', $row["module_name"]);
		DB::query("UPDATE {rating} SET element_type='cat', module_name='%s' WHERE id=%d", $row["module_name"], $row["id"]);
	}
}
if(in_array('tags', $modules))
{
	DB::query("ALTER TABLE {tags} ADD `element_type` ENUM('element', 'cat') NOT NULL DEFAULT 'element';");

	DB::query("UPDATE {tags} SET element_type='element'");
}

if(in_array('comments', $modules))
{
	DB::query("ALTER TABLE {comments} ADD `element_type` ENUM('element', 'cat') NOT NULL DEFAULT 'element';");
	DB::query("ALTER TABLE {comments_mail} ADD `element_type` ENUM('element', 'cat') NOT NULL DEFAULT 'element';");

	DB::query("UPDATE {comments} SET element_type='element'");
	$rows = DB::query_fetch_all("SELECT * FROM {comments} WHERE module_name LIKE '%_category'");
	foreach($rows as $row)
	{
		$row["module_name"] = str_replace('_category', '', $row["module_name"]);
		DB::query("UPDATE {comments} SET element_type='cat', module_name='%s' WHERE id=%d", $row["module_name"], $row["id"]);
	}
	DB::query("UPDATE {comments_mail} SET element_type='element'");
	$rows = DB::query_fetch_all("SELECT * FROM {comments_mail} WHERE module_name LIKE '%_category'");
	foreach($rows as $row)
	{
		$row["module_name"] = str_replace('_category', '', $row["module_name"]);
		DB::query("UPDATE {comments_mail} SET element_type='cat', module_name='%s' WHERE id=%d", $row["module_name"], $row["id"]);
	}
}

if(in_array('map', $modules))
{
	DB::query("ALTER TABLE {map_index} ADD `element_type` ENUM('element', 'cat', 'param', 'brand') NOT NULL DEFAULT 'element', CHANGE table_name module_name VARCHAR(50) NOT NULL DEFAULT '';");

	$rows = DB::query_fetch_all("SELECT * FROM {map_index}");
	foreach($rows as $row)
	{
		$row["element_type"] = 'element';
		if($row["module_name"] == 'tags_name')
		{
			$row["module_name"] = 'tags';
		}
		elseif(strpos($row["module_name"], '_'))
		{
			list($row["module_name"], $row["element_type"], ) = explode('_', $row["module_name"]);
			if($row["element_type"] == 'category')
			{
				$row["element_type"] = 'cat';
			}
		}
		DB::query("UPDATE {map_index} SET element_type='%s', module_name='%s' WHERE id=%d", $row["element_type"], $row["module_name"], $row["id"]);
	}
}

DB::query("UPDATE {config} SET name='images_element' WHERE name='images'");
DB::query("UPDATE {config} SET name='images_variations_element' WHERE name='images_variations'");
DB::query("UPDATE {config} SET name='list_img_element' WHERE name='list_img'");

if(in_array('bs', $modules))
{
	foreach($languages as $l)
	{
		DB::query("ALTER TABLE {bs} ADD `name".$l."` VARCHAR(250) NOT NULL DEFAULT '', ADD `text".$l."` TEXT DEFAULT ''");
	}
	DB::query("UPDATE {bs} set name".$languages[0]."=name");
}
DB::query("ALTER TABLE {bs} DROP `name`");

if(in_array('votes', $modules))
{
	DB::query("RENAME TABLE {votes} TO {votes_answers}");
	DB::query("RENAME TABLE {votes_category} TO {votes}");
	DB::query("RENAME TABLE {votes_category_site_rel} TO {votes_site_rel}");

	DB::query("ALTER TABLE {votes_userversion} CHANGE `cat_id` `votes_id` INT(11) UNSIGNED NOT NULL DEFAULT '0';");
	DB::query("ALTER TABLE {votes_answers} CHANGE `cat_id` `votes_id` INT(11) UNSIGNED NOT NULL DEFAULT '0';");
	foreach($languages as $l)
	{
		DB::query("ALTER TABLE {votes_answers} CHANGE `name".$l."` `text".$l."` TEXT DEFAULT '';");
	}
}

DB::query("ALTER TABLE {users_actlink} ADD `count` INT(11) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'количество неудачных попыток'");

// описание скидки
if(in_array('shop', $modules))
{
	DB::query("ALTER TABLE {shop_discount} ADD `text` TEXT;");
}

if(in_array('bs', $modules))
{
	DB::query("ALTER TABLE {bs} ADD `sort` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'подрядковый номер для сортировки'");
	DB::query("UPDATE {bs} SET sort=id");

	DB::query("ALTER TABLE {bs_category} ADD `sort` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'подрядковый номер для сортировки'");
	DB::query("UPDATE {bs_category} SET sort=id");
}
if(in_array('menu', $modules))
{
	DB::query("ALTER TABLE {menu_category} ADD `sort` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'подрядковый номер для сортировки'");
	DB::query("UPDATE {menu_category} SET sort=id");
}
if(in_array('shop', $modules))
{
	DB::query("ALTER TABLE {shop_import_category} ADD `sort` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'подрядковый номер для сортировки'");
	DB::query("UPDATE {shop_import_category} SET sort=id");
}

DB::query("ALTER TABLE {users_role} ADD `sort` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'подрядковый номер для сортировки'");
DB::query("UPDATE {users_role} SET sort=id");

if(in_array('shop', $modules))
{
	DB::query("ALTER TABLE {shop_import_category} CHANGE `type` `type` ENUM('good', 'category', 'brand') NOT NULL DEFAULT 'good';");
	DB::query("ALTER TABLE {shop_cart} CHANGE `count` `count` DOUBLE UNSIGNED NOT NULL DEFAULT '0';");
	DB::query("ALTER TABLE {shop_wishlist} CHANGE `count` `count` DOUBLE UNSIGNED NOT NULL DEFAULT '0';");
	DB::query("ALTER TABLE {shop_order_goods} CHANGE `count_goods` `count_goods` DOUBLE UNSIGNED NOT NULL DEFAULT '0';");
}
if(in_array('feedback', $modules))
{
	DB::query("ALTER TABLE {feedback} ADD `url` TEXT;");
}

$arr = array();
$rows = DB::query_fetch_all("SELECT * FROM {access}");
foreach($rows as $row)
{
	if(! in_array($row["module_name"]."_".$row["element_type"], $arr))
	{
		DB::query("INSERT INTO {config} (module_name, name, value) VALUES ('%s', 'where_access_%s', '1');", $row["module_name"], $row["element_type"]);
		$arr[] = $row["module_name"]."_".$row["element_type"];
	}
}
if($rows)
{
	DB::query("INSERT INTO {config} (module_name, name, value) VALUES ('all', 'where_access', '1');");
}
DB::query("ALTER TABLE {images} ADD `image_id` INT(11) UNSIGNED NOT NULL DEFAULT '0';");

if(in_array('consultant', $modules))
{
	DB::query("UPDATE {admin} SET rewrite='consultant/livetex', name='LiveTex' WHERE rewrite='consultant/jivosite';");
	DB::query("UPDATE {admin} SET name='JivoSite' WHERE rewrite='consultant';");
}

if(in_array('shop', $modules))
{
	DB::query("ALTER TABLE {shop_discount_person} ADD `used` ENUM( '0', '1' ) NOT NULL DEFAULT '0';");
}

if(in_array('ab', $modules))
{
	DB::query("ALTER TABLE {ab} ADD `readed` ENUM( '0', '1' ) NOT NULL DEFAULT '0';");
	DB::query("UPDATE {ab} SET readed='1'");
}
if(in_array('feedback', $modules))
{
	DB::query("ALTER TABLE {feedback} ADD `readed` ENUM( '0', '1' ) NOT NULL DEFAULT '0';");
	DB::query("UPDATE {feedback} SET readed='1'");
}
DB::query("ALTER TABLE {captcha} ADD `is_write` ENUM( '0', '1' ) NOT NULL DEFAULT '0';");

if(in_array('shop', $modules))
{
	DB::query("ALTER TABLE {shop_param} ADD `id_page` ENUM( '0', '1' ) NOT NULL DEFAULT '0';");
	DB::query("UPDATE {shop_param} SET id_page='1'");
}

if(in_array('ab', $modules))
{
	DB::query("ALTER TABLE {ab_param} ADD `id_page` ENUM( '0', '1' ) NOT NULL DEFAULT '0';");
	DB::query("UPDATE {ab_param} SET id_page='1'");
}

if(in_array('faq', $modules))
{
	DB::query("INSERT INTO {config} (module_name, name, value) VALUES ('faq', 'count_letter_list', '160')");
	DB::query("INSERT INTO {config} (module_name, name, value) VALUES ('faq', 'page_show', '1')");
}
if(in_array('messages', $modules))
{
	DB::query("ALTER TABLE {messages} ADD `trash` ENUM( '0', '1') NOT NULL DEFAULT '0';");
	DB::query("ALTER TABLE {messages_user} ADD `trash` ENUM( '0', '1') NOT NULL DEFAULT '0';");
}

if(in_array('tags', $modules))
{
	DB::query("ALTER TABLE {tags} ADD date_start INT(10) UNSIGNED NOT NULL DEFAULT '0';");
	DB::query("ALTER TABLE {tags} ADD date_finish INT(10) UNSIGNED NOT NULL DEFAULT '0';");
}

if(in_array('search', $modules))
{
	DB::query("ALTER TABLE {search_results} ADD date_start INT(10) UNSIGNED NOT NULL DEFAULT '0';");
	DB::query("ALTER TABLE {search_results} ADD date_finish INT(10) UNSIGNED NOT NULL DEFAULT '0';");
}
if(in_array('map', $modules))
{
	DB::query("ALTER TABLE {map_index} ADD date_start INT(10) UNSIGNED NOT NULL DEFAULT '0';");
	DB::query("ALTER TABLE {map_index} ADD date_finish INT(10) UNSIGNED NOT NULL DEFAULT '0';");
}

DB::query("INSERT INTO {modules} (name, module_name, admin) VALUES ('dashboard', 'core', '1')");
DB::query("INSERT INTO {admin} (name, rewrite, group_id, sort) VALUES ('События', 'dashboard', '1', 1)");

DB::query("ALTER TABLE {admin} ADD `add` ENUM('0', '1') NOT NULL DEFAULT '0', ADD `add_name` VARCHAR(100) NOT NULL DEFAULT '';");
DB::query("UPDATE {admin} SET `add`='1', `add_name`='Страница сайта' WHERE rewrite='site'");
DB::query("UPDATE {admin} SET `add`='1', `add_name`='Товар' WHERE rewrite='shop'");
DB::query("UPDATE {admin} SET `add`='1', `add_name`='Новость' WHERE rewrite='news'");
DB::query("UPDATE {admin} SET `add`='1', `add_name`='Статья' WHERE rewrite='clauses'");
DB::query("UPDATE {admin} SET `add`='1', `add_name`='Фотография' WHERE rewrite='photo'");
DB::query("UPDATE {admin} SET `add`='1', `add_name`='Баннер' WHERE rewrite='bs'");
DB::query("UPDATE {admin} SET `add`='1', `add_name`='Объявление' WHERE rewrite='ab'");

if(in_array('shop', $modules))
{
	DB::query("ALTER TABLE {shop_order_status} ADD `color` VARCHAR(20) NOT NULL DEFAULT '', ADD `count_minus`  ENUM('0', '1') NOT NULL DEFAULT '0', ADD `send_mail`  ENUM('0', '1') NOT NULL DEFAULT '0';");
	DB::query("UPDATE {shop_order_status} SET color='#D6473F' WHERE status='0'");
	DB::query("UPDATE {shop_order_status} SET color='#3C62C7', count_minus='1' WHERE status='1'");
	DB::query("UPDATE {shop_order_status} SET color='gray' WHERE status='2'");
	DB::query("UPDATE {shop_order_status} SET color='#8AC73C', count_minus='1' WHERE status='3'");
	DB::query("UPDATE {shop_order_status} SET send_mail='1'");

	DB::query("ALTER TABLE {shop_order} ADD `count_minus`  ENUM('0', '1') NOT NULL DEFAULT '0';");
	DB::query("UPDATE {shop_order} SET count_minus='1' WHERE status='1' OR status='3'");
	
	DB::query("CREATE TABLE {shop_additional_cost_rel} (
		id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		element_id INT(11) UNSIGNED NOT NULL DEFAULT '0',
		additional_cost_id SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
		summ DOUBLE NOT NULL DEFAULT '0',
		trash ENUM('0', '1') NOT NULL DEFAULT '0',
		PRIMARY KEY  (id),
		KEY element_id (`element_id`)
	) CHARSET=utf8");

	DB::query("ALTER TABLE {shop_additional_cost} ADD `shop_rel`  ENUM('0', '1') NOT NULL DEFAULT '0';");
	DB::query("ALTER TABLE {shop_order_additional_cost} ADD `order_goods_id` INT(11) UNSIGNED NOT NULL DEFAULT '0';");

	DB::query("ALTER TABLE {shop_cart} ADD `additional_cost` TEXT;");
	DB::query("ALTER TABLE {shop_wishlist} ADD `additional_cost` TEXT;");

	DB::query("CREATE TABLE {shop_additional_cost_category_rel} (
		id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		element_id INT(11) UNSIGNED NOT NULL DEFAULT '0',
		cat_id INT(11) UNSIGNED NOT NULL DEFAULT '0',
		trash ENUM('0', '1') NOT NULL DEFAULT '0',
		PRIMARY KEY  (id),
		KEY cat_id (`cat_id`)
	) CHARSET=utf8");

	DB::query("ALTER TABLE {shop_cart} CHANGE `count` `count` DOUBLE NOT NULL DEFAULT '0';");
}

if(in_array('bs', $modules))
{
	foreach($languages as $l)
	{
		DB::query("ALTER TABLE {bs} ADD `link".$l."` TEXT;");
		DB::query("UPDATE {bs} SET `link".$l."`=`link`;");
	}
	DB::query("ALTER TABLE {bs} DROP `link`;");
}

if(in_array('shop', $modules))
{
	DB::query("ALTER TABLE {shop_param} ADD yandex_use ENUM('0', '1') NOT NULL DEFAULT '0', ADD yandex_name VARCHAR(100) NOT NULL DEFAULT '', ADD yandex_unit VARCHAR(50) NOT NULL DEFAULT '';");
}

if(in_array('clauses', $modules))
{
	foreach($languages as $l)
	{
		DB::query("ALTER TABLE {clauses} CHANGE `text".$l["id"]."` `text".$l["id"]."` LONGTEXT;");
	}
}

if(in_array('shop', $modules))
{
	foreach($languages as $l)
	{
		DB::query("ALTER TABLE {shop} ADD `measure_unit".$l."` VARCHAR(50) NOT NULL DEFAULT ''");
	}
}

if(in_array('tags', $modules))
{
	DB::query("ALTER TABLE {tags} ADD `noindex` ENUM('0','1') NOT NULL DEFAULT '0';");
}
if(in_array('news', $modules))
{
	DB::query("ALTER TABLE {news} ADD `noindex` ENUM('0','1') NOT NULL DEFAULT '0';");
	DB::query("ALTER TABLE {news_category} ADD `noindex` ENUM('0','1') NOT NULL DEFAULT '0';");
}
if(in_array('ab', $modules))
{
	DB::query("ALTER TABLE {ab} ADD `noindex` ENUM('0','1') NOT NULL DEFAULT '0';");
	DB::query("ALTER TABLE {ab_category} ADD `noindex` ENUM('0','1') NOT NULL DEFAULT '0';");
}
if(in_array('shop', $modules))
{
	DB::query("ALTER TABLE {shop} ADD `noindex` ENUM('0','1') NOT NULL DEFAULT '0';");
	DB::query("ALTER TABLE {shop_category} ADD `noindex` ENUM('0','1') NOT NULL DEFAULT '0';");
	DB::query("ALTER TABLE {shop_brand} ADD `noindex` ENUM('0','1') NOT NULL DEFAULT '0';");
}
if(in_array('files', $modules))
{
	DB::query("ALTER TABLE {files} ADD `noindex` ENUM('0','1') NOT NULL DEFAULT '0';");
	DB::query("ALTER TABLE {files_category} ADD `noindex` ENUM('0','1') NOT NULL DEFAULT '0';");
}
if(in_array('faq', $modules))
{
	DB::query("ALTER TABLE {faq} ADD `noindex` ENUM('0','1') NOT NULL DEFAULT '0';");
	DB::query("ALTER TABLE {faq_category} ADD `noindex` ENUM('0','1') NOT NULL DEFAULT '0';");
}
if(in_array('clauses', $modules))
{
	DB::query("ALTER TABLE {clauses} ADD `noindex` ENUM('0','1') NOT NULL DEFAULT '0';");
	DB::query("ALTER TABLE {clauses_category} ADD `noindex` ENUM('0','1') NOT NULL DEFAULT '0';");
}
if(in_array('photo', $modules))
{
	DB::query("ALTER TABLE {photo} ADD `noindex` ENUM('0','1') NOT NULL DEFAULT '0';");
	DB::query("ALTER TABLE {photo_category} ADD `noindex` ENUM('0','1') NOT NULL DEFAULT '0';");
}

DB::query("ALTER TABLE {users} CHANGE `useradmin` `useradmin` ENUM('0', '1', '2') NOT NULL DEFAULT '0';");

if(in_array('payment', $modules))
{
	DB::query("ALTER TABLE {payment_history} ADD `payment_data` VARCHAR(50) NOT NULL DEFAULT '0';");
}
if(in_array('shop', $modules))
{
	DB::query("ALTER TABLE {shop_price} CHANGE `count_goods` `count_goods` DOUBLE NOT NULL DEFAULT '0';");
	DB::query("ALTER TABLE {shop_import_category} ADD `header` ENUM( '0', '1' ) NOT NULL DEFAULT '0';");
}
foreach($languages as $l)
{
	DB::query("ALTER TABLE {menu} ADD `text".$l["id"]."` VARCHAR(255) NOT NULL DEFAULT '';");
}
DB::query("ALTER TABLE {users} ADD `config` TEXT;");

if(in_array('shop', $modules))
{
	DB::query("ALTER TABLE {shop_delivery} ADD `service` VARCHAR(50) NOT NULL DEFAULT '', ADD `params` TEXT;");
	DB::query("ALTER TABLE {shop} ADD `weight` VARCHAR(50) NOT NULL DEFAULT '', ADD `length` VARCHAR(50) NOT NULL DEFAULT '', ADD `width` VARCHAR(50) NOT NULL DEFAULT '', ADD `height` VARCHAR(50) NOT NULL DEFAULT '';");
	DB::query("ALTER TABLE {shop_order} ADD `delivery_info` TEXT;");
	DB::query("INSERT INTO {modules} (name, module_name, site, admin, title) VALUES ('delivery', 'shop', '1', '1', 'Служба доставки')");
	DB::query("UPDATE {admin} SET rewrite='delivery' WHERE rewrite='shop/delivery'");
}

if(in_array('tags', $modules))
{
	foreach($languages as $l)
	{
		DB::query("ALTER TABLE {tags_name} ADD `text".$l["id"]."` TEXT, ADD `keywords".$l["id"]."` VARCHAR(250) NOT NULL DEFAULT '', ADD `descr".$l["id"]."` TEXT, ADD `title_meta".$l["id"]."` VARCHAR(250) NOT NULL DEFAULT '';");
	}
	DB::query("ALTER TABLE {tags_name} ADD `timeedit` INT(10) UNSIGNED NOT NULL DEFAULT '0';");
}

if(in_array('shop', $modules))
{
	$adminsite_parent_id = DB::query_result("SELECT id FROM {admin} WHERE rewrite='shop/ordercount' AND parent_id=0");
	
	$adminsite_id = DB::query("INSERT INTO {admin} (parent_id, group_id, name, rewrite, act) VALUES (%d, '4', 'Брошенные корзины', 'shop/cart', '1')", $adminsite_parent_id);
	
	DB::query("INSERT INTO {admin_parents} (parent_id, element_id) VALUES (%d, %d)", $adminsite_parent_id, $adminsite_id);
	DB::query("UPDATE {admin} SET count_children=count_children+1 WHERE id=%d", $adminsite_parent_id);
}

if(in_array('shop', $modules))
{
	DB::query("ALTER TABLE {shop} ADD `google` TEXT, ADD `show_google` ENUM( '0', '1' ) NOT NULL DEFAULT '0';");
	DB::query("ALTER TABLE {shop_category} ADD `show_google` ENUM( '0', '1' ) NOT NULL DEFAULT '0';");
}

if(in_array('subscribtion', $modules))
{
	DB::query("ALTER TABLE {subscribtion} RENAME {subscription}");
	DB::query("ALTER TABLE {subscribtion_category} RENAME {subscription_category}");
	DB::query("ALTER TABLE {subscribtion_category_rel} RENAME {subscription_category_rel}");
	DB::query("ALTER TABLE {subscribtion_emails} RENAME {subscription_emails}");
	DB::query("ALTER TABLE {subscribtion_emails_cat_unrel} RENAME {subscription_emails_cat_unrel}");
	DB::query("ALTER TABLE {subscribtion_phones} RENAME {subscription_phones}");
	DB::query("ALTER TABLE {subscribtion_sms} RENAME {subscription_sms}");
	DB::query("ALTER TABLE {subscribtion_category_parents} RENAME {subscription_category_parents}");
	DB::query("UPDATE {modules} SET module_name='subscription', name='subscription' WHERE name='subscribtion'");
	DB::query("UPDATE {admin} SET rewrite='subscription' WHERE rewrite='subscribtion'");
	DB::query("UPDATE {admin} SET rewrite='subscription/sms' WHERE rewrite='subscribtion/sms'");
	DB::query("UPDATE {admin} SET rewrite='subscription/phones' WHERE rewrite='subscribtion/phones'");
	DB::query("UPDATE {admin} SET rewrite='subscription/emails' WHERE rewrite='subscribtion/emails'");
	DB::query("UPDATE {admin} SET rewrite='subscription/category' WHERE rewrite='subscribtion/category'");
	DB::query("UPDATE {admin} SET rewrite='subscription/config' WHERE rewrite='subscribtion/config'");
	DB::query("UPDATE {site} SET module_name='subscription' WHERE module_name='subscribtion'");
	DB::query("UPDATE {config} SET module_name='subscription' WHERE module_name='subscribtion'");
	DB::query("UPDATE {users_role_perm} SET rewrite='subscription' WHERE rewrite='subscribtion'");
	DB::query("UPDATE {users_role_perm} SET rewrite='subscription/sms' WHERE rewrite='subscribtion/sms'");
	DB::query("UPDATE {users_role_perm} SET rewrite='subscription/phones' WHERE rewrite='subscribtion/phones'");
	DB::query("UPDATE {users_role_perm} SET rewrite='subscription/emails' WHERE rewrite='subscribtion/emails'");
	DB::query("UPDATE {users_role_perm} SET rewrite='subscription/category' WHERE rewrite='subscribtion/category'");
	DB::query("UPDATE {users_role_perm} SET rewrite='subscription/config' WHERE rewrite='subscribtion/config'");
}

if(in_array('shop', $modules))
{
	DB::query("ALTER TABLE {shop_param} ADD `site_id` INT(11) UNSIGNED NOT NULL DEFAULT '0';");
}

if(in_array('ab', $modules))
{
	DB::query("ALTER TABLE {ab_param} ADD `site_id` INT(11) UNSIGNED NOT NULL DEFAULT '0';");
}

$diafan->_cache->delete("");

//////////////////////config
$new_values = array(
	'VERSION_CMS' => VERSION
);

include_once(ABSOLUTE_PATH.'includes/config.php');
Config::save($new_values, $langs);

echo '<font color="green">Обновление успешно завершено!</font>';
}
catch (Exception $e)
{
	Dev::exception($e);
}

function multilang($str)
{
	global $langs;
	if(! $langs)
		return;

	$result = '';

	$arg = func_get_args();
	$i = 0;

	foreach($langs as $lang)
	{
		if (! isset($arg[$i]))
		{
			$arg[$i] = $arg[$i - 1];
		}
		$str = $arg[$i];
		$result .= str_replace('LANG', $lang["id"], $str).' ';
		$i++;
	}
	return $result;
}

function replace_href($text)
{
	if(! $text)
		return false;

	if(preg_match_all('/href="map:([^"]+)"/', $text, $matches))
	{
		foreach ($matches[0] as $i => $m)
		{
			$params = array(
					"lang_id" => 0,
					"module_name" => '',
					"site_id" => 0,
					"cat_id" => 0,
					"element_id" => 0,
					"param_id" => 0,
				);
			$params_ = explode(';', $matches[1][$i]);
			foreach ($params_ as $p)
			{
				if($p)
				{
					list($name, $value) = explode('=', $p);
					$params[$name] = $value;
				}
			}
			$element_id = 0;
			$element_type = 'element';
			if(! empty($params["element_id"]))
			{
				$element_id = $params["element_id"];
			}
			elseif(! empty($params["cat_id"]))
			{
				$element_id = $params["cat_id"];
				$element_type = 'cat';
			}
			elseif(! empty($params["param_id"]))
			{
				$element_id = $params["param_id"];
				$element_type = 'param';
			}
			elseif(! empty($params["site_id"]))
			{
				$element_id = $params["site_id"];
				$params["module_name"] = 'site';
			}
			$replace = 'href="map:'
			.'lang_id='.$params["lang_id"].';'
			.($params["module_name"] ? 'module_name='.$params["module_name"].';' : '')
			.'element_id='.$element_id.';'
			.'element_type='.$element_type.';'
			.'"';

			$text = str_replace($m, $replace, $text);
		}
		return $text;
	}
	return false;
}