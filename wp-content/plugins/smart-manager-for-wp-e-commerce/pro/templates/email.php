<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$email_heading = str_replace('( ', '(', ucwords(str_replace('(', '( ', $records_str))) .' '.( ( $background_process_params['process_name'] == 'Duplicate Records' ) ? __('Duplicated', 'smart-manager-for-wp-e-commerce' ) : __('Updated', 'smart-manager-for-wp-e-commerce' ) );

if ( function_exists( 'wc_get_template' ) ) {
	wc_get_template( 'emails/email-header.php', array( 'email_heading' => $email_heading ) );
} else if ( function_exists( 'woocommerce_get_template' ) ) {
	woocommerce_get_template( 'emails/email-header.php', array( 'email_heading' => $email_heading ) );
} else {
	echo $email_heading;
}

add_filter( 'wp_mail_content_type','sm_beta_pro_batch_set_content_type' );

function sm_beta_pro_batch_set_content_type(){
    return "text/html";
}

?>
<style type="text/css">
	.sm_code {
		padding: 3px 5px 2px;
		margin: 0 1px;
		background: rgba(0,0,0,.07);
	}
	#template_header {
		background-color: #7748AA !important;
		text-align: center !important;
	}
</style>
<?php

$msg_body = '<p>'. __( 'Hi there!', 'smart-manager-for-wp-e-commerce'  ) .'</p>
			<p>'. __( 'Smart Manager has successfully completed', 'smart-manager-for-wp-e-commerce'  ) .' \''. $background_process_params['process_name'] .'\' process on <span class="sm_code">'. get_bloginfo() .'</span>. </p>';

			if( !empty( $actions ) ) {
				$msg_body .= '<p>'. __('Below are the lists of updates done:','smart-manager-for-wp-e-commerce' ) .'</p>
							<p> <table cellspacing="0" cellpadding="6" border="1" style="text-align:center;color:'.$email_text_color.' !important;margin-bottom: 25px;border: 1px solid #e5e5e5;">
							  <tr style="font-weight:bold;color:'.$email_heading_color.' !important;">
							    <th style="border: 1px solid #e5e5e5;">'. __('Field', 'smart-manager-for-wp-e-commerce' ) .'</th>
							    <th style="border: 1px solid #e5e5e5;">'. __('Action', 'smart-manager-for-wp-e-commerce' ) .'</th>
							    <th style="border: 1px solid #e5e5e5;">'. __('Value', 'smart-manager-for-wp-e-commerce' ) .'</th>
							    <th style="border: 1px solid #e5e5e5;">'. __('Records Updated', 'smart-manager-for-wp-e-commerce' ) .'</th>
							  </tr>';

				foreach ( $actions as $action ) {
				  	$msg_body .= '<tr style="font-size: 14px;">
								    <td style="border: 1px solid #e5e5e5;">'. ( ! empty( $action['meta']['displayTitles']['field'] ) ? $action['meta']['displayTitles']['field'] : $action['type'] ) .'</td>
								    <td style="border: 1px solid #e5e5e5;">'. ( ! empty( $action['meta']['displayTitles']['operator'] ) ? $action['meta']['displayTitles']['operator'] : $action['operator'] ) .'</td>
								    <td style="border: 1px solid #e5e5e5;">'. ( ! empty( $action['value_display_text'] ) ? $action['value_display_text'] : ( is_array( $action['value'] ) ? $action['value'][0] : $action['value'] ) ) .'</td>
								    <td style="border: 1px solid #e5e5e5;">'. $records_str .'</td>
								  </tr>';
				}

				$msg_body .= '</table> </p>';
			}
			
			$msg_body .= '<br/>
							<p>
							<div style="color:#9e9b9b;font-size:0.95em;text-align: center;"> <div> '. __('If you like', 'smart-manager-for-wp-e-commerce' ) .' <strong>'. __('Smart Manager', 'smart-manager-for-wp-e-commerce' ) .'</strong> '. __('please leave us a', 'smart-manager-for-wp-e-commerce' ) .' <a href="https://wordpress.org/support/view/plugin-reviews/smart-manager-for-wp-e-commerce?filter=5#postform" target="_blank" data-rated="Thanks :)">★★★★★</a> '.__('rating. A huge thank you from StoreApps in advance!', 'smart-manager-for-wp-e-commerce' ).'.</div>';


echo $msg_body;
echo '<br>';

if ( function_exists( 'wc_get_template' ) ) {
	wc_get_template( 'emails/email-footer.php' );
} else if ( function_exists( 'woocommerce_get_template' ) ) {
	woocommerce_get_template( 'emails/email-footer.php' );
}
