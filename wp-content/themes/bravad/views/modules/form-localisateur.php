<?php // echo do_shortcode('[localisationform]');  // Formulaire de base ?>

<!-- Formulaire personnalisé -->
<form class="retailers u-text-white">
	<div class="container-fluid">
		<div class="input-group">
			<div class="row">
		    	<div class="col-4">
					<div class="row">
						<label for="postalcode"><?php _e( 'Recherche par adresse, ville ou code postal', 'bravad' ); ?></label>
						<input name="postalcode" type="text" />
						<button type="submit" title="<?php _e( 'Chercher', 'bravad' ); ?>" id="postal" class="btn btn-icon">
							<svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 18.4 19.1" style="enable-background:new 0 0 18.4 19.1;" xml:space="preserve">
								<path d="M18.1,17.4l-4.5-4.7c1.2-1.4,1.8-3.1,1.8-5C15.4,3.5,12,0,7.7,0S0,3.5,0,7.7s3.5,7.7,7.7,7.7c1.6,0,3.1-0.5,4.4-1.4l4.6,4.8 c0.2,0.2,0.4,0.3,0.7,0.3c0.3,0,0.5-0.1,0.7-0.3C18.5,18.4,18.5,17.8,18.1,17.4z M7.7,2c3.1,0,5.7,2.6,5.7,5.7s-2.6,5.7-5.7,5.7 S2,10.8,2,7.7S4.6,2,7.7,2z"/>
							</svg>
						</button>
					</div>
					<div class="row radius_filter">
						<label for="radius"><?php _e( 'Distance', 'bravad' ); ?></label>
						<select id="radius" name="radius">
							<option value="10"><?php _e( '10 km', 'bravad' ); ?></option>
							<option value="25"><?php _e( '25 km', 'bravad' ); ?></option>
							<option value="50"><?php _e( '50 km', 'bravad' ); ?></option>
							<option value="100"><?php _e( '100 km', 'bravad' ); ?></option>
							<option value="1000"><?php _e( '1000 km', 'bravad' ); ?></option>
						</select>
					</div>
					<div class="clear"></div>
				</div>
		        <div class="col-8">
			        <div class="row filter_row">
				        <div class="col-lg-3">
							<label><?php _e( 'Recherche par centre', 'bravad' ); ?></label>
				        </div>
				        <div class="col-lg-9" id="retailer_type">
							<div class="filter_choice">
								<input name="capiliacheck" type="checkbox" id="capiliacheck" value="capilia" checked=""><label for="capiliacheck">Capilia</label>
							</div>
							<div class="filter_choice">
								<input name="headfirstcheck" type="checkbox" id="headfirstcheck" value="headfirst" checked=""><label for="headfirstcheck">Headfirst</label>
							</div>
				        </div>
			        </div>
			        <div class="row filter_row filter_speciality">
				    	<div class="col-lg-3">
				        	<label><?php _e( 'Recherche par spécialités', 'bravad' ); ?></label>
				    	</div>
				    	<div class="col-lg-9" id="retailer_speciality">
					    	<div class="row">
						    	<div class="col-lg-6">
									<div class="filter_choice">
										<input name="greffecheck" type="checkbox" id="greffecheck" value="greffe-capillaire" checked=""><label for="greffecheck"><?php _e( 'Greffe capillaire', 'bravad' ); ?></label>
									</div>
									<div class="filter_choice">
										<input name="traitementlasercheck" type="checkbox" id="traitementlasercheck" value="traitement-laser" checked=""><label for="traitementlasercheck"><?php _e( 'Traitement laser', 'bravad' ); ?></label>
									</div>
						    	</div>
						    	<div class="col-lg-6">
							    	<div class="filter_choice">
										<input name="traitementprpcheck" type="checkbox" id="traitementprpcheck" value="traitement-prp" checked=""><label for="traitementprpcheck"><?php _e( 'Traitement PRP', 'bravad' ); ?></label>
									</div>
									<div class="filter_choice">
										<input name="micropigmentationcheck" type="checkbox" id="micropigmentationcheck" value="micropigmentation" checked=""><label for="micropigmentationcheck"><?php _e( 'Micropigmentation', 'bravad' ); ?></label>
									</div>
						    	</div>
					    	</div>
				    	</div>
			        </div>
			        <div class="clear"></div>
		    	</div>
			</div>
		</div>
<!-- 	<a href="#" id="detect" class="localisation-btn" title="<?php _e( 'Detect your location', 'bravad' ); ?>"><span><?php _e( 'Detect your location', 'bravad' ); ?></span></a> -->
	</div>
</form>