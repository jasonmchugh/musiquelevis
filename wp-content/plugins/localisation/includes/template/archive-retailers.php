<?php
/**
 * The template for displaying the retailers archive
 *
 * @package WordPress
 * @subpackage Bravad
 * @since Bravad 1.0
 */
?>

	<div class="map__loading">
		<img class="spinner" src="/wp-content/plugins/localisation/includes/template/assets/img/spinner.gif">
	</div>
	<div id="map__canvas"></div>

	<div class="retailers__results js-parallax">
		<div class="_container">
			<div class="_row">
				<h2><?php _e('Nos succursales', 'bravad'); ?></h2>
				<div id="retailers">
					<!-- Append Retailers here -->
				</div>
			</div>
		</div>
	</div>

	<link rel="stylesheet" type="text/css" href="/wp-content/plugins/localisation/includes/template/assets/css/style.css" />
	<!-- AIzaSyBRo9WpA3mw9na-ORLvwrkfneOuh8i8wKM -->
	<!--script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?libraries=geometry&key=AIzaSyC1B6YLZyD2tG9T3MReSFNpXBz4lCeRHSg"></script-->
	<script type="text/javascript" src="/wp-content/plugins/localisation/includes/template/assets/js/markercluster.js"></script>