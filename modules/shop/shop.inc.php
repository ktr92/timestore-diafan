<?php
/**
 * Подключение модуля «Магазин»
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

/**
 * Shop_inc
 */
class Shop_inc extends Diafan
{
	/**
	 * @var object бэкэнд
	 */
	private $backend;

	/**
	 * Подключает расширения для подключения
	 * 
	 * @return mixed
	 */
	public function __call($name, $args)
	{
		list($prefix, $method) = explode('_', $name, 2);
		switch($prefix)
		{
			case 'price':
				if(! isset($this->backend['price']))
				{
					Custom::inc('modules/shop/inc/shop.inc.price.php');
					$this->backend['price'] = new Shop_inc_price($this->diafan);
				}
				$shop = &$this->backend['price'];
				break;

			case 'order':
				if(! isset($this->backend['order']))
				{
					Custom::inc('modules/shop/inc/shop.inc.order.php');
					$this->backend['order'] = new Shop_inc_order($this->diafan);
				}
				$shop = &$this->backend['order'];
				break;

			default:
				return false;
		}
		return call_user_func_array(array(&$shop, $method), $args);
	}
}