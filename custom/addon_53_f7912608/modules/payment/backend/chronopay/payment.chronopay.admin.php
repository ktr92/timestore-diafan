<?php
/**
 * Настройки платежной системы «ChronoPay» для административного интерфейса
 * 
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    5.4
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2014 OOO «Диафан» (http://diafan.ru)
 */

if (! defined('DIAFAN'))
{
	include dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/includes/404.php';
}

class Payment_chronopay_admin
{
	public $config = array(
		"name" => 'ChronoPay',
		"params" => array(
			'chronopay_shared_sec' => 'SharedSec',
			'chronopay_product_id' => 'Product_ID'
		)
	);
}