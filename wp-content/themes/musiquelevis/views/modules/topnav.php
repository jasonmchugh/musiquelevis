<?php include_once('preprocess/header.php'); ?>

<div class="top-nav-outer _col _col--xl-8 _col--md-12">

	<nav class="top-nav">

		<!-- My account / Login -->
		<?php if ($bv['user']['isLoggedIn']) : ?>
			<a href="<?php echo $bv['permalinks']['logout']; ?>" class="txt-link u-no-margin"><?php _e('Déconnexion', 'bv'); ?></a>
			<span class="_txt-spacer"></span><span class="sep">|</span><span class="_txt-spacer"></span>
			<a href="<?php echo $bv['permalinks']['account']; ?>" class="txt-link u-no-margin"><?php _e('Mon compte', 'bv'); ?></a>
		<?php else : ?>
			<a href="<?php echo $bv['permalinks']['account']; ?>" class="txt-link u-no-margin"><?php _e('Connexion', 'bv'); ?></a>
		<?php endif; ?>

		<!-- Search form -->
		<form class="srch" action="<?php echo get_home_url(); ?>" method="get">
			<input class="srch__txt" type="text" name="s" placeholder="<?php _e('Recherche', 'bv'); ?>">
			<input type="hidden" name="post_type" value="product">
			<button class="srch__btn" type="submit">
				<svg class="srch__btn-icon" role="presentation">
					<use xlink:href="<?php echo get_template_directory_uri(); ?>/assets/dist/img/icons.svg#search"></use>
				</svg>
			</button>
		</form>
	</nav>

</div>