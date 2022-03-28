<?php
/**
 * Работа с платежной системой «ChronoPay», callback
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

if(!empty($_POST) && getenv('REMOTE_ADDR') == '207.97.254.211')
{
	$pay = $this->diafan->_payment->check_pay($_POST['order_id'], 'chronopay');

	if(array_key_exists('chronopay_shared_sec', $pay["params"]))
	{
		$sign = md5($pay["params"]['chronopay_shared_sec'].$_POST['customer_id'].$_POST['transaction_id'].$_POST['transaction_type'].$_POST['total']);

		if($sign == $_POST['sign'])
		{
			$this->diafan->_payment->success($pay);
		}
	}
	$this->diafan->_payment->fail($pay);
}
Custom::inc('includes/404.php');