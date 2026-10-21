<?php
/**
 * Page d'une envie (taxonomie « envie » créée avec SCF, US-12.1 à US-12.3).
 * Hiérarchie des templates : taxonomy-{taxonomie}.php est choisi avant archive-product.php.
 *
 * @package chalet-child
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

do_action( 'woocommerce_before_main_content' );

$envie = get_queried_object();
?>
<section class="envie-intro" aria-labelledby="envie-intro-titre">
	<h2 class="envie-intro__surtitre" id="envie-intro-titre">Une envie du Chalet</h2>
	<?php if ( $envie->description ) : ?>
		<p class="envie-intro__texte"><?php echo esc_html( $envie->description ); ?></p>
	<?php endif; ?>

	<?php chalet_cartes_envies( $envie->term_id ); ?>
</section>

<section class="envie-plats" aria-label="<?php echo esc_attr( 'Les plats « ' . $envie->name . ' »' ); ?>">
	<?php if ( woocommerce_product_loop() ) : ?>
		<div class="woocommerce">
			<?php
			woocommerce_product_loop_start();
			while ( have_posts() ) {
				the_post();
				wc_get_template_part( 'content', 'product' );
			}
			woocommerce_product_loop_end();
			?>
		</div>
	<?php else : ?>
		<p class="envie-plats__vide">Aucun plat dans cette envie pour le moment.</p>
	<?php endif; ?>
	<a class="envie-plats__retour" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">Voir toute la carte</a>
</section>
<?php
do_action( 'woocommerce_after_main_content' );

get_footer( 'shop' );
