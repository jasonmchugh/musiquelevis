<?php /* Template Name: Contact */  get_header(); ?>

	<div class="content">
		<?php get_template_part( 'views/modules/map', 'localisateur' ); ?>

		<div class="contact__wrap _container">
			<div class="_row">
				<h2><?php _e('Contactez-nous', 'bravad'); ?></h2>
			</div>
			<div class="_row">
				<div class="contact__form">
					<?php the_field('contact-txt'); ?>
					<!--?php echo do_shortcode('[formtastic id="212"]'); ?-->
					<?php echo do_shortcode('[contact-form-7 id="326073" title="Formulaire contact"]'); ?>
				</div>
			</div>
		</div>

	</div>

<?php get_footer(); ?>