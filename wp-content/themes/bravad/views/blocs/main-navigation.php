<div class="main-nav__wrap js-main-nav-wrap">
	<?php get_template_part( 'views/modules/topnav' ); ?>

	<!-- Main Nav & Quick Cart -->
	<nav class="main-nav js-main-nav">
		<div class="_container">
			<?php wp_nav_menu( array('menu' => 'Main Nav' )); ?>
			<?php get_template_part( 'views/modules/cart' ); ?>
		</div>
	</nav>

</div>