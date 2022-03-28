<?php
/**
 * Настройки платежной системы «Liqpay» для административного интерфейса
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

class Payment_liqpay_admin
{
	public $config;

	public function __construct()
	{
		$this->config = array(
			"name" => 'Liqpay',
			"params" => array(
				'liqpay_merchant_id' => array(
					'name' => 'merchant_id',
					'help' => 'ID мерчанта - выдается системой',
				),
				'liqpay_signature' => array(
					'name' => 'signature',
					'help' => 'Сигнатура - выдается системой',
				),
				'liqpay_method' => array(
					'name' => 'Метод оплаты',
					'help' => 'С карты (card), с телефона (liqpay), наличными (delayed).',
				),
				'liqpay_currency' => array(
					'name' => 'Валюта',
					'help' => 'RUR и т.д.',
				),
				'liqpay_phone' => array(
					'name' => 'Телефон',
					'help' => 'телефон, на который приходит SMS при попытке оплатить товар картой',
				),
			)
		);
	}
}