<?php
/**
 * Panier et Commande du Chalet : choix du mode de retrait.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package chalet-child
 * @version 11.2.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_cart() && ! is_checkout() ) {
	include WC()->plugin_path() . '/templates/cart/cart-shipping.php';
	return;
}

/* Page Commande : le mode est choisi dans le panier, il n'est que rappelé ici (ligne de ticket + lien Modifier). */
if ( is_checkout() && ! empty( $available_methods ) && is_array( $available_methods ) ) :
	$methode = isset( $available_methods[ $chosen_method ] ) ? $available_methods[ $chosen_method ] : reset( $available_methods );
	$cout    = (float) $methode->get_cost();
	?>
	<p class="woocommerce-shipping-totals shipping addition__ligne addition__ligne--plat addition__ligne--mode">
		<input type="hidden" class="shipping_method" name="shipping_method[<?php echo absint( $index ); ?>]" data-index="<?php echo absint( $index ); ?>" value="<?php echo esc_attr( $methode->id ); ?>">
		<span class="addition__libelle"><?php echo esc_html( $methode->get_label() ); ?></span>
		<a class="addition__modifier" href="<?php echo esc_url( wc_get_cart_url() ); ?>">Modifier<span class="screen-reader-text"> le mode de retrait</span></a>
		<span class="addition__points" aria-hidden="true"></span>
		<span class="addition__montant"><?php echo $cout > 0 ? wp_kses_post( wc_price( $cout ) ) : 'Gratuit'; ?></span>
	</p>
	<?php
	return;
endif;
?>
<div class="woocommerce-shipping-totals shipping modes">
	<p class="modes__question">Comment récupérer votre commande ?</p>
	<?php if ( ! empty( $available_methods ) && is_array( $available_methods ) ) : ?>
		<ul id="shipping_method" class="woocommerce-shipping-methods modes__liste">
			<?php foreach ( $available_methods as $method ) : ?>
				<?php
				$identifiant = 'shipping_method_' . absint( $index ) . '_' . sanitize_title( $method->id );
				$cout        = (float) $method->get_cost();
				?>
				<li class="modes__item">
					<label class="modes__option" for="<?php echo esc_attr( $identifiant ); ?>">
						<?php if ( 1 < count( $available_methods ) ) : ?>
							<input class="shipping_method modes__radio" type="radio" name="shipping_method[<?php echo absint( $index ); ?>]" data-index="<?php echo absint( $index ); ?>" id="<?php echo esc_attr( $identifiant ); ?>" value="<?php echo esc_attr( $method->id ); ?>" <?php checked( $method->id, $chosen_method ); ?>>
						<?php else : ?>
							<input class="shipping_method" type="hidden" name="shipping_method[<?php echo absint( $index ); ?>]" data-index="<?php echo absint( $index ); ?>" id="<?php echo esc_attr( $identifiant ); ?>" value="<?php echo esc_attr( $method->id ); ?>">
						<?php endif; ?>
						<span class="modes__nom"><?php echo esc_html( $method->get_label() ); ?></span>
						<span class="modes__prix"><?php echo $cout > 0 ? wp_kses_post( wc_price( $cout ) ) : 'Gratuit'; ?></span>
						<?php if ( 'local_pickup' === $method->get_method_id() ) : ?>
							<span class="modes__info"><?php echo esc_html( chalet_info_retrait() ); ?></span>
						<?php elseif ( 'flat_rate' === $method->get_method_id() ) : // IT-19 : tarif selon le poids. ?>
							<span class="modes__info"><?php echo esc_html( chalet_info_poids() ); ?></span>
						<?php endif; ?>
					</label>
					<?php do_action( 'woocommerce_after_shipping_rate', $method, $index ); ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php else : ?>
		<p class="modes__info"><?php esc_html_e( 'Shipping costs are calculated during checkout.', 'woocommerce' ); ?></p>
	<?php endif; ?>
</div>
