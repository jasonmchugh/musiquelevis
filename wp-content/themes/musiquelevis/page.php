<?php get_header(); ?>

	<?php
		$bgpage = get_field('bg_section');
	?>

	<div class="page__wrapper">

		<?php get_template_part( 'views/modules/page', 'header' ); ?>

		<div class="page__content _container" style="background-image: url(<?php echo $bgpage['url']; ?>);">

			<?php if (have_posts()) : ?>
				<?php while (have_posts()) : ?>
					<?php the_post(); ?>
					<?php the_content(); ?>
				<?php endwhile; ?>
			<?php endif; ?>


			<?php if( have_rows('blocs_contenu') ): ?>
				<div class="_row">
					<h1 id="page__title"><?php the_title(); ?></h1>
				</div>
				<?php while( have_rows('blocs_contenu') ): the_row();
					$image = get_sub_field('image');
					$content = get_sub_field('contenu');
					$title = get_sub_field('titre');
					?>
					<div class="_row u-flex page__row">
						<?php if( get_sub_field('image') ) { ?>
							<div class="_col _col--xl-6 _col--md-6 _col--sm-12 page__row--img">
								<img src="<?php echo $image['url']; ?>" alt="<?php echo $image['alt'] ?>" />
							</div>
							<div class="_col _col--xl-6 _col--md-6 _col--sm-12 page__row--txt">
								<h2><?php echo $title; ?></h2>
							    <?php echo $content; ?>
							</div>
						<?php } else { ?>
							<div class="_col _col--xl-12 _col--md-12 _col--sm-12 page__row--txt">
								<h2><?php echo $title; ?></h2>
							    <?php echo $content; ?>
							</div>
						<?php } ?>
					</div>
				<?php endwhile; ?>
			<?php endif; ?>
		</div>

		<?php if( have_rows('blocs') ): ?>
			<?php while( have_rows('blocs') ): the_row(); ?>

				<?php if( get_row_layout() == 'bloc_image' ):
					$imageBlocImg = get_sub_field('image');
					$titleBlocImg = get_sub_field('titre');
				?>
					<div class="bloc__image" style="background-image: url(<?php echo $imageBlocImg['url']; ?>);">
						<?php $overlay = get_sub_field('overlay'); ?>

						<?php if( $overlay == 'oui' ){ ?>
							<div class="bloc__image--overlay">
								<h2><?php echo $titleBlocImg; ?></h2>
							</div>
						<?php } else { ?>
							<h2><?php echo $titleBlocImg; ?></h2>
						<?php } ?>
					</div>
				<?php endif; ?>

				<?php if( get_row_layout() == 'bloc_texte' ):
					$imageBlocImg = get_sub_field('image');
					$titleBlocImg = get_sub_field('titre');
				?>
					<div class="bloc__texte _container">
						<div class="_row">
							<div class="_col _col--xl-12">
								<h3><?php the_sub_field('titre'); ?></h3>
								<div class="bloc__texte--intro">
									<?php the_sub_field('introduction'); ?>
								</div>
							</div>
						</div>


						<?php if( have_rows('colonnes_texte') ): ?>
							<div class="_row">
								<?php
									$allowed_classnames = array(
									    1 => '12',
									    2 => '6',
									);

									$number_of_cols = count( get_sub_field( 'colonnes_texte' ) );

									$classname_to_use = $allowed_classnames[1];

									if ( array_key_exists( $number_of_cols , $allowed_classnames ) ) {
									    $classname_to_use = $allowed_classnames[$number_of_cols];
									}

									while( has_sub_field( 'colonnes_texte' ) ) : ?>
									<div class="_col _col--xl-<?php echo esc_attr( $classname_to_use ); ?>  _col--md-<?php echo esc_attr( $classname_to_use ); ?>  _col--sm-12">
										<?php the_sub_field('colonne'); ?>
									</div>
								<?php endwhile; ?>
							</div>
						<?php endif; ?>

					</div>
				<?php endif; ?>

			<?php endwhile; ?>
		<?php endif;  ?>

	</div>

<?php get_footer(); ?>