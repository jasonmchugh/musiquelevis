<?php /* Template Name: Nombre */  /*get_header();*/
	$args = array(
		'order'          => 'asc',
		'orderby'        => 'title',
		'post_status'    => 'publish',
		'post_type'      => 'product',
		'posts_per_page' => -1
	);

	$loop = new WP_Query( $args );
	
	$products = array();
	
	// echo '<style> table.nb-img tr { border: 1px solid #ccc; } table.nb-img td { padding: 3px 5px; }</style>';
	if ( $loop->have_posts() ) :
		// $output = sprintf( '<table class="nb-img"><thead><tr><td>%s</td><td>%s</td></tr></thead><tbody>',
			// 'SKU',
			// 'Nombre d\'images'
		// );

		while( $loop->have_posts() ) : $loop->the_post();

			$product_id = get_the_ID();
			$prod 		= new WC_Product( $product_id );

			$sku     = $prod->get_sku();
			$media   = $prod->get_gallery_image_ids();
			$media[] = get_post_thumbnail_id( $product_id );
			$total   = count( array_unique( $media ) );

			// $output .= sprintf( '<tr><td>%s</td><td>%s</td></tr>',
				// $sku,
				// $total
			// );
			$products[] = array('sku'=>$sku,'total'=>$total);

		endwhile;

		// $output .= '</tbody></table>';
	endif;
	wp_reset_postdata();

	//echo $output;
	header('content-type: application/json');
	$json = json_encode($products);
	echo $json;
	exit;
?>