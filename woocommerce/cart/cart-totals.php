<?php
/**
 * Panier du Chalet : « L'addition ».
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package chalet-child
 * @version 2.3.6
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="cart_totals addition">
	<?php do_action( 'woocommerce_before_cart_totals' ); ?>
	<div class="addition__fond">
		<h2 class="addition__titre">L’addition</h2>
		<p class="addition__ligne">
			<span class="addition__libelle">Sous-total</span>
			<span class="addition__points" aria-hidden="true"></span>
			<span class="addition__montant"><?php wc_cart_totals_subtotal_html(); ?></span>
		</p>
		<?php
		// IT-10 : ligne « Code … · Retirer » par code appliqué, sinon le champ de saisie.
		foreach ( WC()->cart->get_coupons() as $code => $coupon ) {
			chalet_ligne_code_promo( $code );
		}
		if ( wc_coupons_enabled() && ! WC()->cart->get_coupons() ) {
			chalet_champ_code_promo();
		}
		?>
		<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
			<?php do_action( 'woocommerce_cart_totals_before_shipping' ); ?>
			<?php wc_cart_totals_shipping_html(); ?>
			<?php do_action( 'woocommerce_cart_totals_after_shipping' ); ?>
		<?php endif; ?>
		<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
			<p class="addition__ligne">
				<span class="addition__libelle"><?php echo esc_html( $fee->name ); ?></span>
				<span class="addition__points" aria-hidden="true"></span>
				<span class="addition__montant"><?php wc_cart_totals_fee_html( $fee ); ?></span>
			</p>
		<?php endforeach; ?>
		<?php do_action( 'woocommerce_cart_totals_before_order_total' ); ?>
		<p class="addition__ligne addition__ligne--total">
			<span class="addition__libelle">Total</span>
			<span class="addition__points" aria-hidden="true"></span>
			<span class="addition__montant"><?php wc_cart_totals_order_total_html(); ?></span>
		</p>
		<?php do_action( 'woocommerce_cart_totals_after_order_total' ); ?>
		<div class="wc-proceed-to-checkout addition__action">
			<a class="checkout-button button alt wc-forward" href="<?php echo esc_url( wc_get_checkout_url() ); ?>">Commander</a>
		</div>
	</div>
	<?php do_action( 'woocommerce_after_cart_totals' ); ?>
</div>
