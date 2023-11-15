/*
	SWIPER
	------

	Calling Swiper on the home page

 */
;(function homeSwiper($) {

	// Kickstart the progress bar
	jQuery('.progressbar__fill').css({'width': '100%', 'transition' : '6s'});

	// Home page
	var swiper = new Swiper('.swiper-container', {
	    paginationClickable: true,
	    spaceBetween: 0,
	    loop: true,
		autoplay: {
	        delay: 6000,
	        disableOnInteraction: false,
	    },
	    navigation: {
	        nextEl: '.swiper-button-next',
	        prevEl: '.swiper-button-prev',
	    },
	});
	swiper.on('slideChangeTransitionStart', function () {
		jQuery('.progressbar__fill').css({'width': '0%', 'transition' : 'none'});
	});
	swiper.on('slideChangeTransitionEnd', function () {
		jQuery('.progressbar__fill').css({'width': '100%', 'transition' : '6s'});
	});

})(jQuery);