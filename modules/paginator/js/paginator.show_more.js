/**
 * JS-сценарий модуля
 * 
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

$(document).on('click', "input[type=submit].js_paginator_more_button", function() {
	var th = $(this).parents('form');
	if (! th.length) return false;
	var module = th.children("input[name=module]").val(),
		action = th.children("input[name=action]").val();
	if (module || action)
	{
		diafan_ajax.before[module+'_'+action] = function(form){
			$(form).attr("loading", "true");
			return true;
		}
		diafan_ajax.success[module+'_'+action] = function(form, response){
			$(form).removeAttr("loading");
			if (response.set_location)
			{
				diafan_ajax.set_location($(form).attr("action"));
				/*var top = $(window).scrollTop(),
					destination = $(form).offset().top,
					header = $("header.diafan-admin-panel, header.useradmin_panel").eq(0),
					diff = 0;
				if (header.length)
				{
					diff = header.outerHeight(true);
					destination = destination - diff;
				}
				if (top < destination) $('html, body').animate({scrollTop: destination}, 850);*/
			}
			return true;
		}
	}
	return true;
});