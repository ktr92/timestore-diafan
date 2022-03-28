<?php
/**
 * Работа с платежной системой «Liqpay»
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
 
if (!empty($_POST['operation_xml']) && !empty($_POST['signature']))
{
	$xml_decoded = base64_decode($_POST['operation_xml']);

	$xml = simplexml_load_string($xml_decoded);

	$pay = $this->diafan->_payment->check_pay($xml->order_id, 'liqpay');
	
	$sign = base64_encode(sha1($pay["params"]['liqpay_signature'].$xml_decoded.$pay["params"]['liqpay_signature'],1));
	
	if($_POST['signature'] != $sign)
	{
		Custom::inc('includes/404.php');
	}

	//если платеж прошел успешно
	if($xml->status == 'success')
	{
		$this->diafan->_payment->success($pay);
	}
	
	$this->diafan->_payment->fail($pay);
}

Custom::inc('includes/404.php');
