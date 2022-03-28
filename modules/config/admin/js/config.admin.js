/**
 * Редактирование параметров сайта, JS-сценарий
 * 
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

check_route_method();
$(document).on('change', "select[name=route_method]", check_route_method);

function check_route_method()
{
	if($("select[name=route_method]").val() == 1)
	{
		$('#route_translit_from,#route_translit_to').show();
	}
	else
	{
		$('#route_translit_from,#route_translit_to').hide();
	}
	if($("select[name=route_method]").val() == 2)
	{
		$('#route_translate_yandex_key').show();
	}
	else
	{
		$('#route_translate_yandex_key').hide();
	}
}