<?php global $woocommerce;
if ( $woocommerce->cart->cart_contents_count > 0 ) : ?>

    <?php $minicartproduct = ($woocommerce->cart->cart_contents_count > 1 ? __(' items') : __(' item') ); ?>
    <span class="amount"><?php echo  $woocommerce->cart->get_cart_total(); ?></span>
    <span class="count"><?php echo '(' . $woocommerce->cart->cart_contents_count . $minicartproduct . ')'; ?></span>

<?php else : ?>
	<!-- Cart is empty -->
<?php endif; ?>