<?php
/**
 * Panier du Chalet : « Vos plats ».
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package chalet-child
 * @version 11.2.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' );
?>
<div class="panier">
	<form class="woocommerce-cart-form panier__plats" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
		<?php do_action( 'woocommerce_before_cart_table' ); ?>
		<h2 class="panier__titre">Vos plats · <?php echo absint( WC()->cart->get_cart_contents_count() ); ?></h2>
		<ul class="panier__liste">
			<?php
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) :
				$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
				if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 || ! apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
					continue;
				}
				$nom       = apply_filters( 'woocommerce_cart_item_name', chalet_nom_plat_panier( $_product ), $cart_item, $cart_item_key );
				$lien      = $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '';
				$options   = chalet_options_plat_panier( $cart_item );
				$quantite  = woocommerce_quantity_input(
					array(
						'input_name'   => "cart[{$cart_item_key}][qty]",
						'input_value'  => $cart_item['quantity'],
						'max_value'    => $_product->is_sold_individually() ? 1 : $_product->get_max_purchase_quantity(),
						'min_value'    => $_product->is_sold_individually() ? 1 : 0,
						'product_name' => $nom,
					),
					$_product,
					false
				);
				?>
				<li class="plat-panier <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
					<a class="plat-panier__visuel" href="<?php echo esc_url( $lien ); ?>" tabindex="-1" aria-hidden="true">
						<?php echo apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( 'woocommerce_thumbnail' ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</a>
					<div class="plat-panier__infos">
						<p class="plat-panier__ligne">
							<a class="plat-panier__nom" href="<?php echo esc_url( $lien ); ?>"><?php echo esc_html( wp_strip_all_tags( $nom ) ); ?></a>
							<span class="plat-panier__points" aria-hidden="true"></span>
							<span class="plat-panier__total"><?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
						</p>
						<?php if ( $options ) : ?>
							<p class="plat-panier__options"><?php echo esc_html( $options ); ?></p>
						<?php endif; ?>
						<div class="plat-panier__actions">
							<?php echo apply_filters( 'woocommerce_cart_item_quantity', $quantite, $cart_item_key, $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							<span class="plat-panier__prix-unitaire"><?php echo wp_kses_post( WC()->cart->get_product_price( $_product ) ); ?> l’unité</span>
							<a class="plat-panier__retirer" href="<?php echo esc_url( wc_get_cart_remove_url( $cart_item_key ) ); ?>">Retirer<span class="screen-reader-text"> <?php echo esc_html( wp_strip_all_tags( $nom ) ); ?></span></a>
						</div>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
		<button class="panier__maj" type="submit" name="update_cart" value="Mettre à jour le panier">Mettre à jour le panier</button>
		<?php do_action( 'woocommerce_cart_actions' ); ?>
		<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
		<?php do_action( 'woocommerce_after_cart_table' ); ?>
	</form>
	<?php do_action( 'woocommerce_before_cart_collaterals' ); ?>
	<div class="cart-collaterals panier__addition">
		<?php do_action( 'woocommerce_cart_collaterals' ); ?>
	</div>
</div>
<?php do_action( 'woocommerce_after_cart' ); ?>
