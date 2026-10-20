<?php
/**
 * Fiche d'un membre de l'équipe (US-11.1, US-11.3, US-11.5).
 * Hiérarchie des templates : single-{type}.php est choisi avant single.php.
 * Champs SCF : poste, depuis, citation, plat_signature (objet produit).
 *
 * @package chalet-child
 */

get_header();

while ( have_posts() ) :
	the_post();
	$membre_id = get_the_ID();
	$poste     = get_field( 'poste' );
	$depuis    = get_field( 'depuis' );
	$citation  = get_field( 'citation' );
	$plat      = get_field( 'plat_signature' );
	$produit   = $plat ? wc_get_product( $plat->ID ) : null;
	$autres    = get_posts(
		array(
			'post_type'      => 'membre',
			'post__not_in'   => array( $membre_id ),
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'ASC',
			'fields'         => 'ids',
		)
	);
	?>
	<div id="primary" class="content-area">
		<main id="main" class="site-main membre">
			<a class="membre__retour" href="<?php echo esc_url( get_post_type_archive_link( 'membre' ) ); ?>">Retour à l’équipe</a>

			<article class="membre__profil">
				<div class="membre__photo">
					<?php the_post_thumbnail( 'large' ); ?>
				</div>
				<div class="membre__fiche">
					<header class="membre__identite">
						<?php if ( $poste ) : ?>
							<p class="membre__poste"><?php echo esc_html( $poste ); ?></p>
						<?php endif; ?>
						<h1 class="membre__nom"><?php the_title(); ?></h1>
						<?php if ( $depuis ) : ?>
							<p class="membre__depuis">Au Chalet depuis <?php echo absint( $depuis ); ?></p>
						<?php endif; ?>
					</header>

					<?php if ( $citation ) : ?>
						<blockquote class="membre__citation"><p>« <?php echo esc_html( $citation ); ?> »</p></blockquote>
					<?php endif; ?>

					<div class="membre__presentation">
						<?php the_content(); ?>
					</div>

					<?php if ( $produit && $produit->is_visible() ) : ?>
						<section class="membre__plat" aria-labelledby="plat-titre">
							<h2 class="membre__label" id="plat-titre">Son plat signature</h2>
							<div class="plat-signature">
								<?php echo $produit->get_image( 'woocommerce_thumbnail', array( 'class' => 'plat-signature__visuel', 'alt' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
								<div class="plat-signature__infos">
									<p class="plat-signature__nom"><?php echo esc_html( $produit->get_name() ); ?></p>
									<p class="plat-signature__description"><?php echo esc_html( wp_strip_all_tags( $produit->get_short_description() ) ); ?></p>
									<p class="plat-signature__prix"><?php echo wp_kses_post( $produit->get_price_html() ); ?></p>
								</div>
								<a class="plat-signature__lien" href="<?php echo esc_url( $produit->get_permalink() ); ?>">
									Voir le plat<span class="screen-reader-text"> <?php echo esc_html( $produit->get_name() ); ?></span>
								</a>
							</div>
						</section>
					<?php endif; ?>
				</div>
			</article>

			<?php if ( $autres ) : ?>
				<section class="membre__autres" aria-labelledby="autres-titre">
					<h2 class="membre__autres-titre" id="autres-titre">Le reste de la brigade</h2>
					<ul class="equipe__liste">
						<?php
						foreach ( $autres as $autre_id ) {
							chalet_carte_membre( $autre_id, 'h3' );
						}
						?>
					</ul>
				</section>
			<?php endif; ?>
		</main>
	</div>
	<?php
endwhile;

get_footer();
