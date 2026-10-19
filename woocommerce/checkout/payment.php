<?php
/**
 * Page Commande du Chalet : choix du paiement et bouton Commander.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package chalet-child
 * @version 10.9.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! wp_doing_ajax() ) {
	do_action( 'woocommerce_review_order_before_payment' );
}
?>
<div id="payment" class="woocommerce-checkout-payment paiement">
	<?php if ( WC()->cart && WC()->cart->needs_payment() ) : ?>
		<p class="paiement__question" id="paiement-question">Comment souhaitez-vous régler ?</p>
		<ul class="wc_payment_methods paiement__liste" aria-labelledby="paiement-question">
			<?php if ( ! empty( $available_gateways ) ) : ?>
				<?php foreach ( $available_gateways as $gateway ) : ?>
					<li class="wc_payment_method payment_method_<?php echo esc_attr( $gateway->id ); ?> paiement__option">
						<label class="paiement__choix" for="payment_method_<?php echo esc_attr( $gateway->id ); ?>">
							<input id="payment_method_<?php echo esc_attr( $gateway->id ); ?>" type="radio" class="input-radio paiement__radio" name="payment_method" value="<?php echo esc_attr( $gateway->id ); ?>" <?php checked( $gateway->chosen, true ); ?> data-order_button_text="<?php echo esc_attr( $gateway->order_button_text ); ?>">
							<span class="paiement__nom"><?php echo wp_kses_post( $gateway->get_title() ); ?></span>
							<?php if ( 'cod' === $gateway->id ) : ?>
								<span class="paiement__lieu">Sur place</span>
							<?php endif; ?>
						</label>
						<?php if ( $gateway->has_fields() || $gateway->get_description() ) : ?>
							<div class="payment_box payment_method_<?php echo esc_attr( $gateway->id ); ?> paiement__info" <?php echo $gateway->chosen ? '' : 'style="display:none;"'; ?>>
								<?php $gateway->payment_fields(); ?>
							</div>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			<?php else : ?>
				<li><?php wc_print_notice( apply_filters( 'woocommerce_no_available_payment_methods_message', esc_html__( 'Sorry, it seems that there are no available payment methods. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce' ) ), 'notice' ); ?></li>
			<?php endif; ?>
		</ul>
	<?php endif; ?>
	<div class="place-order addition__action">
		<noscript>
			<button type="submit" class="button" name="woocommerce_checkout_update_totals" value="<?php esc_attr_e( 'Update totals', 'woocommerce' ); ?>"><?php esc_html_e( 'Update totals', 'woocommerce' ); ?></button>
		</noscript>
		<?php wc_get_template( 'checkout/terms.php' ); ?>
		<?php do_action( 'woocommerce_review_order_before_submit' ); ?>
		<?php echo apply_filters( 'woocommerce_order_button_html', '<button type="submit" class="button alt addition__commander" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '">' . esc_html( $order_button_text ) . '</button>' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php do_action( 'woocommerce_review_order_after_submit' ); ?>
		<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
	</div>
</div>
<?php
if ( ! wp_doing_ajax() ) {
	do_action( 'woocommerce_review_order_after_payment' );
}
