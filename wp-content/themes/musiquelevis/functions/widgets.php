<?php
/**
 * Register our sidebars and widgetized areas.
 *
 */
function bravad_widgets_init() {

	register_sidebar( array(
		'name'          => 'Footer - Produits',
		'id'            => 'footer1',
	) );

	register_sidebar( array(
		'name'          => 'Footer - Pages',
		'id'            => 'footer2',
	) );

	register_sidebar( array(
		'name'          => 'Footer - Contact',
		'id'            => 'footer3',
	) );

	register_sidebar( array(
		'name'          => 'Footer - Secondary',
		'id'            => 'footer4',
	) );

	register_sidebar( array(
		'name'          => 'Shop Archive - Filters',
		'id'            => 'shoparchive',
		'before_title' => '<h2 class="js-toggle">'
	) );

	register_sidebar( array(
		'name'          => 'Header - Cart',
		'id'            => 'headercart',
	) );

}
add_action( 'widgets_init', 'bravad_widgets_init' );
?>