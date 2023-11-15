<?php
/**
 * The template for displaying the retailers form
 *
 * @package WordPress
 * @subpackage Bravad
 * @since Bravad 1.0
 */
?>

<form class="retailers u-text-white">
	<div class="input-group">
    	<div>
        	<input type="text" placeholder="<?php _e( 'Province, city, postal code', 'bravad' ); ?>" class="input-white" />
       		<button type="submit" title="<?php _e( 'Find', 'bravad' ); ?>" id="postal" class="btn btn-icon">
       			<img src="/wp-content/plugins/localisation/includes/template/assets/img/localisation.svg">
       		</button>
       		<div class="clear"></div>
       	</div>
	</div>
	<p><?php _e( 'Or', 'bravad' ); ?></p>
	<a href="#" id="detect" class="localisation-btn" title="<?php _e( 'Detect your location', 'bravad' ); ?>"><span><?php _e( 'Detect your location', 'bravad' ); ?></span></a>
</form>