/**
 * Редактирование платежных систем, JS-сценарий
 * 
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

$("select[name=payment]").change(function(){
	$(".tr_payment").hide();
	$(".tr_payment[payment="+$(this).val()+"]").show();
});
$(".tr_payment[payment="+$("select[name=payment]").val()+"]").show();