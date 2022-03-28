(function($) {
$(function() {

  $('ul.tabs__caption').on('click', 'li:not(.active)', function() {
    $(this)
      .addClass('active').siblings().removeClass('active')
      .closest('div.tabs').find('div.tabs__content').removeClass('active').eq($(this).index()).addClass('active');
  });

});
})(jQuery);

$(document).ready(function(){
	  $('.mainslider').slick({
		  infinite: true,
		  slidesToShow: 1,
		  slidesToScroll: 1,
		  arrows: true,
		  dots: false,
		  nextArrow: '.ab-right',
		  prevArrow: '.ab-left',
	});	
});

$(document).ready(function(){
	  $('.hitsindex').slick({
		  infinite: true,
		  slidesToShow: 4,
		  slidesToScroll: 1,
		  arrows: true,
		  dots: false,
		  nextArrow: '.ab-right-hits',
		  prevArrow: '.ab-left-hits',
		  responsive: [
			{
			  breakpoint:960,
			  settings: {
				slidesToShow: 3,
			  }
			},
			{
			  breakpoint:767,
			  settings: {
				slidesToShow: 2,
			  }
			},
			{
			  breakpoint:480,
			  settings: {
				slidesToShow: 1,
			  }
			},
			
		  ]
		  
		  
	});	
});


/* мобильное меню */

// переменная для текущего размера экрана, по умолчанию - широкий
var windowState = 'large';

// проверка ширины экрана и адаптация меню
$(document).ready(function() {
    var sw = document.body.clientWidth;
    if (sw < 768) {
       smMenu();
    } 
	else {
	   lgMenu();
	}
});

// учитываем возможное изменение размера экрана (например, если перевернуть телефон в горизонтальный режим)
$(window).resize(function() {
	var sw = document.body.clientWidth;
    if (sw < 768 && windowState != 'small') {
       smMenu();
    }
  
    if (sw > 767 && windowState != 'large') {
       lgMenu();
    } 
});

function smMenu() {
	
	$('#mobile-menu').off('click');	
	$('.expand').removeClass('expand');
	$('#mobile-menu').remove();	
    $('.topmenu').before('<button id="mobile-menu"><i class="fa fa-bars" aria-hidden="true"></i> Меню</button>');   
	$('#mobile-menu').click(function() {
		//развернуть меню
		$('.topmenu').toggleClass('expand');
	});
	
	$('#mobile-catalog').off('click');	
	$('.expand').removeClass('expand');
	$('#mobile-catalog').remove();	
    $('.mainmenu').before('<button id="mobile-catalog"><i class="fa fa-bars" aria-hidden="true"></i> Каталог</button>');   
	$('#mobile-catalog').click(function() {
		//развернуть меню
		$('.mainmenu').toggleClass('expand');
	});
	
	/*$('.header .sub_trigger .icon-down-dir, .header_top_line_wrapper .sub_trigger .icon-down-dir').click(function(e) {
		if ($(window).width() < 767) {
			e.preventDefault();
			$(this).parent().parent().find('>ul').stop(true, true).slideToggle(350).end().siblings().find('>ul').slideUp(350);
		}
	});*/
	
	
	$('.mainmenu .fa').click(function(e) {
		if ($(window).width() < 768) {
			e.preventDefault();		
			$(this).parent().parent().find('>ul').stop(true, true).slideToggle(250).end().siblings().find('>ul').slideUp(250);
		}
	});
	
	
	windowState = 'small';
}



//для больших экранов
function lgMenu() {
	
	$('#mobile-menu').off('click');	
	$('.expand').removeClass('expand');
	$('#mobile-menu').remove();
	
	$('#mobile-catalog').off('click');	
	$('#mobile-catalog').remove();
	
    windowState = 'large';
}

/* END мобильное меню */