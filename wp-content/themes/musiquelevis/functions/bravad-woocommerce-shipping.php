<?php

/**
	 * Segment Orders in store-by-stores packages
	 */
	function split_item_class_items( $packages ) {

		$found_item = $found_item2 = $found_item3 = $found_item4 = $found_item5 = $found_item6 = $found_item7 = $found_item8 = $found_item9 = $found_item10 = false;

		$new_package = $new_package2 = $new_package3 = $new_package4 = $new_package5 = $new_package6 = $new_package7 = $new_package8 = $new_package9 = $new_package10 = current( $packages );

		$new_package['contents'] = $new_package2['contents'] = $new_package3['contents'] = $new_package4['contents'] = $new_package5['contents'] = $new_package6['contents'] = $new_package7['contents'] = $new_package8['contents'] = $new_package9['contents'] = $new_package10['contents'] = array();

				$new_package['contents_cost'] = $new_package2['contents_cost'] = $new_package3['contents_cost'] = $new_package4['contents_cost'] = $new_package5['contents_cost'] = $new_package6['contents_cost'] = $new_package7['contents_cost'] = $new_package8['contents_cost'] = $new_package9['contents_cost'] = $new_package10['contents_cost'] = 0;

		$new_package['applied_coupons'] = $new_package2['applied_coupons'] = $new_package3['applied_coupons'] = $new_package4['applied_coupons'] = $new_package5['applied_coupons'] = $new_package6['applied_coupons'] = $new_package7['applied_coupons'] = $new_package8['applied_coupons'] = $new_package9['applied_coupons'] = $new_package10['applied_coupons'] = array();

		$new_package['ship_via'] = $new_package2['ship_via'] = $new_package3['ship_via'] = $new_package4['ship_via'] = $new_package5['ship_via'] = $new_package6['ship_via'] = $new_package7['ship_via'] = $new_package8['ship_via'] = $new_package9['ship_via'] = $new_package10['ship_via'] = array( 'flat_rate', 'local_pickup' );



		$NSfound_item = $NSfound_item2 = $NSfound_item3 = $NSfound_item4 = $NSfound_item5 = $NSfound_item6 = $NSfound_item7 = $NSfound_item8 = $NSfound_item9 = $NSfound_item10 = false;

		$NSnew_package = $NSnew_package2 = $NSnew_package3 = $NSnew_package4 = $NSnew_package5 = $NSnew_package6 = $NSnew_package7 = $NSnew_package8 = $NSnew_package9 = $NSnew_package10 = current( $packages );

		$NSnew_package['contents'] = $NSnew_package2['contents'] = $NSnew_package3['contents'] = $NSnew_package4['contents'] = $NSnew_package5['contents'] = $NSnew_package6['contents'] = $NSnew_package7['contents'] = $NSnew_package8['contents'] = $NSnew_package9['contents'] = $NSnew_package10['contents'] = array();

				$NSnew_package['contents_cost'] = $NSnew_package2['contents_cost'] = $NSnew_package3['contents_cost'] = $NSnew_package4['contents_cost'] = $NSnew_package5['contents_cost'] = $NSnew_package6['contents_cost'] = $NSnew_package7['contents_cost'] = $NSnew_package8['contents_cost'] = $NSnew_package9['contents_cost'] = $NSnew_package10['contents_cost'] = 0;

		$NSnew_package['applied_coupons'] = $NSnew_package2['applied_coupons'] = $NSnew_package3['applied_coupons'] = $NSnew_package4['applied_coupons'] = $NSnew_package5['applied_coupons'] = $NSnew_package6['applied_coupons'] = $NSnew_package7['applied_coupons'] = $NSnew_package8['applied_coupons'] = $NSnew_package9['applied_coupons'] = $NSnew_package10['applied_coupons'] = array();

		$NSnew_package['ship_via'] = $NSnew_package2['ship_via'] = $NSnew_package3['ship_via'] = $NSnew_package4['ship_via'] = $NSnew_package5['ship_via'] = $NSnew_package6['ship_via'] = $NSnew_package7['ship_via'] = $NSnew_package8['ship_via'] = $NSnew_package9['ship_via'] = $NSnew_package10['ship_via'] = array( 'local_pickup' );


		$drummondville                  = 'Drummondville';
		$sherbrookeEst                  = 'Sherbrooke King Est';
		$sherbrookeBelevedere           = 'Sherbrooke Belvédère Sud';
		$levis							= 'Lévis';
		$vanier							= 'Vanier';
		$hamel							= 'Hamel';
		$beauport						= 'Beauport';
		$langelier						= 'Langelier';
		$premiereavenue					= 'Première Avenue';
		$limoilou						= 'Limoilou';


		foreach ( WC()->cart->get_cart() as $item_key => $item ) {


			// Is the product in the Drummondville class?
			if ( $drummondville === $item['data']->get_attribute( 'pa_succursale' ) ) {
				$noshipping = $item['data']->get_attribute( 'pa_no-shipping' );
				if ( $noshipping == 1 ) {
					$NSfound_item                           = true;
					$NSnew_package['contents'][ $item_key ]  = $item;
					$NSnew_package['contents_cost']         += $item['line_total'];
				} else {
					$found_item                           = true;
					$new_package['contents'][ $item_key ]  = $item;
					$new_package['contents_cost']         += $item['line_total'];
				}
				// Remove from original package
				$packages[0]['contents_cost'] = $packages[0]['contents_cost'] - $item['line_total'];
				unset( $packages[0]['contents'][ $item_key ] );

				// If there are no items left in the previous package, remove it completely.
				if ( empty( $packages[0]['contents'] ) ) {
					unset( $packages[0] );
				}
			} else if ( $sherbrookeEst === $item['data']->get_attribute( 'pa_succursale' ) ) {
				$noshipping2 = $item['data']->get_attribute( 'pa_no-shipping' );
				if ( $noshipping2 == 1 ) {
					$NSfound_item2                           = true;
					$NSnew_package2['contents'][ $item_key ]  = $item;
					$NSnew_package2['contents_cost']         += $item['line_total'];
				} else {
					$found_item2                           = true;
					$new_package2['contents'][ $item_key ]  = $item;
					$new_package2['contents_cost']         += $item['line_total'];
				}
				// Remove from original package
				$packages[0]['contents_cost'] = $packages[0]['contents_cost'] - $item['line_total'];
				unset( $packages[0]['contents'][ $item_key ] );

				// If there are no items left in the previous package, remove it completely.
				if ( empty( $packages[0]['contents'] ) ) {
					unset( $packages[0] );
				}
			} else if ( $sherbrookeBelevedere === $item['data']->get_attribute( 'pa_succursale' ) ) {
				$noshipping3 = $item['data']->get_attribute( 'pa_no-shipping' );
				if ( $noshipping3 == 1 ) {
					$NSfound_item3                           = true;
					$NSnew_package3['contents'][ $item_key ]  = $item;
					$NSnew_package3['contents_cost']         += $item['line_total'];
				} else {
					$found_item3                           = true;
					$new_package3['contents'][ $item_key ]  = $item;
					$new_package3['contents_cost']         += $item['line_total'];
				}
				// Remove from original package
				$packages[0]['contents_cost'] = $packages[0]['contents_cost'] - $item['line_total'];
				unset( $packages[0]['contents'][ $item_key ] );

				// If there are no items left in the previous package, remove it completely.
				if ( empty( $packages[0]['contents'] ) ) {
					unset( $packages[0] );
				}
			} else if ( $levis === $item['data']->get_attribute( 'pa_succursale' ) ) {
				$noshipping4 = $item['data']->get_attribute( 'pa_no-shipping' );
				if ( $noshipping4 == 1 ) {
					$NSfound_item4                           = true;
					$NSnew_package4['contents'][ $item_key ]  = $item;
					$NSnew_package4['contents_cost']         += $item['line_total'];
				} else {
					$found_item4                           = true;
					$new_package4['contents'][ $item_key ]  = $item;
					$new_package4['contents_cost']         += $item['line_total'];
				}
				// Remove from original package
				$packages[0]['contents_cost'] = $packages[0]['contents_cost'] - $item['line_total'];
				unset( $packages[0]['contents'][ $item_key ] );

				// If there are no items left in the previous package, remove it completely.
				if ( empty( $packages[0]['contents'] ) ) {
					unset( $packages[0] );
				}
			} else if ( $vanier === $item['data']->get_attribute( 'pa_succursale' ) ) {
				$noshipping5 = $item['data']->get_attribute( 'pa_no-shipping' );
				if ( $noshipping5 == 1 ) {
					$NSfound_item5                           = true;
					$NSnew_package5['contents'][ $item_key ]  = $item;
					$NSnew_package5['contents_cost']         += $item['line_total'];
				} else {
					$found_item5                           = true;
					$new_package5['contents'][ $item_key ]  = $item;
					$new_package5['contents_cost']         += $item['line_total'];
				}
				// Remove from original package
				$packages[0]['contents_cost'] = $packages[0]['contents_cost'] - $item['line_total'];
				unset( $packages[0]['contents'][ $item_key ] );

				// If there are no items left in the previous package, remove it completely.
				if ( empty( $packages[0]['contents'] ) ) {
					unset( $packages[0] );
				}
			} else if ( $hamel === $item['data']->get_attribute( 'pa_succursale' ) ) {
				$noshipping6 = $item['data']->get_attribute( 'pa_no-shipping' );
				if ( $noshipping6 == 1 ) {
					$NSfound_item6                           = true;
					$NSnew_package6['contents'][ $item_key ]  = $item;
					$NSnew_package6['contents_cost']         += $item['line_total'];
				} else {
					$found_item6                           = true;
					$new_package6['contents'][ $item_key ]  = $item;
					$new_package6['contents_cost']         += $item['line_total'];
				}
				// Remove from original package
				$packages[0]['contents_cost'] = $packages[0]['contents_cost'] - $item['line_total'];
				unset( $packages[0]['contents'][ $item_key ] );

				// If there are no items left in the previous package, remove it completely.
				if ( empty( $packages[0]['contents'] ) ) {
					unset( $packages[0] );
				}
			} else if ( $beauport === $item['data']->get_attribute( 'pa_succursale' ) ) {
				$noshipping7 = $item['data']->get_attribute( 'pa_no-shipping' );
				if ( $noshipping7 == 1 ) {
					$NSfound_item7                           = true;
					$NSnew_package7['contents'][ $item_key ]  = $item;
					$NSnew_package7['contents_cost']         += $item['line_total'];
				} else {
					$found_item7                           = true;
					$new_package7['contents'][ $item_key ]  = $item;
					$new_package7['contents_cost']         += $item['line_total'];
				}
				// Remove from original package
				$packages[0]['contents_cost'] = $packages[0]['contents_cost'] - $item['line_total'];
				unset( $packages[0]['contents'][ $item_key ] );

				// If there are no items left in the previous package, remove it completely.
				if ( empty( $packages[0]['contents'] ) ) {
					unset( $packages[0] );
				}
			} else if ( $langelier === $item['data']->get_attribute( 'pa_succursale' ) ) {
				$noshipping8 = $item['data']->get_attribute( 'pa_no-shipping' );
				if ( $noshipping8 == 1 ) {
					$NSfound_item8                           = true;
					$NSnew_package8['contents'][ $item_key ]  = $item;
					$NSnew_package8['contents_cost']         += $item['line_total'];
				} else {
					$found_item8                           = true;
					$new_package8['contents'][ $item_key ]  = $item;
					$new_package8['contents_cost']         += $item['line_total'];
				}
				// Remove from original package
				$packages[0]['contents_cost'] = $packages[0]['contents_cost'] - $item['line_total'];
				unset( $packages[0]['contents'][ $item_key ] );

				// If there are no items left in the previous package, remove it completely.
				if ( empty( $packages[0]['contents'] ) ) {
					unset( $packages[0] );
				}
			} else if ( $premiereavenue === $item['data']->get_attribute( 'pa_succursale' ) ) {
				$noshipping9 = $item['data']->get_attribute( 'pa_no-shipping' );
				if ( $noshipping9 == 1 ) {
					$NSfound_item9                           = true;
					$NSnew_package9['contents'][ $item_key ]  = $item;
					$NSnew_package9['contents_cost']         += $item['line_total'];
				} else {
					$found_item9                           = true;
					$new_package9['contents'][ $item_key ]  = $item;
					$new_package9['contents_cost']         += $item['line_total'];
				}
				// Remove from original package
				$packages[0]['contents_cost'] = $packages[0]['contents_cost'] - $item['line_total'];
				unset( $packages[0]['contents'][ $item_key ] );

				// If there are no items left in the previous package, remove it completely.
				if ( empty( $packages[0]['contents'] ) ) {
					unset( $packages[0] );
				}
			} else if ( $limoilou === $item['data']->get_attribute( 'pa_succursale' ) ) {
				$noshipping10 = $item['data']->get_attribute( 'pa_no-shipping' );
				if ( $noshipping10 == 1 ) {
					$NSfound_item10                           = true;
					$NSnew_package10['contents'][ $item_key ]  = $item;
					$NSnew_package10['contents_cost']         += $item['line_total'];
				} else {
					$found_item10                           = true;
					$new_package10['contents'][ $item_key ]  = $item;
					$new_package10['contents_cost']         += $item['line_total'];
				}

				// Remove from original package
				$packages[0]['contents_cost'] = $packages[0]['contents_cost'] - $item['line_total'];
				unset( $packages[0]['contents'][ $item_key ] );

				// If there are no items left in the previous package, remove it completely.
				if ( empty( $packages[0]['contents'] ) ) {
					unset( $packages[0] );
				}
			}

		}

		if ( $found_item ) {
		   $packages[1] = $new_package;
		}
		if ( $found_item2 ) {
		   $packages[2] = $new_package2;
		}
		if ( $found_item3 ) {
		   $packages[3] = $new_package3;
		}
		if ( $found_item4 ) {
		   $packages[4] = $new_package4;
		}
		if ( $found_item5 ) {
		   $packages[5] = $new_package5;
		}
		if ( $found_item6 ) {
		   $packages[6] = $new_package6;
		}
		if ( $found_item7 ) {
		   $packages[7] = $new_package7;
		}
		if ( $found_item8 ) {
		   $packages[8] = $new_package8;
		}
		if ( $found_item9 ) {
		   $packages[9] = $new_package9;
		}
		if ( $found_item10 ) {
		   $packages[10] = $new_package10;
		}


		if ( $NSfound_item ) {
		   $packages[11] = $NSnew_package;
		}
		if ( $NSfound_item2 ) {
		   $packages[12] = $NSnew_package2;
		}
		if ( $NSfound_item3 ) {
		   $packages[13] = $NSnew_package3;
		}
		if ( $NSfound_item4 ) {
		   $packages[14] = $NSnew_package4;
		}
		if ( $NSfound_item5 ) {
		   $packages[15] = $NSnew_package5;
		}
		if ( $NSfound_item6 ) {
		   $packages[16] = $NSnew_package6;
		}
		if ( $NSfound_item7 ) {
		   $packages[17] = $NSnew_package7;
		}
		if ( $NSfound_item8 ) {
		   $packages[18] = $NSnew_package8;
		}
		if ( $NSfound_item9 ) {
		   $packages[19] = $NSnew_package9;
		}
		if ( $NSfound_item10 ) {
		   $packages[20] = $NSnew_package10;
		}


		return $packages;
	}


	/**
	 * Shipping package names
	 */

	add_filter( 'woocommerce_cart_shipping_packages', 'split_item_class_items' );

	function wc_change_pkg_titles( $current_title, $pkg_id, $package ) {

	    // exit if package error
	    if( ! isset( $package['contents'] ) || ! is_array( $package['contents'] ) ) return $current_title;

	    foreach( $package['contents'] as $key => $item ) {
	        $product = $item['data'];
	        $shipping_class_id = $product->get_shipping_class_id();
	        $shipping_class = get_term( $shipping_class_id );
	    }

	    return ( $shipping_class ) ? $shipping_class->name : $current_title;

	}
	add_filter( 'woocommerce_shipping_package_name', 'wc_change_pkg_titles', 10, 3 );

?>