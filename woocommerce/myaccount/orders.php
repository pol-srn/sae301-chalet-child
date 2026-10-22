<?php
/**
 * Mon compte du Chalet : liste des commandes en cartes (n°, date, mode, total, statut, actions).
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package chalet-child
 * @version 9.5.0
 */

defined( 'ABSPATH' ) || exit;

$wp_button_class = $wp_button_class ?? '';

do_action( 'woocommerce_before_account_orders', $has_orders );
?>
<div class="tableau__tete">
	<h2 class="tableau__titre">Mes commandes</h2>
	<p class="tableau__intro">Retrouvez vos commandes et suivez leur préparation.</p>
</div>

<?php if ( $has_orders ) : ?>
	<ul class="commandes">
		<?php foreach ( $customer_orders->orders as $customer_order ) : ?>
			<?php
			$order   = wc_get_order( $customer_order ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$statut  = $order->get_status();
			$actions = wc_get_account_orders_actions( $order );
			?>
			<li class="commande-carte commande-carte--<?php echo esc_attr( $statut ); ?>">
				<dl class="commande-carte__infos">
					<div>
						<dt>Commande</dt>
						<dd>n° <?php echo esc_html( $order->get_order_number() ); ?></dd>
					</div>
					<div>
						<dt>Date</dt>
						<dd><time datetime="<?php echo esc_attr( $order->get_date_created()->date( 'c' ) ); ?>"><?php echo esc_html( wc_format_datetime( $order->get_date_created(), 'd/m/Y' ) ); ?></time></dd>
					</div>
					<div>
						<dt>Mode</dt>
						<dd class="commande-carte__mode"><?php echo esc_html( $order->get_shipping_method() ? $order->get_shipping_method() : '—' ); ?></dd>
					</div>
					<div>
						<dt>Total</dt>
						<dd><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></dd>
					</div>
				</dl>
				<p class="commande-carte__fin">
					<span class="badge <?php echo esc_attr( chalet_classe_badge( $statut ) ); ?>"><?php echo esc_html( wc_get_order_status_name( $statut ) ); ?></span>
					<?php foreach ( $actions as $cle => $action ) : ?>
						<a class="commande-carte__action commande-carte__action--<?php echo esc_attr( $cle ); ?>" href="<?php echo esc_url( $action['url'] ); ?>"<?php echo 'invoice' === $cle ? ' target="_blank" rel="noopener"' : ''; // IT-17 : PDF dans un nouvel onglet. ?> aria-label="<?php echo esc_attr( ( 'invoice' === $cle ? 'Télécharger la facture de' : $action['name'] ) . ' la commande n° ' . $order->get_order_number() ); ?>"><?php echo esc_html( $action['name'] ); ?></a>
					<?php endforeach; ?>
				</p>
			</li>
		<?php endforeach; ?>
	</ul>

	<?php do_action( 'woocommerce_before_account_orders_pagination' ); ?>

	<?php if ( 1 < $customer_orders->max_num_pages ) : ?>
		<div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
			<?php if ( 1 !== $current_page ) : ?>
				<a class="woocommerce-button woocommerce-button--previous button<?php echo esc_attr( $wp_button_class ); ?>" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page - 1 ) ); ?>">Précédentes</a>
			<?php endif; ?>
			<?php if ( intval( $customer_orders->max_num_pages ) !== $current_page ) : ?>
				<a class="woocommerce-button woocommerce-button--next button<?php echo esc_attr( $wp_button_class ); ?>" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page + 1 ) ); ?>">Suivantes</a>
			<?php endif; ?>
		</div>
	<?php endif; ?>
<?php else : ?>
	<section class="derniere">
		<h3 class="derniere__titre">Pas encore de commande</h3>
		<p class="derniere__mode">Composez votre premier repas, à retirer au Chalet ou livré à Limoges.</p>
		<p class="derniere__action">
			<a class="button compte__bouton" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">Voir la carte</a>
		</p>
	</section>
<?php endif; ?>

<?php do_action( 'woocommerce_after_account_orders', $has_orders ); ?>
