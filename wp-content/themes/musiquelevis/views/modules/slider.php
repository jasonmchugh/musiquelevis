<?php if( have_rows('carrousel') ): ?>
	<div class="swiper-container">

		<div class="swiper-wrapper">
			<?php while( have_rows('carrousel') ): the_row();
				$image = get_sub_field('image_slideshow');
				$title = get_sub_field('titre_slideshow');
				$content = get_sub_field('texte_slideshow');
				$linktxt = get_sub_field('bouton_texte_slideshow');
				$link = get_sub_field('bouton_lien');
				?>

				<div class="swiper-slide" style="background-image: url(<?php echo str_replace("kijiji.","",$image['url']); ?>)">
					<div class="_container">
						<div class="swiper__slide--content">
							<h2><?php echo $title; ?></h2>
							<p><?php echo $content; ?></p>
							<?php if( $linktxt ): ?>
								<a class="btn btn--yellow" href="<?php echo $link; ?>">
									<?php echo $linktxt; ?>
								</a>
							<?php endif; ?>
						</div>
					</div>
				</div>

			<?php endwhile; ?>
	    </div>

	    <!-- Add Arrows -->
	    <div class="swiper-button-next"></div>
	    <div class="swiper-button-prev"></div>

	    <!-- Progress bar -->
	    <div class="progressbar">
		    <span class="progressbar__fill"></span>
		</div>

	</div>
<?php endif; ?>