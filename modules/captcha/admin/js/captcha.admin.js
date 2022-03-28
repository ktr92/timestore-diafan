/**
 * Редактирование вопросов для капчи, JS-сценарий
 * 
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

$(".param_actions").on('click', "a[action=delete_param]", function(){
	if ( $(this).attr("confirm") && ! confirm( $(this).attr("confirm")))
	{
		return false;
	}
	if($(".param").length == 1)
	{
		return false;
	}
	$(this).parents(".param").remove();
	if($(".param").length == 1)
	{
		$(".param").find('a[action=delete_param]').hide();
	}
	return false;
});

$('.param_plus').click(function() {
	var last = $('.param:last');
	last.after(last.clone(true));
	$('.param:last input').val('');
	$('.param a[action=delete_param]').show();
	if($('.param:last input[name=is_right]').is(':checked'))
	{
		$("input[name='answer_is_right[]']").val('');
		$("input[name='answer_is_right[]']:last").val('1');
	}
	return false;
});
$(document).on('click', 'input[name=is_right]', function(){
	$("input[name='answer_is_right[]']").val('');
	$(this).prev("input[name='answer_is_right[]']").val('1');
});
$('input[name=is_write]').click(function() {
	if($(this).is(':checked'))
	{
		$(".label_answer_is_right, input[name=is_right]").hide();
	}
	else
	{
		$(".label_answer_is_right, input[name=is_right]").show();
	}
});
if($('input[name=is_write]').is(':checked'))
{
	$(".label_answer_is_right, input[name=is_right]").hide();
}
else
{
	$(".label_answer_is_right, input[name=is_right]").show();
}