<?php
/**
 * E-mail du Chalet (texte brut) : commande prête à retirer.
 *
 * @package chalet-child
 */

defined( 'ABSPATH' ) || exit;

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
echo esc_html( wp_strip_all_tags( $email_heading ) );
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo 'Bonjour ' . esc_html( $order->get_billing_first_name() ) . ",\n\n";
echo $order->has_shipping_method( 'local_pickup' ) ? "Votre commande vous attend au comptoir du Chalet.\n" : "Votre commande est prête et part en livraison.\n";
echo 'Votre numéro de commande : ' . esc_html( $order->get_order_number() ) . "\n";
if ( 'cod' === $order->get_payment_method() ) {
	echo 'À régler sur place : ' . esc_html( wp_strip_all_tags( $order->get_formatted_order_total() ) ) . "\n";
}
echo esc_html( chalet_adresse_retrait() . ' · ' . chalet_horaires_retrait() ) . "\n\n";

do_action( 'woocommerce_email_order_details', $order, false, true, $email ); // phpcs:ignore WooCommerce.Commenting.CommentHooks
echo "\n----------------------------------------\n\n";
do_action( 'woocommerce_email_customer_details', $order, false, true, $email ); // phpcs:ignore WooCommerce.Commenting.CommentHooks

if ( $additional_content ) {
	echo esc_html( wp_strip_all_tags( wptexturize( $additional_content ) ) ) . "\n\n";
}
echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) ); // phpcs:ignore WooCommerce.Commenting.CommentHooks
