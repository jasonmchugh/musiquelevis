<div id="head" class="js-header">
	<div class="_container">
		<div class="_row u-flex">

			<!-- Logo -->
			<div class="_col _col--xl-5 _col--md-6 _col--sm-9">
				<a class="logo__wrap" href="<?php echo get_home_url(); ?>">
					<img src="/wp-content/themes/musiquelevis/assets/img/logos/Musique_Levis_logo.png" alt="<?php echo get_bloginfo(); ?>">
				</a>
			</div>

			<!-- Mobile hamburger -->
			<div class="main-nav__btn--wrapper _col _col--md-3">
				<?php wp_nav_menu( array('menu' => 'Main Nav' )); ?>
				<div class="main-nav__btn"></div>
			</div>

		</div>
	</div>
</div>