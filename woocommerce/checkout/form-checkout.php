<?php
/**
 * Page Commande du Chalet : « Bon de commande » + « L'addition ».
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package chalet-child
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_checkout_form', $checkout );

if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}
?>
<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="Commande">
	<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>
	<div class="commande">
		<section class="bon" aria-labelledby="bon-titre">
			<h2 class="bon__titre" id="bon-titre">Bon de commande</h2>
			<p class="bon__sous-titre">À quel nom préparons-nous votre commande ?</p>
			<p class="bon__tampon" aria-hidden="true">Click<br>&amp;<br>Collect</p>
			<div class="bon__champs">
				<?php
				foreach ( $checkout->get_checkout_fields( 'billing' ) as $cle => $champ ) {
					woocommerce_form_field( $cle, $champ, $checkout->get_value( $cle ) );
				}
				if ( apply_filters( 'woocommerce_enable_order_notes_field', 'yes' === get_option( 'woocommerce_enable_order_comments', 'yes' ) ) ) {
					foreach ( $checkout->get_checkout_fields( 'order' ) as $cle => $champ ) {
						woocommerce_form_field( $cle, $champ, $checkout->get_value( $cle ) );
					}
				}
				?>
			</div>
			<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>
		</section>
		<div class="addition">
			<div class="addition__fond">
				<h2 class="addition__titre">L’addition</h2>
				<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>
				<div class="woocommerce-checkout-review-order addition__contenu">
					<?php do_action( 'woocommerce_checkout_order_review' ); ?>
				</div>
				<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
			</div>
		</div>
	</div>
</form>
<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
