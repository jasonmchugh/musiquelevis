/*
	MOBILE MENU
	------

	Mobile Menu function

 */
;(function mobileMenu($) {

	$(".main-nav__btn").on('click', function(){
		$('.js-main-nav-wrap').toggleClass('open');
		$(this).toggleClass('opened');
	});

	$(".main-nav").find(".menu-item-has-children").prepend('<span class="submenu-button"></span>');
	$(".main-nav").find('.submenu-button').on('click', function() {
        $(this).toggleClass('opened');
        if ($(this).siblings('ul').hasClass('open')) {
        	$(this).siblings('ul').removeClass('open').slideUp("slow");
        }
        else {
        	$(this).siblings('ul').addClass('open').slideDown("slow");
        }
    });

	// Close mobile menu after clicking on a submenu anchor
    $('#menu-menu-principal li ul a').click(function(){

		if($(window).width() <= 991){
			$('#menu-button').trigger("click");
		}

	});

})(jQuery);