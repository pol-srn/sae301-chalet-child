<?php
/**
 * Page La Carte (boutique) du thème enfant Le Chalet.
 *
 * Surcharge de woocommerce/templates/archive-product.php :
 * sur la boutique, les produits sont regroupés par catégorie (voir chalet_carte() dans functions.php) ;
 * sur une page de catégorie, la grille WooCommerce standard est conservée.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package chalet-child
 * @version 8.6.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

do_action( 'woocommerce_before_main_content' );

if ( is_shop() ) {
	chalet_carte();
} elseif ( woocommerce_product_loop() ) {
	do_action( 'woocommerce_before_shop_loop' );
	woocommerce_product_loop_start();
	if ( wc_get_loop_prop( 'total' ) ) {
		while ( have_posts() ) {
			the_post();
			do_action( 'woocommerce_shop_loop' );
			wc_get_template_part( 'content', 'product' );
		}
	}
	woocommerce_product_loop_end();
	do_action( 'woocommerce_after_shop_loop' );
} else {
	do_action( 'woocommerce_no_products_found' );
}

do_action( 'woocommerce_after_main_content' );

get_footer( 'shop' );
