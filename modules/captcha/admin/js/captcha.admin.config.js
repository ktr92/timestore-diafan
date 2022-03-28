/**
 * Настройка модуля, JS-сценарий
 * 
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

if($('select[name=type]').val() != 'reCAPTCHA')
{
	$('#recaptcha_public_key, #recaptcha_private_key').hide();
}
$('select[name=type]').change(function(){
	if($(this).val() == 'reCAPTCHA')
	{
		$('#recaptcha_public_key, #recaptcha_private_key').show();
	}
	else
	{
		$('#recaptcha_public_key, #recaptcha_private_key').hide();
	}
});