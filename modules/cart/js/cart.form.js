/**
 * JS-сценарий модуля «Корзина товаров, оформление заказа»
 * 
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

$(".js_cart_count input").keyup(function(e) {
  e.target.value = e.target.value.replace(/,/g,'.');
});
 
$('.js_cart_table_form :submit, .cart_table_form :submit').hide();
$(document).on('change', '.js_cart_table_form :text, .js_cart_table_form input[type=number], .cart_table_form :text, .cart_table_form input[type=number]', cart_submit);
$(document).on('click', '.js_cart_table_form :checkbox, .js_cart_table_form :radio, .cart_table_form :checkbox, .cart_table_form :radio',  function(e){
        e.preventDefault();
	if($(this).parents('.js_cart_remove').length)
	{
		$(this).parents('.js_cart_remove').click();
		return;
	}
	cart_submit();
});
$(document).on('change', '.js_cart_table_form :checkbox, .js_cart_table_form :radio, .cart_table_form :checkbox, .cart_table_form :radio',   function(e){
        e.preventDefault();
	if($(this).parents('.js_cart_remove').length)
	{
		$(this).parents('.js_cart_remove').click();
		return;
	}
	cart_submit();
});

$(document).on('click', '.js_cart_table_form .js_cart_remove', function(e){
        e.preventDefault();
	if ($(this).attr('confirm') && ! confirm($(this).attr('confirm')))
	{
		return false;
	}
	$(this).find('input[type=checkbox]').prop('checked',true);
	$(this).find('input[type=hidden]').val(1);
	cart_submit();
});
$(document).on('click', '.js_cart_table_form .js_cart_count_minus, .cart_table_form .cart_count_minus', function(e){
        e.preventDefault();
	var count = $(this).parents('.js_cart_count, .cart_count').find('input');
	count.val().replace(/,/g, ".");	
	if(count.val() > 1)
	{
		count.val(count.val() * 1 - 1);
	}
	cart_submit();
});

$(document).on('click', '.js_cart_table_form .js_cart_count_plus, .cart_table_form .cart_count_plus', function(e){
        e.preventDefault();
	var count = $(this).parents('.js_cart_count, .cart_count').find('input');
	count.val().replace(/,/g, ".");	
	count.val(count.val() * 1 + 1);
	cart_submit();
});

function cart_submit()
{
	$('.js_cart_table_form, .cart_table_form').submit();
}