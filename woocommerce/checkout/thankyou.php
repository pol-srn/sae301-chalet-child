<?php
/**
 * Page Confirmation du Chalet : message, récapitulatif, retrait et addition.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package chalet-child
 * @version 8.1.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! $order ) {
	wc_get_template( 'checkout/order-received.php', array( 'order' => false ) );
	return;
}

if ( $order->has_status( 'failed' ) ) :
	?>
	<div class="confirmation">
		<p class="woocommerce-error">Votre paiement n’a pas abouti. Vous pouvez réessayer ou nous appeler au 05 55 77 51 81.</p>
		<p class="confirmation__actions">
			<a class="button" href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>">Réessayer</a>
		</p>
	</div>
	<?php
	return;
endif;

$retrait  = $order->has_shipping_method( 'local_pickup' );
$paiement = 'cod' === $order->get_payment_method() ? 'Au retrait' : $order->get_payment_method_title();
// Étapes réglées dans WooCommerce → Click & Collect ; la 3e change pour une livraison.
$etapes = array_values( chalet_etapes_retrait() );
if ( ! $retrait && isset( $etapes[2] ) ) {
	$etapes[2] = array( 'titre' => 'Nous livrons', 'texte' => 'À l’adresse indiquée dans votre bon de commande.' );
}
$etats = array( 'fait', 'en-cours', 'a-venir' );
?>
<div class="confirmation woocommerce-order">
	<?php do_action( 'woocommerce_before_thankyou', $order->get_id() ); ?>

	<p class="woocommerce-message confirmation__message woocommerce-thankyou-order-received">
		Votre commande n° <?php echo esc_html( $order->get_order_number() ); ?> est confirmée. Un e-mail de confirmation vous a été envoyé.
	</p>

	<ul class="recap woocommerce-order-overview">
		<li class="recap__carte">
			<span class="recap__libelle">Commande</span>
			<strong class="recap__valeur">n° <?php echo esc_html( $order->get_order_number() ); ?></strong>
		</li>
		<li class="recap__carte">
			<span class="recap__libelle">Date</span>
			<strong class="recap__valeur"><?php echo esc_html( wc_format_datetime( $order->get_date_created(), 'd/m/Y' ) ); ?></strong>
		</li>
		<li class="recap__carte">
			<span class="recap__libelle">Total</span>
			<strong class="recap__valeur"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></strong>
		</li>
		<li class="recap__carte">
			<span class="recap__libelle">Paiement</span>
			<strong class="recap__valeur"><?php echo esc_html( $paiement ); ?></strong>
		</li>
	</ul>

	<?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>

	<div class="confirmation__colonnes">
		<section class="suivi" aria-labelledby="suivi-titre">
			<p class="suivi__libelle"><?php echo $retrait ? 'Retrait au restaurant' : 'Livraison à Limoges'; ?></p>
			<h2 class="suivi__titre" id="suivi-titre">Votre commande est en cuisine</h2>
			<p class="suivi__adresse">
				<?php if ( $retrait ) : ?>
					<?php echo esc_html( chalet_adresse_retrait() ); ?>
				<?php else : ?>
					<?php echo esc_html( trim( $order->get_billing_address_1() . ', ' . $order->get_billing_postcode() . ' ' . $order->get_billing_city(), ', ' ) ); ?>
				<?php endif; ?>
				<br><?php echo esc_html( ucfirst( chalet_horaires_retrait() ) ); ?>
			</p>
			<ol class="suivi__etapes">
				<?php foreach ( $etapes as $i => $etape ) : ?>
					<li class="suivi__etape suivi__etape--<?php echo esc_attr( $etats[ $i ] ?? 'a-venir' ); ?>">
						<strong><?php echo esc_html( $etape['titre'] ); ?></strong>
						<?php echo esc_html( $etape['texte'] ); ?>
					</li>
				<?php endforeach; ?>
			</ol>
		</section>

		<div class="addition">
			<div class="addition__fond">
				<h2 class="addition__titre">L’addition</h2>
				<div class="addition__detail">
					<?php foreach ( $order->get_items() as $item ) : ?>
						<p class="addition__ligne addition__ligne--plat">
							<span class="addition__libelle"><?php echo esc_html( chalet_nom_plat_commande( $item ) ); ?> ×<?php echo absint( $item->get_quantity() ); ?></span>
							<span class="addition__points" aria-hidden="true"></span>
							<span class="addition__montant"><?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?></span>
						</p>
						<?php $options = chalet_options_plat_commande( $item ); ?>
						<?php if ( $options ) : ?>
							<p class="addition__options"><?php echo esc_html( $options ); ?></p>
						<?php endif; ?>
					<?php endforeach; ?>
					<p class="addition__ligne">
						<span class="addition__libelle">Sous-total</span>
						<span class="addition__points" aria-hidden="true"></span>
						<span class="addition__montant"><?php echo wp_kses_post( wc_price( $order->get_subtotal(), array( 'currency' => $order->get_currency() ) ) ); ?></span>
					</p>
					<?php if ( $order->get_total_discount() > 0 ) : ?>
						<p class="addition__ligne addition__ligne--plat">
							<span class="addition__libelle">Réduction</span>
							<span class="addition__points" aria-hidden="true"></span>
							<span class="addition__montant">−<?php echo wp_kses_post( wc_price( $order->get_total_discount(), array( 'currency' => $order->get_currency() ) ) ); ?></span>
						</p>
					<?php endif; ?>
					<?php if ( $order->get_shipping_method() ) : ?>
						<p class="addition__ligne addition__ligne--plat">
							<span class="addition__libelle"><?php echo esc_html( $order->get_shipping_method() ); ?></span>
							<span class="addition__points" aria-hidden="true"></span>
							<span class="addition__montant"><?php echo (float) $order->get_shipping_total() > 0 ? wp_kses_post( wc_price( $order->get_shipping_total(), array( 'currency' => $order->get_currency() ) ) ) : 'Gratuit'; ?></span>
						</p>
					<?php endif; ?>
					<p class="addition__ligne addition__ligne--total">
						<span class="addition__libelle">Total</span>
						<span class="addition__points" aria-hidden="true"></span>
						<span class="addition__montant"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></span>
					</p>
				</div>
			</div>
		</div>
	</div>

	<p class="confirmation__actions">
		<a class="button confirmation__bouton confirmation__bouton--secondaire" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">Retour à la carte</a>
		<a class="button alt confirmation__bouton" href="<?php echo esc_url( is_user_logged_in() ? $order->get_view_order_url() : wc_get_page_permalink( 'myaccount' ) ); ?>">Suivre ma commande</a>
	</p>

	<?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>
</div>
