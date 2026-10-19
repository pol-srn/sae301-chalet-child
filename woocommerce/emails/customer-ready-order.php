<?php
/**
 * E-mail du Chalet : commande prête à retirer (numéro mis en avant, montant à régler, adresse et horaires).
 *
 * @package chalet-child
 */

defined( 'ABSPATH' ) || exit;

$retrait = $order->has_shipping_method( 'local_pickup' );
$a_payer = 'cod' === $order->get_payment_method();

do_action( 'woocommerce_email_header', $email_heading, $email ); // phpcs:ignore WooCommerce.Commenting.CommentHooks -- Hook de WooCommerce.
?>
<div class="email-introduction">
	<p>Bonjour <?php echo esc_html( $order->get_billing_first_name() ); ?>,</p>
	<p><?php echo $retrait ? 'Votre commande vous attend au comptoir du Chalet.' : 'Votre commande est prête et part en livraison.'; ?></p>
</div>

<table class="retrait-mail" width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation" bgcolor="#8a6544">
	<tr>
		<td>
			<p class="retrait-mail__titre">Votre numéro de commande</p>
			<p class="retrait-mail__numero">n° <?php echo esc_html( $order->get_order_number() ); ?></p>
			<?php if ( $retrait ) : ?>
				<p class="retrait-mail__etape">Présentez ce numéro au comptoir.<?php echo $a_payer ? ' À régler sur place : ' . wp_kses_post( $order->get_formatted_order_total() ) . '.' : ''; ?></p>
			<?php endif; ?>
			<p class="retrait-mail__infos"><?php echo esc_html( chalet_adresse_retrait() . ' · ' . chalet_horaires_retrait() ); ?></p>
		</td>
	</tr>
</table>
<?php
do_action( 'woocommerce_email_order_details', $order, false, false, $email ); // phpcs:ignore WooCommerce.Commenting.CommentHooks
do_action( 'woocommerce_email_order_meta', $order, false, false, $email ); // phpcs:ignore WooCommerce.Commenting.CommentHooks
do_action( 'woocommerce_email_customer_details', $order, false, false, $email ); // phpcs:ignore WooCommerce.Commenting.CommentHooks

if ( $additional_content ) {
	echo '<table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation"><tr><td class="email-additional-content">' . wp_kses_post( wpautop( wptexturize( $additional_content ) ) ) . '</td></tr></table>';
}

do_action( 'woocommerce_email_footer', $email ); // phpcs:ignore WooCommerce.Commenting.CommentHooks
