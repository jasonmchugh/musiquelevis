<?php if( get_field('street_view_lat') ) { ?>
	<div id="street-view"></div>

	<script>
      var panorama;
      function initialize() {
        panorama = new google.maps.StreetViewPanorama(
            document.getElementById('street-view'),
            {
              position: {lat:<?php the_field('street_view_lat'); ?>, lng: <?php the_field('street_view_lng'); ?>},
              pov: {heading: <?php the_field('pov'); ?>, pitch: 0},
              zoom: <?php the_field('zoom'); ?>,
              scrollwheel: false,
            });
      }
    </script>
    <script async defer
         src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDCINVRmiU2AlezhqfymFgNAkd14yXTRkk&callback=initialize">
    </script>

<?php } else { ?>

	<?php $imageHeader = get_field('photo');
		if( !empty($imageHeader) ): ?>
		<div class="retailer_header" style="background-image: url(<?php echo $imageHeader['url']; ?>);">
		</div>
	<?php endif; ?>

<?php } ?>
