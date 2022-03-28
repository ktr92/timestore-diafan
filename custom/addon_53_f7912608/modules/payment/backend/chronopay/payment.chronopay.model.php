<?php
/**
 * Формирует данные для формы платежной системы «ChronoPay»
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

class Payment_chronopay_model extends Diafan
{
	/**
     * Формирует данные для формы платежной системы "ChronoPay"
     *
     * @param array $params настройки платежной системы
     * @param array $pay данные о платеже
     * @return array
     */
	public function get($params, $pay)
	{
		$result["summ"]       = $pay["summ"];
		$result["order_id"]   = $pay["id"];
		$result["text"]       = $pay["text"];
		$result['product_id'] = $params['chronopay_product_id'];
	    // если контроль времени жизни order_id (договариваеться с тех поддержкой)		
		$result['shared_sec'] = md5(implode('-', array($params['chronopay_product_id'], $pay['summ'], $pay['id'], $params['chronopay_shared_sec'])));
		$result['link'] = BASE_PATH.'payment/get/chronopay/';
		return $result;
	}
}