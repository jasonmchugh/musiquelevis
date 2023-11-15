/*
	CATEGORIES MENU
	------

	Sidebar category menu (/produits).
 */
;(function sidebarCategories($) {

	$('.cat-parent .children').hide();
	$('.cat-parent').append('<a class="js-catToggle"></a>');

	// Open parents toggles on sub-category page
	$('.cat-parent').each(function() {
		if( $(this).hasClass('current-cat-parent') ){
			$(this).children('.children').show();
			$(this).children('.js-catToggle').addClass('open');
		}
	});

	// Toggle
	$('.js-catToggle').click(function() {
		$(this).toggleClass('open');
		$(this).siblings('.children').slideToggle();
	});

})(jQuery);