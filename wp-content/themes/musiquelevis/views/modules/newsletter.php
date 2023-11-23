<?php
/**
 * The template for displaying the Newsletter form
 *
 * @package WordPress
 * @subpackage Bravad
 * @since Bravad 1.0
 */

	// Regex
	$regex1 = array(
		'name'    => "#^[a-zàáäâèéêëìíîïòóôõùúûüÿç' -]{3,}$#i",
		'phone'   => "#^[1]*+[- ]*+[0-9]{3}+[- ]*+[0-9]{3}+[-]*+[0-9]{4}$#",
		'email'   => "#[a-z0-9._-]+@[a-z0-9.-]{2,}[.][a-z]{2,3}#",
		'message' => "#^(.*)#i"
	);

	// Rules to follow
	$rules1 = array(
		'email1'   => $regex1['email'],
	);

	// Error messages array, as displayed on page
	$errorLabels1 = array(
		'email1'   => '',
	);

	// Error messages array
	$errorMsg1 = array(
		'email1'   => __( 'Votre adresse courriel est invalide.', 'bravad' ),
		'valid1'   => __( 'Vous vous êtes abonné à votre infolettre avec succès, merci!', 'bravad' ),
		'invalid1' => __( 'Votre adresse courriel est invalide.', 'bravad' )
	);

	$confirmation1 = '';

	// Initialise valid form boolean
	$isFormValid1 = true;

	if( isset( $_REQUEST['submit1'] ) ) {

		// Loop & validate fields
		foreach( $rules1 as $key1 => $value1 ) {
			if( isset( $_REQUEST[$key1] ) ) {
				if( !preg_match( $value1, $_REQUEST[$key1] ) or $_REQUEST[$key1] == '' ) {
					// Invalid value
					$errorLabels1[$key1] = $errorMsg1[$key1];
					$isFormValid1 = false;
				}
			}
		}

		// If form is valid
		if( $isFormValid1 ) {
			// Send email
			save_wp1($formConfig1);

			// Reset field values
			$_REQUEST['email1']   = '';

			// Confirmation script
			$confirmation1 = $errorMsg1['valid1'];
		} else {
			$confirmation1 .= $errorMsg1['invalid1'];
		}
	}

	function save_wp1($formConfig1) {
		// Create post
		$postData1 = array(
			'post_content'   => $_REQUEST['email1'],
			'post_title'     => $_REQUEST['email1'],
			'post_status'    => 'private',
			'post_type'      => 'newsletter'
		);
		$postId1 = wp_insert_post($postData1);

		if ($postId1 != 0) {
			$formConfig1['status']['isWordpressSaveSuccess'] = true;
		} else {
			$formConfig1['status']['isWordpressSaveSuccess'] = false;
		}

		return $formConfig1;
	}

?>
<div class="newsletter _container">

	<div class="_row">
		<h2><?php the_field('titre_newsletter', 'options') ?></h2>
		<p><?php the_field('texte_newsletter', 'options') ?></p>
	</div>

	<div class="_row">
	</div>

</div>