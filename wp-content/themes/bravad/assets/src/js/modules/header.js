/*
	HEADER
	------

	Set header classes on scroll.
	Available classes :
		"nav-down" : User is scrolling up, the nav is down
		"nav-up" : User is scrolling down, the nav is up
 */
;(function header($) {

	/**
	 * SCROLL
	 *
	*/
	var didScroll;
	var lastScrollTop = 0;
	var delta = 5;
	var navbarHeight = 0;
	var win = $(window).height();

	$(window).scroll(function(event){
	    didScroll = true;
	});

	setInterval(function() {
	    if (didScroll) {
	        hasScrolled();
	        didScroll = false;
	    }
	}, 250);

	function hasScrolled() {
	    var st = $(this).scrollTop();

	    if(Math.abs(lastScrollTop - st) <= delta)
	        return;

	    // If they scrolled down and are past the navbar, add class .nav-up.
	    if($(window).width() >= 980){
		    if (st > lastScrollTop && st > navbarHeight){
		        // Scroll Down
		        $('#header').removeClass('nav-down').addClass('nav-up');
		        $('#menu-main-nav').css('padding-left', '5.313em');
		        $('.logo-small').fadeIn('fast');
		    } else {
		        // Scroll Up
		        if(st + $(window).height() < $(document).height()) {
		            $('#header').removeClass('nav-up').addClass('nav-down');
		            $('.logo-small').fadeOut('fast');
		            $('#menu-main-nav').css('padding-left', '0');
		        }
		    }
		    lastScrollTop = st;
	    }
	}

	$('#menu-main-nav').prepend('<a class="logo-small" href="/" style="display:none;"></a>');

})(jQuery);