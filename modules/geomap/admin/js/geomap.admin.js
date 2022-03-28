/**
 * Редактирование бэкенда для геокарты, JS-сценарий
 * 
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

$("select[name=backend]").change(function(){
	$(".tr_geomap").hide();
	$(".tr_geomap[backend="+$(this).val()+"]").show();
});
$(".tr_geomap[backend="+$("select[name=backend]").val()+"]").show();