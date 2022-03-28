/*DIAFAN.CMS*/
$('input[type=number], input.number').keydown(function (evt) {
	evt = (evt) ? evt : ((window.event) ? event : null);

	if (evt) {
		var elem = (evt.target)
		? evt.target
		: (
		evt.srcElement
			? evt.srcElement
			: null
		);

		if (elem) {
		var charCode = evt.charCode
			? evt.charCode
			: (evt.which
			? evt.which
			: evt.keyCode
			);

		if ((charCode < 32 ) ||
			(charCode > 36 && charCode < 41) ||
			(charCode > 44 && charCode < 47) ||
			(charCode > 95 && charCode < 106) ||
			(charCode > 47 && charCode < 58) || charCode == 188 || charCode == 191 || charCode == 190 || charCode == 110 || charCode == 86 || charCode == 67) {
			return true;
		}
		else {
			return false;
		}
		}
	}
});

$('input[type=tel]').mask('+9999 999 9999');

var diafan_ajax = {
	data: false,
	url: false,
	success: false,
	init: function(config){
		if(config.data)
		{
			this.data = config.data;
		}
		else
		{
			this.data = {};
		}
		if(config.url)
		{
			this.url = config.url;
		}
		else
		{
			this.url = window.location.href;
		}
		if(config.success)
		{
			this.success = config.success;
		}
		else
		{
			this.success = (function(response){});
		}
		diafan_ajax.data.check_hash_user = $('.check_hash_user').text();
		return $.ajax({
			url: diafan_ajax.url,
			type: 'POST',
			data: diafan_ajax.data,
			success:(function (result) {
				try {
					var response = $.parseJSON(result);
				} catch(err){
					$('body').append(result);
					$('.diafan_div_error').css('left', $(window).width()/2 - $('.diafan_div_error').width()/2);
					$('.diafan_div_error').css('top', $(window).height()/2 - $('.diafan_div_error').height()/2 + $(document).scrollTop());
					$('.diafan_div_error_overlay').css('height', $('body').height());
					return false;
				}
				if (response.hash) {
					$('input[name=check_hash_user]').val(response.hash);
					$('.check_hash_user').text(response.hash);
				}
				if(response.redirect) {
					window.location.href = prepare(response.redirect);
				}
				diafan_ajax.success(response);
			})
		});
	}
}

$(".timecalendar").each(function () {
	if ($(this).attr('showtime') == "true") {
		$(this).datetimepicker({
			dateFormat:'dd.mm.yy',
			timeFormat:'hh:mm',
			language: 'ru',
		}).mask('99.99.9999 99:99');
	}
	else if($(this).attr('hideYear') == "true")
	{
		$(this).datepicker({
		dateFormat:'dd.mm'
		}).mask('99.99');
	}
	else{
		$(this).datepicker({
		dateFormat:'dd.mm.yy'
		}).mask('99.99.9999');
	}
});

if ($('input[name=check_hash_user]').length && $('input[name=check_hash_user]').val() != $('.check_hash_user').text()) {
	$('input[name=check_hash_user]').val($('.check_hash_user').text());
}

$('select.redirect').change(function () {
	var path = $(this).attr("rel");
	if ($(this).val()) {
		path = path + $(this).attr("name") + $(this).val() + '/';
	}
	window.location.href = path;
});

$(document).tooltip();

function prepare(string) {
	string = str_replace('&lt;', '<', string);
	string = str_replace('&gt;', '>', string);
	string = str_replace('&amp;', '&', string);
	return string;
}

function str_replace(search, replace, subject, count) {
	f = [].concat(search),
		r = [].concat(replace),
		s = subject,
		ra = r instanceof Array, sa = s instanceof Array;
	s = [].concat(s);
	if (count) {
		this.window[count] = 0;
	}
	for (i = 0, sl = s.length; i < sl; i++) {
		if (s[i] === '') {
			continue;
		}
		for (j = 0, fl = f.length; j < fl; j++) {
			temp = s[i] + '';
			repl = ra ? (r[j] !== undefined ? r[j] : '') : r[0];
			s[i] = (temp).split(f[j]).join(repl);
			if (count && s[i] !== temp) {
				this.window[count] += (temp.length - s[i].length) / f[j].length;
			}
		}
	}
	return sa ? s : s[0];
}
function htmlentities(s) {
	var div = document.createElement('div');
	var text = document.createTextNode(s);
	div.appendChild(text);
	return div.innerHTML;
}