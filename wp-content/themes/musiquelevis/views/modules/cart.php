<div id="site-header-cart">
	<a class="cart-content" href="<?php echo esc_url( wc_get_cart_url() ); ?>" title="<?php _e( 'Voir voir panier', 'bv' ); ?>">
		<span class="cart-loading" style="display: none;"><?php get_template_part( 'views/modules/loading' ); ?></span>
		<span class="cart-result">
			<span class="amount"><?php echo WC()->cart->get_cart_subtotal(); ?></span>
			<span class="count">(<?php echo wp_kses_data( sprintf( _n( '%d item', '%d items', WC()->cart->get_cart_contents_count(), 'bv' ), WC()->cart->get_cart_contents_count() ) );?>)</span>
		</span>

		<span class="cart-btn"><?php _e('Panier', 'bv'); ?></span>
		<span class="cart-icon">
			<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
				 viewBox="0 0 32 25" style="enable-background:new 0 0 32 25;" xml:space="preserve">
				<path d="M25,16.3H6.5c-0.3,0-0.6-0.2-0.8-0.4L0.2,7.8C0,7.5-0.1,7.1,0.1,6.7S0.6,6.2,1,6.2h24c0.6,0,1,0.4,1,1v8.1
					C26,15.8,25.6,16.3,25,16.3z M7.1,14.3H24V8.2H2.9L7.1,14.3z"/>
				<path d="M23.8,20.5H9c-0.6,0-1-0.4-1-1s0.4-1,1-1h13.8l0-3c0-0.6,0.4-1,1-1c0,0,0,0,0,0c0.6,0,1,0.4,1,1l0,4c0,0.3-0.1,0.5-0.3,0.7
					C24.3,20.4,24.1,20.5,23.8,20.5z"/>
				<path d="M25,8c-0.6,0-1-0.4-1-1V2.2c0-0.5,0.3-0.9,0.8-1l6-1.2c0.5-0.1,1.1,0.2,1.2,0.8c0.1,0.5-0.2,1.1-0.8,1.2L26,3V7
					C26,7.6,25.6,8,25,8z"/>
				<path d="M8.9,25c-1.8,0-3.2-1.5-3.2-3.3s1.4-3.3,3.2-3.3c1.8,0,3.2,1.5,3.2,3.3S10.6,25,8.9,25z M8.9,20.5c-0.7,0-1.2,0.6-1.2,1.3
					S8.2,23,8.9,23c0.7,0,1.2-0.6,1.2-1.3S9.5,20.5,8.9,20.5z"/>
				<path d="M23,25c-1.8,0-3.2-1.5-3.2-3.3s1.4-3.3,3.2-3.3c1.8,0,3.2,1.5,3.2,3.3S24.7,25,23,25z M23,20.5c-0.7,0-1.2,0.6-1.2,1.3
					S22.3,23,23,23c0.7,0,1.2-0.6,1.2-1.3S23.6,20.5,23,20.5z"/>
			</svg>
		</span>
	</a>
	<div class="cart-dropdown">
		<?php dynamic_sidebar( 'headercart' ); ?>
	</div>
</div>