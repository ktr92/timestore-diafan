<?php
/**
 * Формирует данные для формы платежной системы «Liqpay»
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

class Payment_liqpay_model extends Diafan
{
	/**
     * Формирует данные для формы платежной системы «Liqpay»
     * 
     * @param array $params настройки платежной системы
     * @param array $pay данные о платеже
     * @return array
     */
	public function get($params, $pay)
	{
		$result["summ"]      	= $pay["summ"];
		$result["order_id"]  	= $pay["id"];
		$result["text"]      	= $pay["text"];
		$result["desc"]      	= $pay["desc"];
		$result["merchant_id"]  = $params["liqpay_merchant_id"];
		$result["signature"]    = $params["liqpay_signature"];
		$result["method"]    	= $params["liqpay_method"];
		$result["currency"]   	= $params["liqpay_currency"];
		$result["phone"]   	= $params["liqpay_phone"];

		return $result;
	}
}