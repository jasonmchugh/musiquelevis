<?php get_header(); ?>

	<?php
		$bgpage = get_field('bg_section');
	?>

	<div class="page__wrapper">

		<?php get_template_part( 'views/modules/page', 'header' ); ?>

		<div class="page__content _container" style="background-image: url(<?php echo $bgpage['url']; ?>);">

			<div class="_row">
				<h1 id="page__title"><?php _e('Page introuvable', 'bravad'); ?></h1>
			</div>

			<div class="_row u-flex page__row">
				<div class="_col _col--xl-12 _col--md-12 _col--sm-12 page__row--txt">
					<h2><?php _e('Que faire maintenant ?', 'bravad'); ?></h2>
					<p>
						<?php _e('Le plus simple est de suivre ce <a href="/">lien</a> pour vous rendre sur la page d’accueil, mais vous pouvez également vous rendre directement sur l’une des pages de notre site en utilisant le menu de navigation.', 'bravad'); ?>
					</p>
					<p>
						<?php _e('Dans le cas où vous seriez arrivé ici alors que vous suiviez un lien provenant d’un autre site web, vous pouvez <a href="/contact">nous contacter</a> pour nous signaler cette erreur.', 'bravad'); ?>
					</p>

				</div>
			</div>

		</div>

	</div>

<?php get_footer(); ?>