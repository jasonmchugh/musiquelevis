<?php

namespace CTXFeed\V5\Compatibility;

class TranslatePress {
	public function __construct(){
		add_filter( 'woo_feed_get_translatePress_attribute', [$this,'woo_feed_get_tp_translate'], 10, 5 );
	}

	/**
	 * Translate with translatepress plugin.
	 * Switches translatepress's query language to expected language
	 *
	 * @param string $attribute product attribute name
	 * @param mixed $attributeValue product attribute value
	 * @param WC_Product|WC_Product_Variable|WC_Product_Variation|WC_Product_Grouped|WC_Product_External|WC_Product_Composite $product product obj
	 * @param mixed $config feed configuration
	 *
	 * @return mixed
	 * @since  5.2.12
	 *
	 */

	public  function woo_feed_get_tp_translate( $output, $product, $config, $product_attribute, $merchant_attribute ) {

		//when translatepress is activated
		if ( is_plugin_active( 'translatepress-multilingual/index.php' ) ) {

			$feed_language = $config->get_feed_language( $product_attribute );
			if ( isset( $feed_language) && ! empty( $feed_language) ) {
				$feed_language = $feed_language;

				if ( class_exists( 'TRP_Settings' ) && class_exists( 'TRP_Translation_Render' ) ) {

					$settings   = ( new TRP_Settings() )->get_settings();

					$trp_render = new TRP_Translation_Render( $settings );

					global $TRP_LANGUAGE;
					$default_language = $TRP_LANGUAGE;
					$TRP_LANGUAGE     = $feed_language;

					$output   = $trp_render->translate_page( $output ); //@TODO need to make attributeValue as html, description attribute should return html

					//reset trp_language
					$TRP_LANGUAGE = $default_language;
				}
			}
		}

		return $output;
	}
}
