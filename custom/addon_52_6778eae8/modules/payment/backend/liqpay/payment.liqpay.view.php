<?php
/**
 * Шаблон платежа через систему «Liqpay»
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

echo $result["text"];
$signature = $result["signature"];
$phone = $result["phone"];
$xml = "<request>      
	<version>1.2</version>
	<result_url>".BASE_PATH."</result_url>
	<server_url>".BASE_PATH."payment/get/liqpay/</server_url>
	<merchant_id>".$result["merchant_id"]."</merchant_id>
	<order_id>".$result["order_id"]."</order_id>
	<amount>".$result["summ"]."</amount>
	<currency>".$result["currency"]."</currency>
	<description>Oplata zakaza № ".$result["order_id"]."</description>
	<default_phone>$phone</default_phone>
	<pay_way>".$result["method"]."</pay_way> 
	</request>
	";

$xml_encoded = base64_encode($xml); 
$lqsignature = base64_encode(sha1($signature.$xml.$signature,1));

echo "<form action='https://www.liqpay.com/?do=clickNbuy' method='POST'>
<input type='hidden' name='operation_xml' value='$xml_encoded' />
<input type='hidden' name='signature' value='$lqsignature' />
<input type='submit' value=".$this->diafan->_('Оплатить', false).">
</form>";