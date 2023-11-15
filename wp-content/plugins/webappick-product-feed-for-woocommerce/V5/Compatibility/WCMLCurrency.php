<?php

namespace CTXFeed\V5\Compatibility;

class WCMLCurrency {
	public function __construct() {
		add_filter( 'woo_feed_wcml_price', [$this,'woo_feed_get_wcml_price'], 10, 4 );
	}

	/**
	 * Get price by product id and currency
	 *
	 * @param int $productId Product ID for price convert
	 * @param string $currency currency to convert the price
	 * @param string $price current/raw price
	 * @param string $type Price type (_price , _regular_price or _sale_price)
	 *
	 * @return float               return current price if type is null
	 */

	public function woo_feed_wpml_get_original_post_id( $element_id ) {
		$lang = apply_filters( 'wpml_default_language', '' );

		/**
		 * Get translation of specific language for element id.
		 *
		 * @param int $elementId translated object id
		 * @param string $element_type object type (post type). If set to 'any' wpml will try to detect the object type
		 * @param bool|f`alse $return_original_if_missing return the original if missing.
		 * @param string|null $language_code Language code to get the translation. If set to 'null', wpml will use current language.
		 */
		return apply_filters( 'wpml_object_id', $element_id, 'any', true, $lang );
	}

	public function woo_feed_get_wcml_price( $price, $productId, $currency, $type = null ) {

		if ( class_exists( 'woocommerce_wpml' ) && wcml_is_multi_currency_on() && get_woocommerce_currency() !== $currency ) {
			$originalId = $this->woo_feed_wpml_get_original_post_id( $productId );
			global $woocommerce_wpml;
			if ( get_post_meta( $originalId, '_wcml_custom_prices_status', true ) ) {
				$prices = $woocommerce_wpml->multi_currency->custom_prices->get_product_custom_prices( $originalId, $currency );
				if ( ! empty( $prices ) ) {
					if ( is_null( $type ) ) {
						return $prices['_price'];
					}
					if ( array_key_exists( $type, $prices ) ) {
						return $prices[ $type ];
					} else {
						return $prices['_price'];
					}
				}
			} else {
				$currencies = $woocommerce_wpml->multi_currency->currencies;
				if ( array_key_exists( $currency, $currencies ) ) {
					$price = ( (float) $price * (float) $currencies[ $currency ]['rate'] );
					$price = $woocommerce_wpml->multi_currency->prices->apply_rounding_rules( $price, $currency );
				}
			}
		}

		return (float) $price;
	}
}
