<?php get_header(); ?>

	<div class="col-lg-12 col-md-12 col-sm-12 xpert-info">

		<!-- Infos -->
		<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 infos-wrapper">
			<div class="infos">
				<div class="intro">
					<h1><?php the_field('titre_detaillant'); ?></h1>
					<?php the_field('description_detaillant'); ?>
				</div>
				<div class="xpert-coord">
					<div class="col-lg-4 col-md-6 col-sm-12 col-xs-12">
						<?php $image = get_field('photo_detaillant');

						if( !empty($image) ): ?>

							<img src="<?php echo $image['url']; ?>" alt="<?php echo $image['alt']; ?>" />

						<?php endif; ?>
					</div>
					<div class="col-lg-8 col-md-6 col-sm-12 col-xs-12">
						<p><?php the_field('titre_detaillant'); ?></p>
						<p><?php the_field('nom_detaillant'); ?></p>
						<p class="mail">
							<?php if(get_field('courriel_detaillant')): ?>
								<?php while(has_sub_field('courriel_detaillant')): ?>
									<a href="mailto:<?php the_sub_field('courriel'); ?>"><?php the_sub_field('courriel'); ?></a>
								<?php endwhile; ?>
							<?php endif; ?>
						</p>

						<p class="phone"><?php _e( 'Phone:', 'bravad' ); ?>
							<?php if(get_field('telephone_detaillant')): ?>
								<?php while(has_sub_field('telephone_detaillant')): ?>
									<span><?php the_sub_field('telephone'); ?></span>
								<?php endwhile; ?>
							<?php endif; ?>
						</p>
						<p><?php _e( 'RBQ:', 'bravad' ); ?> <?php the_field('rbq_detaillant'); ?></p>
						<?php $apchq = get_field('apchq'); ?>
							<?php if( $apchq == 'oui' ){ ?>
								<p><?php _e( 'APCHQ Member', 'bravad' ); ?></p>
							<?php } else if( $apchq  == 'non' ){ ?>

						<?php } ?>
					</div>
					<div class="clear"></div>
				</div>
			</div>
		</div>

		<!-- Map -->
		<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
			<div id="map-canvas" class="map map-contact"></div>
		</div>

		<div class="clear"></div>
	</div>
	<div class="col-lg-12 contact-form" id="contact-form">
		<div class="container">
			<?php the_field('formulaire'); ?>
		</div>
	</div>

	<div class="page-content">

		<div class="col-lg-12">
			<div class="toggle">
				<h2 id="toggle1"><?php _e( 'Quebec', 'bravad' ); ?></h2>
				<div id="togglcontent1" style="display: none;">
					<div class="container">
						<?php
				            $postsPerPage = -1;
				            $args = array(
				                    'post_type' => 'retailers',
				                    'posts_per_page' => $postsPerPage,
				                    'order' => 'ASC',
				                    'orderby' => 'name',
				                    'tax_query' => array(
								        array (
								            'taxonomy' => 'province',
								            'field' => 'slug',
								            'terms' => 'quebec',
								        )
								    ),
				            );
				            $loop = new WP_Query($args);
				            while ($loop->have_posts()) : $loop->the_post();  ?>

							<div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
								<a class="retailer" href="<?php the_permalink(); ?> ">
									<?php the_title(); ?>
								</a>
							</div>
						<?php endwhile; wp_reset_postdata(); ?>
						<div class="clear"></div>
					</div>
				</div>
			</div>

			<div class="toggle">
				<h2 id="toggle2"><?php _e( 'New-Brunswick', 'bravad' ); ?></h2>
				<div id="togglcontent2" style="display: none;">
					<div class="container">
						<?php
				            $postsPerPage = -1;
				            $args = array(
				                    'post_type' => 'retailers',
				                    'posts_per_page' => $postsPerPage,
				                    'order' => 'ASC',
				                    'orderby' => 'name',
				                    'tax_query' => array(
								        array (
								            'taxonomy' => 'province',
								            'field' => 'slug',
								            'terms' => 'new-brunswick',
								        )
								    ),
				            );
				            $loop = new WP_Query($args);
				            while ($loop->have_posts()) : $loop->the_post();  ?>

							<div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
								<a class="retailer" href="<?php the_permalink(); ?> ">
									<?php the_title(); ?>
								</a>
							</div>
							<?php endwhile; wp_reset_postdata(); ?>
						<div class="clear"></div>
					</div>
				</div>
			</div>

			<div class="toggle">
				<h2 id="toggle3"><?php _e( 'Ontario', 'bravad' ); ?></h2>
				<div id="togglcontent3" style="display: none;">
					<div class="container">
						<?php
				            $postsPerPage = -1;
				            $args = array(
				                    'post_type' => 'retailers',
				                    'posts_per_page' => $postsPerPage,
				                    'order' => 'ASC',
				                    'orderby' => 'name',
				                    'tax_query' => array(
								        array (
								            'taxonomy' => 'province',
								            'field' => 'slug',
								            'terms' => 'ontario',
								        )
								    ),
				            );
				            $loop = new WP_Query($args);
				            while ($loop->have_posts()) : $loop->the_post();  ?>

							<div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
								<a class="retailer" href="<?php the_permalink(); ?> ">
									<?php the_title(); ?>
								</a>
							</div>
						<?php endwhile; wp_reset_postdata(); ?>
						<div class="clear"></div>
					</div>
				</div>
			</div>

		</div>

	</div>
	<div class="col-lg-12 bloc_txt100">
		<div class="container">
			<h1><?php the_field('titre_txt100', 12); ?></h1>
			<?php the_field('contenu_txt100', 12); ?>

			<?php if( get_field('boutontexte_txt100', 12) ): ?>
				<?php $colorav = get_field('boutoncouleur_txt100', 12); ?>
				<?php if( $colorav == 'jaune' ){ ?>
					<a href="<?php the_field('boutonlien_txt100', 12); ?>" class="btn btn_jaune"><?php the_field('boutontexte_txt100', 12); ?></a>
				<?php } else if( $colorav  == 'bleu' ){ ?>
					<a href="<?php the_field('boutonlien_txt100', 12); ?>" class="btn btn_bleu"><?php the_field('boutontexte_txt100', 12); ?></a>
				<?php } else if( $colorav  == 'noir' ){ ?>
					<a href="<?php the_field('boutonlien_txt100', 12); ?>" class="btn btn_noir"><?php the_field('boutontexte_txt100', 12); ?></a>
				<?php } ?>
			<?php endif; ?>
		</div>
	</div>
	<div class="clear"></div>

<?php get_footer(); ?>