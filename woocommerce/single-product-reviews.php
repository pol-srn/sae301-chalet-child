<?php
/**
 * Fiche produit du Chalet : contenu de l'onglet « Avis » (US-14.1, US-14.2).
 * Bande caramel « Ce qu'en disent nos clients », avis en cartes, formulaire réservé aux acheteurs.
 * Les id (#reviews, #review_form, #rating) sont gardés : le script de WooCommerce
 * transforme toujours la liste des notes en étoiles cliquables.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package chalet-child
 * @version 11.2.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! comments_open() ) {
	return;
}

$nombre  = $product->get_review_count();
$moyenne = (float) $product->get_average_rating();
?>
<div id="reviews" class="woocommerce-Reviews avis">
	<div id="comments" class="avis__liste-zone">
		<?php chalet_ornement_etoiles(); ?>
		<h2 class="avis__titre">Ce qu’en disent nos clients</h2>

		<?php if ( $nombre && wc_review_ratings_enabled() ) : ?>
			<p class="avis__synthese">
				<?php chalet_etoiles( $moyenne ); ?>
				<strong><?php echo esc_html( number_format_i18n( $moyenne, 1 ) ); ?> / 5</strong>
				<span>· <?php echo esc_html( sprintf( _n( '%s avis d’acheteur certifié', '%s avis d’acheteurs certifiés', $nombre, 'chalet-child' ), number_format_i18n( $nombre ) ) ); ?></span>
			</p>
		<?php endif; ?>
		<span class="avis__zigzag" aria-hidden="true"></span>

		<?php if ( have_comments() ) : ?>
			<ol class="avis__liste">
				<?php wp_list_comments( array( 'callback' => 'chalet_carte_avis' ) ); ?>
			</ol>
			<?php
			if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :
				echo '<nav class="woocommerce-pagination">';
				paginate_comments_links( array( 'type' => 'list' ) );
				echo '</nav>';
			endif;
			?>
		<?php else : ?>
			<p class="avis__vide">Aucun avis pour le moment : soyez le premier à donner le vôtre après votre commande.</p>
		<?php endif; ?>
	</div>

	<div class="avis__formulaire">
		<?php if ( get_option( 'woocommerce_review_rating_verification_required' ) === 'no' || wc_customer_bought_product( '', get_current_user_id(), $product->get_id() ) ) : ?>
			<div id="review_form_wrapper">
				<div id="review_form">
					<?php
					$commenter    = wp_get_current_commenter();
					$comment_form = array(
						'title_reply'         => 'Vous avez goûté ce plat ?',
						'title_reply_to'      => 'Répondre à %s',
						'title_reply_before'  => '<h3 id="reply-title" class="comment-reply-title avis__form-titre">',
						'title_reply_after'   => '</h3>',
						'comment_notes_after' => '<p class="avis__mention">Votre avis sera publié après validation du restaurant.</p>',
						'label_submit'        => 'Publier mon avis',
						'class_submit'        => 'submit avis__bouton',
						'logged_in_as'        => '',
						'comment_field'       => '',
					);

					// Nom et e-mail : demandés seulement aux clients non connectés.
					$requis = (bool) get_option( 'require_name_email', 1 );
					$champs = array(
						'author' => array( 'Votre prénom', 'text', $commenter['comment_author'], 'given-name' ),
						'email'  => array( 'Votre e-mail', 'email', $commenter['comment_author_email'], 'email' ),
					);
					$comment_form['fields'] = array();
					foreach ( $champs as $cle => $champ ) {
						$comment_form['fields'][ $cle ] = sprintf(
							'<p class="comment-form-%1$s"><label for="%1$s">%2$s%3$s</label><input id="%1$s" name="%1$s" type="%4$s" autocomplete="%6$s" value="%5$s" size="30"%7$s /></p>',
							esc_attr( $cle ),
							esc_html( $champ[0] ),
							$requis ? '&nbsp;<span class="required">*</span>' : '',
							esc_attr( $champ[1] ),
							esc_attr( $champ[2] ),
							esc_attr( $champ[3] ),
							$requis ? ' required' : ''
						);
					}

					$comment_form['must_log_in'] = '<p class="must-log-in">Vous devez être <a href="' . esc_url( wc_get_page_permalink( 'myaccount' ) ) . '">connecté</a> pour laisser un avis.</p>';

					if ( wc_review_ratings_enabled() ) {
						$comment_form['comment_field'] = '<div class="comment-form-rating"><label for="rating" id="comment-form-rating-label">Votre note' . ( wc_review_ratings_required() ? '&nbsp;<span class="required">*</span>' : '' ) . '</label><select name="rating" id="rating" required>
							<option value="">Choisir une note…</option>
							<option value="5">Parfait</option>
							<option value="4">Très bon</option>
							<option value="3">Correct</option>
							<option value="2">Décevant</option>
							<option value="1">Mauvais</option>
						</select></div>';
					}

					$comment_form['comment_field'] .= '<p class="comment-form-comment"><label for="comment">Votre commentaire&nbsp;<span class="required">*</span></label><textarea id="comment" name="comment" cols="45" rows="4" placeholder="Racontez-nous ce que vous avez pensé du plat…" required></textarea></p>';

					comment_form( apply_filters( 'woocommerce_product_review_comment_form_args', $comment_form ) );
					?>
				</div>
			</div>
		<?php else : ?>
			<h3 class="avis__form-titre">Vous avez goûté ce plat ?</h3>
			<p class="woocommerce-verification-required woocommerce-info">Seuls les clients ayant commandé ce plat peuvent laisser un avis.</p>
		<?php endif; ?>
	</div>
</div>
