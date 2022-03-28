$(".threshold_actions").hide();
$(document).on('mouseover', ".threshold", function(){
	$(this).addClass("hover");
	$(this).find(".threshold_actions").show();
});
$(document).on('mouseout', ".threshold", function(){
	$(this).removeClass("hover");
	$(this).find(".threshold_actions").hide();
});

$(".threshold_actions").on('click', "a[action=delete_threshold]", function(){
	if ( $(this).attr("confirm") && ! confirm( $(this).attr("confirm")))
	{
		return false;
	}
	if($(".threshold").length == 1)
	{
		$(this).parents(".threshold").find('input').val('');
		$(this).parents(".threshold").hide();
	}
	else
	{
		$(this).parents(".threshold").remove();
	}
	return false;
});
$('.threshold_plus').click(function() {
	if ($("select[name=service]").val().length == 0)
	{
		$('.threshold:last').clone(true).appendTo('#thresholds table');
		$('.threshold:last input').val('');
		$('.threshold:last .threshold_actions a[action=delete_threshold]').attr('confirm', '');
		$('.threshold:last').show();
		return false;
	}
});

$("select[name=service]").change(function(){
	$(".tr_service").hide();
	$(".tr_service[service="+$(this).val()+"]").show();
	$("#name input[name=name]").val($( "select[name=service] option:selected" ).text());
	$(".threshold input[name='price[]'], .threshold input[name='amount[]']").val("0");
	if ($(this).val().length > 0)
	{
		$('#thresholds table tr:not(:first)').remove();
	}
});
$(".tr_service[service="+$("select[name=service]").val()+"]").show();