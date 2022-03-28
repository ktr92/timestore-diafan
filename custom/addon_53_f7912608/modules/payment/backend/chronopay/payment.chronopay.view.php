<?php
/**
 * Шаблон платежа через систему «ChronoPay»
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
?>

<form action="https://payments.chronopay.com/" method="POST">
<input type="hidden" name="product_id" value="<?php echo $result["product_id"]; ?>" />
<input type="hidden" name="product_price" value="<?php echo $result["summ"]; ?>" />
<input type="hidden" name="order_id" value="<?php echo $result["order_id"]; ?>" />
<input type="hidden" name="cb_url" value="<?php echo $result['link'];?>" />
<input type="hidden" name="cb_type" value="P" />
<input type="hidden" name="success_url" value="<?php echo $result['link'].'success/';?>" />
<input type="hidden" name="decline_url" value="<?php echo $result['link'].'fail/';?>" />
<input type="hidden" name="sign" value="<?php echo $result['shared_sec']?>" />
<input type="submit" value="<?php echo $this->diafan->_('Оплатить', false);?>" />
</form>