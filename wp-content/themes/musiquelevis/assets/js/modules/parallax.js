/*
	PARALLAX
	------

	Adding a subtile parallax effect to background images

 */
;(function parallaxBg($) {

	$(window).scroll(function() {
		var scrollTop = $(window).scrollTop();
		var divam = -10;
		$(".js-parallax").css({
			"background-position":"center "+scrollTop/divam+"px"
		});
    });


})(jQuery);