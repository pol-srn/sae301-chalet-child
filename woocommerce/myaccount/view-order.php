<?php
/**
 * Mon compte du Chalet : détail d'une commande (frise de suivi, addition, annulation, messages).
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package chalet-child
 * @version 10.6.0
 */

defined( 'ABSPATH' ) || exit;

$notes     = $order->get_customer_order_notes();
$statut    = $order->get_status();
$suivi     = chalet_suivi_commande( $order );
$annulable = in_array( $statut, apply_filters( 'woocommerce_valid_order_statuses_for_cancel', array( 'pending', 'failed' ), $order ), true ); // phpcs:ignore WooCommerce.Commenting.CommentHooks -- Hook de WooCommerce.
$infos     = array_filter( array( $order->get_shipping_method(), $order->get_payment_method_title() ) );
?>
<div class="detail-commande">
	<div class="tableau__tete">
		<h2 class="tableau__titre">Commande n° <?php echo esc_html( $order->get_order_number() ); ?></h2>
		<p class="tableau__intro">
			Passée le <?php echo esc_html( wc_format_datetime( $order->get_date_created(), 'd/m/Y' ) ); ?><?php echo $infos ? ' · ' . esc_html( implode( ' · ', $infos ) ) : ''; ?>
			<span class="badge <?php echo esc_attr( chalet_classe_badge( $statut ) ); ?>"><?php echo esc_html( wc_get_order_status_name( $statut ) ); ?></span>
		</p>
	</div>

	<section class="suivi-commande" aria-labelledby="suivi-commande-titre">
		<h3 class="suivi-commande__titre" id="suivi-commande-titre">Suivi de votre commande</h3>
		<?php if ( $suivi['etapes'] ) : ?>
			<ol class="frise">
				<?php foreach ( $suivi['etapes'] as $etape ) : ?>
					<li class="frise__etape frise__etape--<?php echo esc_attr( $etape[2] ); ?>"<?php echo 'en-cours' === $etape[2] ? ' aria-current="step"' : ''; ?>>
						<strong class="frise__nom"><?php echo esc_html( $etape[0] ); ?></strong>
						<span class="frise__texte"><?php echo esc_html( $etape[1] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ol>
		<?php endif; ?>
		<p class="suivi-commande__message"><?php echo esc_html( $suivi['message'] ); ?></p>
	</section>

	<div class="detail-commande__bas">
		<?php chalet_addition_commande( $order ); ?>

		<aside class="imprevu">
			<?php if ( $annulable ) : ?>
				<h3 class="imprevu__titre">Un imprévu ?</h3>
				<p class="imprevu__texte">Vous pouvez annuler tant que la commande n’est pas prête. Si vous l’avez réglée par virement, nous vous remboursons et vous recevez une confirmation par e-mail.</p>
				<a class="button compte__bouton compte__bouton--secondaire" href="<?php echo esc_url( $order->get_cancel_order_url( wc_get_account_endpoint_url( 'orders' ) ) ); ?>">Annuler la commande</a>
			<?php else : ?>
				<h3 class="imprevu__titre">Une question ?</h3>
				<p class="imprevu__texte">Appelez-nous au <a href="tel:+33555775181">05 55 77 51 81</a>, du mardi au samedi aux heures d’ouverture.</p>
			<?php endif; ?>
		</aside>
	</div>

	<?php if ( $notes ) : ?>
		<section class="messages-commande" aria-labelledby="messages-commande-titre">
			<h3 class="suivi-commande__titre" id="messages-commande-titre">Messages du Chalet</h3>
			<ol class="messages-commande__liste">
				<?php foreach ( $notes as $note ) : ?>
					<li>
						<p class="messages-commande__date"><?php echo esc_html( date_i18n( 'd/m/Y · H \h i', strtotime( $note->comment_date ) ) ); ?></p>
						<?php echo wp_kses_post( wpautop( wptexturize( $note->comment_content ) ) ); ?>
					</li>
				<?php endforeach; ?>
			</ol>
		</section>
	<?php endif; ?>
</div>
<?php do_action( 'woocommerce_view_order', $order_id ); ?>
