<?php
	$titleheader = get_field('titre_header');
	$bgheader = get_field('bg_header');
?>

<?php if( $titleheader ): ?>
	<div class="page__header js-parallax" style="background-image: url(<?php echo $bgheader['url']; ?>);">
		<?php if( get_field('titre_header') ): ?>
			<div class="_container">
				<p class="page__title">
					<?php echo $titleheader; ?>
				</p>
			</div>
		<?php endif; ?>
	</div>
<?php endif; ?>