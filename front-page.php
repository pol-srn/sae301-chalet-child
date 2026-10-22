<?php
/**
 * Page d'accueil statique (IT-16, US-16.1 à US-16.3).
 * Hiérarchie des templates : front-page.php est choisi pour la page définie dans Réglages › Lecture.
 *
 * @package chalet-child
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div id="primary" class="content-area">
	<main id="main" class="site-main accueil">

		<section class="accueil-intro" aria-labelledby="accueil-intro-titre">
			<p class="accueil__surtitre">Click &amp; collect · Limoges</p>
			<h2 class="accueil-intro__titre" id="accueil-intro-titre">Nos plats faits maison, prêts quand vous l’êtes</h2>
			<p class="accueil-intro__texte">Commandez en ligne, la cuisine prépare votre repas en 20 minutes environ, vous le récupérez au comptoir, <?php echo esc_html( chalet_adresse_retrait() ); ?>.</p>
			<p class="accueil-intro__actions">
				<a class="button compte__bouton" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">Commander</a>
			</p>
		</section>

		<section class="accueil-nouveautes" aria-labelledby="accueil-nouveautes-titre">
			<p class="accueil__surtitre">Tout juste arrivés</p>
			<h2 class="accueil-nouveautes__titre" id="accueil-nouveautes-titre">Nouveau à la carte</h2>
			<?php chalet_nouveautes(); ?>
			<a class="envie-plats__retour" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">Voir toute la carte</a>
		</section>

		<?php chalet_bande_etapes( 'Comment ça marche ?' ); ?>

		<section class="accueil-envies" aria-labelledby="accueil-envies-titre">
			<p class="accueil__surtitre">Une envie particulière ?</p>
			<h2 class="accueil-envies__titre" id="accueil-envies-titre">Nos envies</h2>
			<?php chalet_cartes_envies(); ?>
		</section>

	</main>
</div>
<?php
get_footer();
