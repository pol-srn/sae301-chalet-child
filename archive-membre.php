<?php
/**
 * Page « L'équipe » : liste des membres (archive du type de publication « membre », US-11.1, US-11.3, US-11.5).
 * Hiérarchie des templates : archive-{type}.php est choisi avant archive.php et index.php.
 *
 * @package chalet-child
 */

get_header();
?>
<div id="primary" class="content-area">
	<main id="main" class="site-main equipe">
		<section class="equipe__intro" aria-labelledby="equipe-titre">
			<p class="equipe__surtitre">La brigade</p>
			<h2 class="equipe__titre" id="equipe-titre">Ceux qui font le Chalet</h2>
			<p class="equipe__chapeau">Trois visages, une même exigence : cuisiner le bœuf limousin et les produits du marché comme à la maison. Découvrez celles et ceux qui préparent vos commandes à emporter.</p>
		</section>

		<?php if ( have_posts() ) : ?>
			<ul class="equipe__liste">
				<?php
				while ( have_posts() ) :
					the_post();
					chalet_carte_membre( get_the_ID(), 'h3' );
				endwhile;
				?>
			</ul>
		<?php else : ?>
			<p class="equipe__vide">L’équipe se présente bientôt.</p>
		<?php endif; ?>
	</main>
</div>
<?php
get_footer();
