<?php
/**
 * Page Commande du Chalet : lignes de « L'addition ».
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package chalet-child
 * @version 11.2.0
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="woocommerce-checkout-review-order-table addition__detail">
	<?php
	foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) :
		$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
		if ( ! $_product instanceof WC_Product || ! $_product->exists() || $cart_item['quantity'] <= 0 || ! apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
			continue;
		}
		$options = chalet_options_plat_panier( $cart_item );
		?>
		<p class="addition__ligne addition__ligne--plat">
			<span class="addition__libelle"><?php echo esc_html( chalet_nom_plat_panier( $_product ) ); ?> ×<?php echo absint( $cart_item['quantity'] ); ?></span>
			<span class="addition__points" aria-hidden="true"></span>
			<span class="addition__montant"><?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
		</p>
		<?php if ( $options ) : ?>
			<p class="addition__options"><?php echo esc_html( $options ); ?></p>
		<?php endif; ?>
	<?php endforeach; ?>
	<p class="addition__ligne">
		<span class="addition__libelle">Sous-total</span>
		<span class="addition__points" aria-hidden="true"></span>
		<span class="addition__montant"><?php wc_cart_totals_subtotal_html(); ?></span>
	</p>
	<?php
	foreach ( WC()->cart->get_coupons() as $code => $coupon ) {
		chalet_ligne_code_promo( $code ); // IT-10 : même ligne que dans le panier.
	}
	?>
	<?php foreach ( WC()->cart->get_fees() as $fee ) : // IT-15 : frais de préparation, avant le mode de retrait. ?>
		<p class="addition__ligne addition__ligne--plat">
			<span class="addition__libelle"><?php echo esc_html( $fee->name ); ?></span>
			<span class="addition__points" aria-hidden="true"></span>
			<span class="addition__montant"><?php wc_cart_totals_fee_html( $fee ); ?></span>
		</p>
	<?php endforeach; ?>
	<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
		<?php do_action( 'woocommerce_review_order_before_shipping' ); ?>
		<?php wc_cart_totals_shipping_html(); ?>
		<?php do_action( 'woocommerce_review_order_after_shipping' ); ?>
	<?php endif; ?>
	<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>
	<p class="addition__ligne addition__ligne--total">
		<span class="addition__libelle">Total</span>
		<span class="addition__points" aria-hidden="true"></span>
		<span class="addition__montant"><?php wc_cart_totals_order_total_html(); ?></span>
	</p>
	<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>
</div>
