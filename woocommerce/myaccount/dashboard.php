<?php
/**
 * Mon compte du Chalet : tableau de bord (Bonjour, dernière commande, raccourcis).
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package chalet-child
 * @version 4.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$prenom     = $current_user->first_name ? $current_user->first_name : $current_user->display_name;
$commandes  = wc_get_orders(
	array(
		'customer_id' => get_current_user_id(),
		'limit'       => 1,
		'orderby'     => 'date',
		'order'       => 'DESC',
	)
);
$derniere   = $commandes ? $commandes[0] : null;
$raccourcis = array(
	array( 'Mes commandes', 'Historique et suivi', wc_get_account_endpoint_url( 'orders' ) ),
	array( 'Mes adresses', 'Facturation et livraison', wc_get_account_endpoint_url( 'edit-address' ) ),
	array( 'Mes informations', 'Nom, e-mail, mot de passe', wc_get_account_endpoint_url( 'edit-account' ) ),
);
?>
<div class="tableau">
	<div class="tableau__tete">
		<h2 class="tableau__titre">Bonjour <?php echo esc_html( $prenom ); ?></h2>
		<p class="tableau__intro">Depuis votre compte, suivez vos commandes et gérez vos adresses et vos informations.</p>
	</div>

	<section class="derniere" aria-labelledby="derniere-titre">
		<?php if ( $derniere ) : ?>
			<div class="derniere__tete">
				<h3 class="derniere__titre" id="derniere-titre">Dernière commande</h3>
				<span class="badge derniere__statut <?php echo esc_attr( chalet_classe_badge( $derniere->get_status() ) ); ?>"><?php echo esc_html( wc_get_order_status_name( $derniere->get_status() ) ); ?></span>
			</div>
			<dl class="derniere__infos">
				<div>
					<dt>Commande</dt>
					<dd>n° <?php echo esc_html( $derniere->get_order_number() ); ?></dd>
				</div>
				<div>
					<dt>Date</dt>
					<dd><?php echo esc_html( wc_format_datetime( $derniere->get_date_created(), 'd/m/Y' ) ); ?></dd>
				</div>
				<div>
					<dt>Total</dt>
					<dd><?php echo wp_kses_post( $derniere->get_formatted_order_total() ); ?></dd>
				</div>
			</dl>
			<?php if ( $derniere->get_shipping_method() ) : ?>
				<p class="derniere__mode"><?php echo esc_html( $derniere->get_shipping_method() ); ?></p>
			<?php endif; ?>
			<p class="derniere__action">
				<a class="button compte__bouton" href="<?php echo esc_url( $derniere->get_view_order_url() ); ?>">Voir la commande</a>
			</p>
		<?php else : ?>
			<h3 class="derniere__titre" id="derniere-titre">Pas encore de commande</h3>
			<p class="derniere__mode">Composez votre premier repas, à retirer au Chalet ou livré à Limoges.</p>
			<p class="derniere__action">
				<a class="button compte__bouton" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">Voir la carte</a>
			</p>
		<?php endif; ?>
	</section>

	<ul class="raccourcis">
		<?php foreach ( $raccourcis as $raccourci ) : ?>
			<li class="raccourcis__item">
				<a class="raccourcis__lien" href="<?php echo esc_url( $raccourci[2] ); ?>">
					<span class="raccourcis__titre"><?php echo esc_html( $raccourci[0] ); ?></span>
					<span class="raccourcis__texte"><?php echo esc_html( $raccourci[1] ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
<?php
do_action( 'woocommerce_account_dashboard' );
do_action( 'woocommerce_before_my_account' ); // phpcs:ignore WooCommerce.Commenting.CommentHooks
do_action( 'woocommerce_after_my_account' ); // phpcs:ignore WooCommerce.Commenting.CommentHooks
