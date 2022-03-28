/*DIAFAN.CMS*/
var cat_search = '';

$('.cat_id_edit').click(function () {
	$(this).next('div').toggle();
});
$('input[name=cat_search]').keyup(function(){
	var self = $(this);
	if(cat_search == self.val())
	{
		return;
	}
	cat_search = self.val();
	if(! cat_search)
	{
		$('.cat_search_select').remove();
		return;
	}
	diafan_ajax.init({
		data:{
			action: 'cat_list',
			search: cat_search
		},
		success: function(response){
			
			self.next('.cat_search_select').remove();
			self.after(prepare(response.data));
		}
	});
});
$(document).on('click', '.cat_search_select li', function(){
	var addition = $(this).parents('.additional_cat_ids');
	if(addition.length)
	{
		$('input[name=cat_search]', addition).val();
		addition.append('<br><input type="checkbox" name="cat_ids[]" value="'+$(this).attr('cat_id')+'" id="input_user_additional_cat_id_'+$(this).attr('cat_id')+'" checked> <label for="input_user_additional_cat_id_'+$(this).attr('cat_id')+'">'+$(this).text()+'</label>');
		$('.cat_search_select').remove();
	}
	else
	{
		$('input[name=cat_search]').first().val($(this).text());
		$('input[name=cat_id]').val($(this).attr('cat_id'));
		$('.cat_search_select').remove();
	}
});
	
	
$('#input_user_additional_cat_id').change(function() {
	$('.cat_ids').stop().slideToggle('fast');
});

if(!$('#input_user_additional_cat_id').is(':checked')) $('.cat_ids').hide();

$('input[name=multi_site]').change(function(){
	if ($(this).is(':checked'))
	{
		$('select[name="cat_ids[]"] option').show();
	}
	else
	{
		var site_id = $('select[name=site_id]').val();
		$('select[name="cat_ids[]"] option').each(function(){
			if ($(this).attr('rel') && $(this).attr('rel') !== "0" && $(this).attr('rel') !== site_id) {
				$(this).hide();
				if ($(this).is(':selected')) {
					$(this).prop('selected', false);
				}
			}
			else
			{
				$(this).show();
			}
		});
	}
});