<?php get_header();

	add_filter( 'formtastic_hidden_value', 'custom_email', 10, 2 );
	function custom_email( $output, $id ) {
	    if ( $id == 'ft_5a3d60e3d4230' ) {
	        $output = get_field('courriel');
	    }

	    return $output;
	}

?>
	<?php $location = get_field('adresse'); ?>

	<div class="content">

		<?php get_template_part( 'views/modules/streetview'); ?>

		<div class="retailers__results js-parallax">
			<div class="_container">
				<div class="_row">
					<h1><?php the_title(); ?></h1>
				</div>
				<div class="_row">
					<div class="_col _col--xl-4 _col--md-4 _col--sm-12">
						<h3><?php _e('Adresse', 'bravad'); ?></h3>
						<?php $address = explode( "," , $location['address']);
							echo $address[0].','.$address[1].'<br/>'; //street number and line break
							echo $address[2].','.$address[3].'<br/>'; //city, state
							echo $address[4]; // Postal Code
						?>
					</div>
					<div class="_col _col--xl-4 _col--md-4 _col--sm-12">
						<h3><?php _e('Horaire', 'bravad'); ?></h3>
						<?php the_field('horaire'); ?>
					</div>
					<div class="_col _col--xl-4 _col--md-4 _col--sm-12">
						<h3><?php _e('Contact', 'bravad'); ?></h3>

						<?php if( get_field('telephone') ): ?>
							<a href="tel:<?php the_field('telephone'); ?>"><?php the_field('telephone'); ?></a>
						<?php endif; ?>

						<?php if( get_field('courriel') ): ?>
							<a href="mailto:<?php the_field('courriel'); ?>"><?php the_field('courriel'); ?></a>
						<?php endif; ?>

						<?php if( get_field('facebook') ): ?>
							<a class="facebook" href="<?php the_field('facebook'); ?>" target="_blank">facebook</a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>

		<div class="contact__wrap _container">
			<div class="_row">
				<h2><?php _e('Contactez-nous', 'bravad'); ?></h2>
			</div>
			<div class="_row">
				<div class="contact__form">
					<?php the_field('contact-txt'); ?>
					<?php the_field('contact-form'); ?>
				</div>
			</div>
		</div>

	</div>

<?php get_footer(); ?>