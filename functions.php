<?php
/**
 * Thème enfant Le Chalet (Storefront) – SAé 3.01.
 *
 * IT-2 : apparence générale (US-2.1), page La Carte (US-2.3), fiche produit simple (US-2.4).
 * IT-3 : niveaux de stock (US-3.1), sélecteur d'options en boutons (US-3.2, US-3.3).
 * IT-4 : champs personnalisés Origine et Allergènes créés avec SCF (US-4.1), étiquette du plat avant le prix (US-4.3, US-4.4).
 * IT-5 : page Panier « Vos plats » + « L'addition » (US-5.3), choix du mode de retrait (US-5.2, US-5.4).
 * IT-6 : page Commande « Bon de commande » + « L'addition », instructions de retrait modifiables (US-6.1, US-6.2).
 * IT-11 : page L'équipe et fiche Membre (type de publication « membre » et champs créés avec SCF, US-11.1 à US-11.5).
 * IT-12 : taxonomie « envie » (SCF) indépendante des catégories, page d'une envie (US-12.1 à US-12.3).
 * IT-13 : ventes croisées en ardoises « Composez votre repas » (fiche) et « Vous oubliez quelque chose ? » (panier) (US-13.1 à US-13.3).
 * IT-14 : avis clients dans un onglet « Avis » de la fiche, réservés aux acheteurs certifiés et modérés (US-14.1, US-14.2).
 * IT-X : protection invisible des formulaires d'inscription et d'avis (champ piège + délai minimum) (US-X.1 à US-X.3).
 *
 * @package chalet-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* --------------------------------------------------------------------------
 * 1. Polices du Chalet (Storefront charge déjà style.css du thème enfant).
 * ------------------------------------------------------------------------ */
function chalet_polices() {
	wp_enqueue_style( 'chalet-dm-sans', 'https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap', array(), null );
	wp_enqueue_style( 'chalet-melodrama', 'https://api.fontshare.com/v2/css?f[]=melodrama@500&display=swap', array(), null );
	wp_dequeue_style( 'storefront-fonts' ); // Police Source Sans Pro de Storefront, inutile ici.
}
add_action( 'wp_enqueue_scripts', 'chalet_polices', 20 );

/* --------------------------------------------------------------------------
 * 2. On retire les éléments de Storefront remplacés par ceux de la maquette.
 * ------------------------------------------------------------------------ */
function chalet_reorganiser_storefront() {
	// En-tête.
	remove_action( 'storefront_header', 'storefront_header_container', 0 );
	remove_action( 'storefront_header', 'storefront_social_icons', 10 );
	remove_action( 'storefront_header', 'storefront_site_branding', 20 );
	remove_action( 'storefront_header', 'storefront_secondary_navigation', 30 );
	remove_action( 'storefront_header', 'storefront_product_search', 40 );
	remove_action( 'storefront_header', 'storefront_header_container_close', 41 );
	remove_action( 'storefront_header', 'storefront_primary_navigation_wrapper', 42 );
	remove_action( 'storefront_header', 'storefront_primary_navigation', 50 );
	remove_action( 'storefront_header', 'storefront_header_cart', 60 );
	remove_action( 'storefront_header', 'storefront_primary_navigation_wrapper_close', 68 );
	add_action( 'storefront_header', 'chalet_entete', 20 );

	// Fil d'Ariane : uniquement sur la fiche produit.
	remove_action( 'storefront_before_content', 'woocommerce_breadcrumb', 10 );
	remove_action( 'storefront_before_content', 'storefront_header_widget_region', 10 );
	add_action( 'storefront_before_content', 'chalet_fil_ariane', 10 );

	// Pas de barre latérale.
	remove_action( 'storefront_sidebar', 'storefront_get_sidebar', 10 );

	// Pied de page.
	remove_action( 'storefront_footer', 'storefront_footer_widgets', 10 );
	remove_action( 'storefront_footer', 'storefront_credit', 20 );
	remove_action( 'storefront_footer', 'storefront_handheld_footer_bar', 999 );
	add_action( 'storefront_footer', 'chalet_pied', 10 );

	// Tri et nombre de résultats : absents de la maquette.
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
	remove_action( 'woocommerce_after_shop_loop', 'woocommerce_catalog_ordering', 10 );
	remove_action( 'woocommerce_after_shop_loop', 'woocommerce_result_count', 20 );

	// Fiche produit : pas de produits apparentés (IT-13), ni de navigation produit précédent / suivant.
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
	remove_action( 'woocommerce_after_single_product_summary', 'storefront_single_product_pagination', 30 );
	remove_action( 'storefront_after_footer', 'storefront_sticky_single_add_to_cart', 999 );

	// Carte produit : description courte sous le nom.
	add_action( 'woocommerce_after_shop_loop_item_title', 'chalet_description_carte', 5 );

	// Carte produit : badge « Épuisé » / « Plus que 3 » sur la photo (IT-3).
	add_action( 'woocommerce_before_shop_loop_item_title', 'chalet_badge_carte', 5 );
}
add_action( 'init', 'chalet_reorganiser_storefront' );

/* --------------------------------------------------------------------------
 * 3. En-tête : barre (horaires, réservation, panier, menu) + logo + titre sur photo.
 * ------------------------------------------------------------------------ */
function chalet_titre_page() {
	if ( is_front_page() ) {
		return 'Le Chalet, à emporter'; // IT-16 : page d'accueil.
	}
	if ( is_shop() ) {
		return 'La Carte';
	}
	if ( is_product_category() || is_tax( 'envie' ) ) { // IT-12 : nom de l'envie.
		return single_term_title( '', false );
	}
	if ( is_product() ) {
		$categories = get_the_terms( get_the_ID(), 'product_cat' );
		return ( $categories && ! is_wp_error( $categories ) ) ? $categories[0]->name : 'La Carte';
	}
	if ( is_order_received_page() ) {
		return 'Merci !';
	}
	if ( is_post_type_archive( 'membre' ) || is_singular( 'membre' ) ) {
		return 'L’équipe'; // IT-11.
	}
	if ( is_singular() ) {
		return get_the_title();
	}
	return get_bloginfo( 'name' );
}

// Liens du pied de page : uniquement des pages de ce site (aucun lien vers le site vitrine).
function chalet_liens_restaurant() {
	return array(
		'La Carte' => wc_get_page_permalink( 'shop' ),
		'L’équipe' => get_post_type_archive_link( 'membre' ), // IT-11.
	);
}

function chalet_compteur_panier() {
	$nombre = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
	return '<span class="entete__compteur">' . absint( $nombre ) . '</span>';
}

function chalet_entete() {
	$img   = get_stylesheet_directory_uri() . '/assets/img/';
	$balise = ( is_product() || is_singular( 'membre' ) ) ? 'p' : 'h1'; // Sur une fiche, le h1 est le nom du produit ou du membre.
	?>
	<div class="entete">
		<div class="entete__barre">
			<p class="entete__horaires"><span class="entete__badge">Horaires</span> 12h-14h, 19h30-22h30</p>
			<ul class="entete__actions">
				<li><span class="entete__lien entete__lien--reservation entete__lien--inactif">Réservation</span></li><?php // Volontairement sans lien : pas de réservation en ligne sur ce site. ?>
				<li>
					<a class="entete__lien entete__lien--panier" href="<?php echo esc_url( wc_get_cart_url() ); ?>">
						<span class="screen-reader-text">Panier</span><?php echo chalet_compteur_panier(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</a>
				</li>
				<li>
					<button type="button" class="entete__lien entete__lien--menu" aria-haspopup="dialog" aria-controls="menu-principal" aria-expanded="false">
						<span class="screen-reader-text">Ouvrir le menu</span>
					</button>
				</li>
			</ul>
		</div>
		<?php chalet_menu_plein_ecran(); ?>
		<a class="entete__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<img src="<?php echo esc_url( $img . 'logo-le-chalet.png' ); ?>" alt="Le Chalet – accueil" width="286" height="42">
		</a>
		<<?php echo $balise; // phpcs:ignore ?> class="entete__titre"><?php echo esc_html( chalet_titre_page() ); ?></<?php echo $balise; // phpcs:ignore ?>>
	</div>
	<?php
}

// Lien d'appel du numéro affiché dans le panneau du menu.
function chalet_lien_reservation() {
	return 'tel:+33555775181';
}

// Menu burger par défaut (si aucun menu « Primaire » n'est choisi dans Apparence > Menus).
function chalet_menu_par_defaut() {
	// IT-12 : « Nos envies » ouvre la première envie, dont la page montre les cartes des autres.
	$envies = taxonomy_exists( 'envie' ) ? get_terms(
		array(
			'taxonomy'   => 'envie',
			'hide_empty' => true,
			'orderby'    => 'term_id',
			'number'     => 1,
		)
	) : array();

	$liens = array(
		array( 'Accueil', home_url( '/' ), is_front_page() ), // IT-16 : page d'accueil du site (pas le site vitrine).
		array( 'La Carte', wc_get_page_permalink( 'shop' ), is_shop() || is_product_category() || is_product() ),
	);
	if ( $envies && ! is_wp_error( $envies ) ) {
		$liens[] = array( 'Nos envies', get_term_link( $envies[0] ), is_tax( 'envie' ) );
	}
	$liens[] = array( 'L’équipe', get_post_type_archive_link( 'membre' ), is_post_type_archive( 'membre' ) || is_singular( 'membre' ) ); // IT-11.
	$liens[] = array( 'Mon compte', wc_get_page_permalink( 'myaccount' ), is_account_page() ); // IT-7.
	$liens[] = array( 'Panier', wc_get_cart_url(), is_cart() || is_checkout() );

	echo '<ul class="menu-ecran__liens">';
	foreach ( $liens as $lien ) {
		$actif = $lien[2] ? ' aria-current="page"' : '';
		echo '<li><a href="' . esc_url( $lien[1] ) . '"' . $actif . '>' . esc_html( $lien[0] ) . '</a></li>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	echo '<li><span class="menu-ecran__inactif">Réserver</span></li>'; // Volontairement sans lien.
	echo '</ul>';
}

// Menu plein écran (maquette « Menu ouvert ») : photo + liens à gauche, panneau caramel à droite.
// <dialog> natif : la touche Échap, le focus et le fond inerte sont gérés par le navigateur.
function chalet_menu_plein_ecran() {
	$img = get_stylesheet_directory_uri() . '/assets/img/';
	?>
	<dialog class="menu-ecran" id="menu-principal" aria-label="Menu">
		<div class="menu-ecran__photo">
			<form method="dialog">
				<button class="menu-ecran__fermer" type="submit">
					<span class="screen-reader-text">Fermer le menu</span>
					<svg width="32" height="32" viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 3l26 26M29 3L3 29"/></svg>
				</button>
			</form>
			<nav aria-label="Menu principal">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'menu-ecran__liens',
						'depth'          => 1,
						'fallback_cb'    => 'chalet_menu_par_defaut',
					)
				);
				?>
			</nav>
		</div>
		<div class="menu-ecran__infos">
			<span class="menu-ecran__taureau" aria-hidden="true"></span>
			<img class="menu-ecran__logo" src="<?php echo esc_url( $img . 'logo-le-chalet.png' ); ?>" alt="Le Chalet" width="472" height="69">
			<p class="menu-ecran__texte"><?php echo esc_html( chalet_adresse_retrait() ); ?></p>
			<p class="menu-ecran__texte"><a href="<?php echo esc_url( chalet_lien_reservation() ); ?>">05 55 77 51 81</a></p>
			<p class="menu-ecran__texte"><a href="mailto:lechalet.limoges@gmail.com">lechalet.limoges@gmail.com</a></p>
			<p class="menu-ecran__label">Retrait des commandes</p>
			<p class="menu-ecran__texte"><?php echo esc_html( ucfirst( chalet_horaires_retrait() ) ); ?></p>
			<?php chalet_reseaux( 'menu-ecran__reseaux' ); ?>
		</div>
	</dialog>
	<?php
}

// Instagram et Facebook : pied de page et menu.
function chalet_reseaux( $classe ) {
	?>
	<ul class="<?php echo esc_attr( $classe ); ?>">
		<li>
			<a class="pied__reseau" href="https://www.instagram.com/lechalet__restaurant/">
				<span class="screen-reader-text">Instagram</span>
				<svg width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4.5"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>
			</a>
		</li>
		<li>
			<a class="pied__reseau" href="https://www.facebook.com/LeChalet87">
				<span class="screen-reader-text">Facebook</span>
				<svg width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M13.5 21v-7.5h2.5l.5-3h-3V9c0-.9.4-1.5 1.6-1.5h1.5V5.1c-.5-.1-1.4-.1-2.2-.1-2.3 0-3.9 1.4-3.9 3.9v1.6H8v3h2.5V21"/></svg>
			</a>
		</li>
	</ul>
	<?php
}

// Ouverture / fermeture du menu.
function chalet_script_menu() {
	wp_enqueue_script( 'chalet-menu', get_stylesheet_directory_uri() . '/assets/js/menu.js', array(), '1.0.0', array( 'strategy' => 'defer' ) );
}
add_action( 'wp_enqueue_scripts', 'chalet_script_menu' );

// Le compteur du panier se met à jour quand on ajoute un produit sans recharger la page.
function chalet_fragment_panier( $fragments ) {
	$fragments['span.entete__compteur'] = chalet_compteur_panier();
	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'chalet_fragment_panier' );

/* --------------------------------------------------------------------------
 * 4. Page La Carte : intro Click & Collect + une section par catégorie.
 *    Appelée par woocommerce/archive-product.php.
 * ------------------------------------------------------------------------ */
function chalet_carte() {
	$categories = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
			'orderby'    => 'term_id',
		)
	);
	if ( is_wp_error( $categories ) ) {
		return;
	}
	?>
	<section class="intro" aria-labelledby="intro-titre">
		<h2 class="intro__titre" id="intro-titre">Click &amp; Collect</h2>
		<p class="intro__texte">Commandez, nous préparons, vous récupérez.</p>
		<nav class="intro__nav" aria-label="Catégories de la carte">
			<ul class="intro__liste">
				<?php foreach ( $categories as $categorie ) : ?>
					<li><a class="intro__lien" href="#<?php echo esc_attr( $categorie->slug ); ?>"><?php echo esc_html( $categorie->name ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</nav>
	</section>
	<?php foreach ( $categories as $categorie ) : ?>
		<section class="categorie" id="<?php echo esc_attr( $categorie->slug ); ?>" aria-labelledby="titre-<?php echo esc_attr( $categorie->slug ); ?>">
			<h2 class="categorie__titre" id="titre-<?php echo esc_attr( $categorie->slug ); ?>"><?php echo esc_html( $categorie->name ); ?></h2>
			<?php echo do_shortcode( '[products category="' . esc_attr( $categorie->slug ) . '" columns="3" limit="-1" orderby="menu_order" order="ASC"]' ); ?>
		</section>
	<?php endforeach; ?>
	<?php
}

function chalet_description_carte() {
	global $product;
	$description = $product ? wp_strip_all_tags( $product->get_short_description() ) : '';
	if ( $description ) {
		echo '<p class="carte-produit__description">' . esc_html( $description ) . '</p>';
	}
}

/* --------------------------------------------------------------------------
 * 5. Fiche produit : fil d'Ariane, onglet Description seul.
 * ------------------------------------------------------------------------ */
function chalet_fil_ariane() {
	if ( is_product() ) {
		woocommerce_breadcrumb();
	}
}

function chalet_separateur_ariane( $reglages ) {
	$reglages['delimiter'] = '<span class="breadcrumb-separator" aria-hidden="true"> › </span>';
	return $reglages;
}
add_filter( 'woocommerce_breadcrumb_defaults', 'chalet_separateur_ariane', 20 );

function chalet_onglets_produit( $onglets ) {
	unset( $onglets['additional_information'] );
	// IT-14 : l'ardoise « Composez votre repas » (IT-13) fait partie de l'onglet Description.
	if ( isset( $onglets['description'] ) ) {
		$onglets['description']['callback'] = 'chalet_onglet_description';
	}
	// IT-14 : onglet « Avis (n) » à côté de « Description » (contenu : woocommerce/single-product-reviews.php).
	if ( isset( $onglets['reviews'] ) ) {
		global $product;
		$onglets['reviews']['title'] = sprintf( 'Avis (%d)', $product ? $product->get_review_count() : 0 );
	}
	return $onglets;
}
add_filter( 'woocommerce_product_tabs', 'chalet_onglets_produit', 98 );

// Contenu de l'onglet Description : la description de WooCommerce, puis l'ardoise du repas.
function chalet_onglet_description() {
	woocommerce_product_description_tab();
	chalet_ardoise_repas();
}
add_filter( 'woocommerce_product_description_heading', '__return_empty_string' );

/* --------------------------------------------------------------------------
 * 6. IT-3 : stock et produits variables.
 * ------------------------------------------------------------------------ */

// Texte du stock (fiche produit + sélection d'une option) : Disponible, Plus que 3, Épuisé.
function chalet_disponibilite( $dispo, $produit ) {
	if ( ! $produit->is_in_stock() ) {
		return array( 'availability' => 'Épuisé', 'class' => 'out-of-stock' );
	}
	if ( $produit->managing_stock() ) {
		$stock = (int) $produit->get_stock_quantity();
		if ( $stock <= wc_get_low_stock_amount( $produit ) ) {
			return array( 'availability' => 'Plus que ' . $stock, 'class' => 'in-stock stock--faible' );
		}
		return array( 'availability' => 'Disponible', 'class' => 'in-stock' );
	}
	return $dispo;
}
add_filter( 'woocommerce_get_availability', 'chalet_disponibilite', 10, 2 );

// Badge sur la photo de la carte produit (La Carte).
function chalet_badge_carte() {
	global $product;
	if ( ! $product ) {
		return;
	}
	$dispo = $product->get_availability();
	if ( ! $product->is_in_stock() ) {
		echo '<span class="badge badge--erreur carte-produit__badge">Épuisé</span>';
	} elseif ( ! empty( $GLOBALS['chalet_nouveautes'] ) ) {
		echo '<span class="badge badge--nouveau carte-produit__badge">Nouveau</span>'; // IT-16 : bloc « Nouveau à la carte ».
	} elseif ( str_contains( $dispo['class'], 'stock--faible' ) ) {
		echo '<span class="badge badge--alerte carte-produit__badge">' . esc_html( $dispo['availability'] ) . '</span>';
	}
}

// Bouton de la carte : « Choisir les options » (produit variable), « Indisponible » (épuisé, non cliquable).
function chalet_texte_bouton_carte( $texte, $produit ) {
	return $produit->is_type( 'variable' ) && $produit->is_in_stock() ? 'Choisir les options' : $texte;
}
add_filter( 'woocommerce_product_add_to_cart_text', 'chalet_texte_bouton_carte', 10, 2 );

function chalet_bouton_carte( $lien, $produit ) {
	if ( ! $produit->is_in_stock() ) {
		return '<span class="button button--indisponible">Indisponible</span>';
	}
	return $lien;
}
add_filter( 'woocommerce_loop_add_to_cart_link', 'chalet_bouton_carte', 10, 2 );

// Fiche produit variable : les listes déroulantes deviennent des boutons radio (maquette v3.0).
// La liste déroulante reste dans la page (masquée) : WooCommerce s'en sert pour trouver la variation.
function chalet_options_en_boutons( $html, $args ) {
	$attribut = $args['attribute'];
	$produit  = $args['product'];
	$options  = $args['options'];
	if ( empty( $options ) && $produit && $attribut ) {
		$attributs = $produit->get_variation_attributes();
		$options   = $attributs[ $attribut ] ?? array();
	}
	if ( empty( $options ) ) {
		return $html;
	}

	$nom    = 'choix-' . sanitize_title( $attribut );
	$sortie = '<fieldset class="choix"><legend class="choix__titre">' . esc_html( wc_attribute_label( $attribut, $produit ) ) . '</legend><div class="choix__liste">';
	foreach ( $options as $option ) {
		$valeur = $option;
		$texte  = $option;
		if ( taxonomy_exists( $attribut ) ) {
			$terme = get_term_by( 'slug', $option, $attribut );
			$texte = $terme ? $terme->name : $option;
		}
		$coche   = ( $args['selected'] === $valeur || $args['selected'] === sanitize_title( $valeur ) ) ? ' checked' : '';
		$sortie .= '<label class="choix__option"><input class="choix__radio" type="radio" name="' . esc_attr( $nom ) . '" value="' . esc_attr( $valeur ) . '"' . $coche . '> ' . esc_html( $texte ) . '</label>';
	}
	$sortie .= '</div></fieldset>';

	return $html . $sortie;
}
add_filter( 'woocommerce_dropdown_variation_attribute_options_html', 'chalet_options_en_boutons', 10, 2 );
add_filter( 'woocommerce_reset_variations_link', '__return_empty_string' );

// Sélecteur de quantité de la maquette : boutons − et + autour du champ de WooCommerce.
function chalet_bouton_moins() {
	if ( is_product() || is_cart() ) {
		echo '<button type="button" class="quantite__bouton" data-pas="-1" aria-label="Retirer un">−</button>';
	}
}
add_action( 'woocommerce_before_quantity_input_field', 'chalet_bouton_moins' );

function chalet_bouton_plus() {
	if ( is_product() || is_cart() ) {
		echo '<button type="button" class="quantite__bouton" data-pas="1" aria-label="Ajouter un">+</button>';
	}
}
add_action( 'woocommerce_after_quantity_input_field', 'chalet_bouton_plus' );

// Petit script qui recopie le bouton coché dans la liste déroulante masquée (et gère − / +).
function chalet_script_options() {
	if ( is_product() || is_cart() ) {
		wp_enqueue_script( 'chalet-options-produit', get_stylesheet_directory_uri() . '/assets/js/options-produit.js', array(), '1.4.0', array( 'strategy' => 'defer' ) );
	}
}
add_action( 'wp_enqueue_scripts', 'chalet_script_options' );

/* --------------------------------------------------------------------------
 * 7. IT-4 : champs personnalisés (Secure Custom Fields) et étiquette du plat.
 * ------------------------------------------------------------------------ */

// Le groupe de champs « Étiquette du plat » (Origine, Allergènes) est créé dans l'interface de Secure Custom Fields.

// Étiquette du plat sur la fiche produit : après le titre (5), avant le prix (10).
function chalet_etiquette_plat() {
	if ( ! function_exists( 'get_field' ) ) {
		return;
	}
	$origine    = get_field( 'origine' );
	$allergenes = get_field( 'allergenes' );
	if ( ! $origine && ! $allergenes ) {
		return; // Rien de renseigné : pas d'étiquette.
	}
	?>
	<section class="etiquette" aria-labelledby="etiquette-titre">
		<div class="etiquette__fond">
			<?php if ( $origine ) : ?>
				<p class="etiquette__sceau">
					<span class="etiquette__sceau-libelle">Origine</span>
					<span class="etiquette__sceau-filet" aria-hidden="true"></span>
					<span class="etiquette__origine"><?php echo esc_html( $origine ); ?></span>
				</p>
			<?php endif; ?>
			<div class="etiquette__contenu">
				<h2 class="etiquette__titre" id="etiquette-titre">Dans l’assiette</h2>
				<?php if ( $allergenes ) : ?>
					<p class="etiquette__libelle">Allergènes</p>
					<ul class="etiquette__allergenes">
						<?php foreach ( (array) $allergenes as $allergene ) : ?>
							<li><?php echo esc_html( $allergene ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<p class="etiquette__libelle">Aucun allergène à déclarer</p>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
}
add_action( 'woocommerce_single_product_summary', 'chalet_etiquette_plat', 6 );

/* --------------------------------------------------------------------------
 * 8. IT-5 et IT-6 : panier et commande.
 * ------------------------------------------------------------------------ */
function chalet_reglages_panier() {
	remove_action( 'storefront_page', 'storefront_page_header', 10 );
	remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
	remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
	remove_action( 'woocommerce_checkout_terms_and_conditions', 'wc_checkout_privacy_policy_text', 20 );
	remove_action( 'woocommerce_thankyou', 'woocommerce_order_details_table', 10 ); // Remplacé par l'addition de thankyou.php.
	remove_action( 'woocommerce_view_order', 'woocommerce_order_details_table', 10 ); // IT-9 : remplacé par l'addition de view-order.php.
}
add_action( 'init', 'chalet_reglages_panier' );

/* IT-7 : navigation de Mon compte (filtre natif) ; « Téléchargements » ne sert pas à un restaurant. */
function chalet_menu_compte( $items ) {
	$libelles = array(
		'dashboard'       => 'Tableau de bord',
		'orders'          => 'Commandes',
		'edit-address'    => 'Adresses',
		'edit-account'    => 'Détails du compte',
		'customer-logout' => 'Déconnexion',
	);
	return array_intersect_key( $libelles, $items );
}
add_filter( 'woocommerce_account_menu_items', 'chalet_menu_compte' );

/* « Poursuivre les achats » ramène à La Carte, pas à la fiche produit d'où vient le client. */
function chalet_retour_carte() {
	return wc_get_page_permalink( 'shop' );
}
add_filter( 'woocommerce_continue_shopping_redirect', 'chalet_retour_carte' );

function chalet_options_plat_panier( $cart_item ) {
	$options = array();
	foreach ( (array) $cart_item['variation'] as $attribut => $valeur ) {
		if ( '' === $valeur ) {
			continue;
		}
		$taxonomie = str_replace( 'attribute_', '', $attribut );
		$terme     = taxonomy_exists( $taxonomie ) ? get_term_by( 'slug', $valeur, $taxonomie ) : false;
		$options[] = $terme ? $terme->name : $valeur;
	}
	return implode( ' · ', $options );
}

function chalet_code_postal_par_defaut( $paquets ) {
	foreach ( $paquets as $i => $paquet ) {
		if ( empty( $paquet['destination']['postcode'] ) ) {
			$paquets[ $i ]['destination']['postcode'] = get_option( 'woocommerce_store_postcode' );
		}
	}
	return $paquets;
}
add_filter( 'woocommerce_cart_shipping_packages', 'chalet_code_postal_par_defaut' );

function chalet_nom_plat_panier( $produit ) {
	$parent = $produit->get_parent_id() ? wc_get_product( $produit->get_parent_id() ) : false;
	return $parent ? $parent->get_name() : $produit->get_name();
}

/* Page Confirmation (IT-6) : nom et options d'un plat d'une commande passée. */
function chalet_nom_plat_commande( $item ) {
	$produit = $item->get_product();
	return $produit ? chalet_nom_plat_panier( $produit ) : $item->get_name();
}

function chalet_options_plat_commande( $item ) {
	$produit = $item->get_product();
	if ( ! $produit || ! $produit->is_type( 'variation' ) ) {
		return '';
	}
	// Mêmes options que dans le panier : attributs de la variante commandée ;
	// une option « n'importe laquelle » (vide) est relue dans la ligne de commande.
	$attributs = $produit->get_variation_attributes();
	foreach ( $attributs as $cle => $valeur ) {
		if ( '' === $valeur ) {
			$attributs[ $cle ] = (string) $item->get_meta( str_replace( 'attribute_', '', $cle ) );
		}
	}
	return chalet_options_plat_panier( array( 'variation' => $attributs ) );
}

/* US-6.1 : instructions de retrait réglées dans WooCommerce → Click & Collect (page d'options SCF). */
function chalet_reglage_retrait( $champ, $defaut ) {
	$valeur = function_exists( 'get_field' ) ? get_field( $champ, 'option' ) : '';
	return $valeur ? $valeur : $defaut;
}

function chalet_etapes_retrait() {
	$defaut = array(
		array( 'titre' => 'Vous commandez', 'texte' => 'Votre commande part en cuisine dès sa validation.' ),
		array( 'titre' => 'Nous préparons', 'texte' => 'Comptez environ 20 minutes, tout est fait maison.' ),
		array( 'titre' => 'Vous récupérez', 'texte' => 'Au comptoir, avec votre numéro de commande.' ),
	);
	// Une ligne enregistrée sans titre est ignorée ; sans aucune étape remplie, on garde le texte par défaut.
	$etapes = array_filter(
		(array) chalet_reglage_retrait( 'retrait_etapes', array() ),
		function ( $etape ) {
			return ! empty( $etape['titre'] );
		}
	);
	return $etapes ? array_values( $etapes ) : $defaut;
}

function chalet_adresse_retrait() {
	return chalet_reglage_retrait( 'retrait_adresse', '5 rue des Filles Notre-Dame, 87000 Limoges' );
}

function chalet_horaires_retrait() {
	return chalet_reglage_retrait( 'retrait_horaires', 'du mardi au samedi · 12h–14h · 19h30–22h30' );
}

function chalet_info_retrait() {
	return chalet_adresse_retrait() . ', ' . chalet_horaires_retrait();
}

function chalet_bande_retrait() {
	chalet_bande_etapes( chalet_reglage_retrait( 'retrait_titre', 'Instructions pour le retrait' ) );
}
add_action( 'woocommerce_before_checkout_form', 'chalet_bande_retrait', 5 );

// Bande des 3 étapes (page Commande, et « Comment ça marche ? » de l'accueil à l'IT-16).
function chalet_bande_etapes( $titre, $ancre = '' ) {
	?>
	<div class="retrait"<?php echo $ancre ? ' id="' . esc_attr( $ancre ) . '"' : ''; ?>>
		<h2 class="retrait__titre"><?php echo esc_html( $titre ); ?></h2>
		<ol class="retrait__etapes">
			<?php foreach ( chalet_etapes_retrait() as $etape ) : ?>
				<li><strong><?php echo esc_html( $etape['titre'] ); ?></strong><?php echo esc_html( $etape['texte'] ); ?></li>
			<?php endforeach; ?>
		</ol>
		<p class="retrait__infos"><?php echo esc_html( chalet_adresse_retrait() . ' · ' . chalet_horaires_retrait() ); ?></p>
	</div>
	<?php
}

function chalet_livraison_choisie() {
	$methodes = WC()->session ? (array) WC()->session->get( 'chosen_shipping_methods', array() ) : array();
	$methode  = (string) reset( $methodes );
	return '' !== $methode && ! str_starts_with( $methode, 'local_pickup' );
}

function chalet_champs_commande( $champs ) {
	$libelles = array(
		'billing_first_name' => 'Prénom',
		'billing_last_name'  => 'Nom',
		'billing_phone'      => 'Téléphone',
		'billing_email'      => 'E-mail',
		'billing_address_1'  => 'Adresse de livraison',
		'billing_postcode'   => 'Code postal',
		'billing_city'       => 'Ville',
	);
	$adresse = array( 'billing_address_1', 'billing_postcode', 'billing_city' );
	$ordre   = 10;
	foreach ( array_keys( $champs['billing'] ) as $cle ) {
		if ( 'billing_country' === $cle ) {
			$champs['billing'][ $cle ] = array(
				'type'     => 'hidden',
				'class'    => array( 'bon__champ--cache' ),
				'default'  => get_option( 'woocommerce_default_country' ),
				'priority' => 100,
			);
			continue;
		}
		if ( ! isset( $libelles[ $cle ] ) ) {
			unset( $champs['billing'][ $cle ] );
			continue;
		}
		$est_adresse = in_array( $cle, $adresse, true );
		$classes     = array( 'bon__champ' );
		if ( $est_adresse ) {
			$classes[] = 'bon__champ--adresse';
		}
		if ( 'billing_address_1' === $cle ) {
			$classes[] = 'bon__champ--plein';
		}
		$champs['billing'][ $cle ] = array_merge(
			$champs['billing'][ $cle ],
			array(
				'label'       => $libelles[ $cle ],
				'required'    => ! $est_adresse || chalet_livraison_choisie(),
				'placeholder' => '',
				'class'       => $classes,
				'label_class' => array( 'bon__libelle' ),
				'input_class' => array( 'bon__saisie' ),
				'priority'    => $ordre,
			)
		);
		$ordre += 10;
	}
	// IT-7 : mot de passe du compte créé pendant la commande, au style du Bon de commande.
	foreach ( (array) ( $champs['account'] ?? array() ) as $cle => $champ ) {
		$champs['account'][ $cle ] = array_merge(
			$champ,
			array(
				'label'       => 'account_password' === $cle ? 'Mot de passe du compte' : $champ['label'],
				'placeholder' => '',
				'class'       => array( 'bon__champ' ),
				'label_class' => array( 'bon__libelle' ),
				'input_class' => array( 'bon__saisie' ),
			)
		);
	}
	if ( isset( $champs['order']['order_comments'] ) ) {
		$champs['order']['order_comments'] = array_merge(
			$champs['order']['order_comments'],
			array(
				'label'       => 'Un mot pour la cuisine',
				'placeholder' => 'Sans oignons, une allergie…',
				'class'       => array( 'bon__champ', 'bon__champ--plein', 'bon__champ--note' ),
				'label_class' => array( 'bon__libelle' ),
				'input_class' => array( 'bon__saisie' ),
				'custom_attributes' => array( 'rows' => 1 ),
			)
		);
	}
	return $champs;
}
add_filter( 'woocommerce_checkout_fields', 'chalet_champs_commande', 20 );

function chalet_texte_bouton_commande() {
	return 'Commander';
}
add_filter( 'woocommerce_order_button_text', 'chalet_texte_bouton_commande' );

/* --------------------------------------------------------------------------
 * 8 bis. IT-8 : e-mails transactionnels (logo, couleurs et réglages communs dans
 * WooCommerce › Réglages › E-mails ; pied dans woocommerce/emails/email-footer.php).
 * ------------------------------------------------------------------------ */

/* Styles ajoutés à ceux de WooCommerce, qui les écrit dans les balises (exigé par les messageries).
   Polices de secours : les messageries ne chargent pas Melodrama ni DM Sans. */
function chalet_styles_email( $css ) {
	return $css . '
		#template_header_image { background-color: #121f29; padding: 28px 20px 20px; text-align: center; }
		#template_header_image p { margin: 0; }
		#template_header_image img { width: 240px; max-width: 100%; height: auto; }
		#header_wrapper h1, #body_content h2, #body_content h3 { font-family: Georgia, "Times New Roman", serif; font-weight: normal; color: #1e1a17; }
		#template_footer { background-color: #121f29; }
		#template_footer #credit, #template_footer #credit p { color: #ffffff; font-size: 13px; line-height: 150%; text-align: center; }
		#template_footer #credit a { color: #ded1c3; }
		.retrait-mail { margin: 0 0 24px; background-color: #8a6544; color: #ffffff; }
		.retrait-mail td { padding: 24px; color: #ffffff; }
		.retrait-mail__titre { margin: 0 0 16px; font-size: 12px; font-weight: bold; letter-spacing: 2px; text-transform: uppercase; color: #ffffff; }
		.retrait-mail__numero { margin: 0 0 12px; font-family: Georgia, "Times New Roman", serif; font-size: 44px; line-height: 110%; color: #ffffff; }
		.retrait-mail__etape { margin: 0 0 12px; font-size: 14px; line-height: 150%; color: #ffffff; }
		.retrait-mail__etape strong { display: block; font-family: Georgia, "Times New Roman", serif; font-size: 18px; font-weight: normal; }
		.retrait-mail__losange { color: #ded1c3; }
		.retrait-mail__infos { margin: 16px 0 0; padding-top: 16px; border-top: 1px solid #ded1c3; font-size: 13px; line-height: 150%; color: #ffffff; }
		.bouton-mail { margin: 8px 0 32px; text-align: center; }
		.bouton-mail a { display: inline-block; padding: 16px 28px; background-color: #8a6544; color: #ffffff; font-weight: bold; letter-spacing: 1px; text-decoration: none; text-transform: uppercase; }
	';
}
add_filter( 'woocommerce_email_styles', 'chalet_styles_email' );

/* E-mails au client concernés par le retrait : commande en attente (virement) et en cours. */
function chalet_email_retrait_concerne( $order, $sent_to_admin, $email ) {
	$ids = array( 'customer_on_hold_order', 'customer_processing_order' );
	return ! $sent_to_admin && $email && in_array( $email->id, $ids, true ) && $order->has_shipping_method( 'local_pickup' );
}

/* US-8.3 : instructions de retrait (WooCommerce › Click & Collect), avant le récapitulatif. */
function chalet_email_instructions_retrait( $order, $sent_to_admin, $plain_text, $email ) {
	if ( ! chalet_email_retrait_concerne( $order, $sent_to_admin, $email ) ) {
		return;
	}
	$titre  = chalet_reglage_retrait( 'retrait_titre', 'Instructions pour le retrait' );
	$etapes = chalet_etapes_retrait();
	$infos  = chalet_adresse_retrait() . ' · ' . chalet_horaires_retrait();
	if ( $plain_text ) {
		echo "\n" . esc_html( mb_strtoupper( $titre ) ) . "\n";
		foreach ( $etapes as $etape ) {
			echo '- ' . esc_html( $etape['titre'] ) . ' : ' . esc_html( $etape['texte'] ) . "\n";
		}
		echo esc_html( $infos ) . "\n\n";
		return;
	}
	?>
	<table class="retrait-mail" width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation" bgcolor="#8a6544">
		<tr>
			<td>
				<p class="retrait-mail__titre"><?php echo esc_html( $titre ); ?></p>
				<?php foreach ( $etapes as $etape ) : ?>
					<p class="retrait-mail__etape"><strong><span class="retrait-mail__losange">◆</span> <?php echo esc_html( $etape['titre'] ); ?></strong><?php echo esc_html( $etape['texte'] ); ?></p>
				<?php endforeach; ?>
				<p class="retrait-mail__infos"><?php echo esc_html( $infos ); ?></p>
			</td>
		</tr>
	</table>
	<?php
}
add_action( 'woocommerce_email_order_details', 'chalet_email_instructions_retrait', 5, 4 );

/* Bouton « Suivre ma commande » sous le récapitulatif, dans les e-mails de commande au client. */
function chalet_email_bouton_suivi( $order, $sent_to_admin, $plain_text, $email ) {
	$ids = array( 'customer_on_hold_order', 'customer_processing_order', 'customer_ready_order', 'customer_completed_order' );
	if ( $sent_to_admin || ! $email || ! in_array( $email->id, $ids, true ) ) {
		return;
	}
	$lien = $order->get_customer_id() ? $order->get_view_order_url() : wc_get_page_permalink( 'myaccount' );
	if ( $plain_text ) {
		echo 'Suivre ma commande : ' . esc_url( $lien ) . "\n\n";
		return;
	}
	echo '<p class="bouton-mail"><a href="' . esc_url( $lien ) . '">Suivre ma commande</a></p>';
}
add_action( 'woocommerce_email_customer_details', 'chalet_email_bouton_suivi', 5, 4 );

/* --------------------------------------------------------------------------
 * 8 ter. IT-9 : commandes (statut « Prête à retirer », e-mail, annulation, suivi).
 * ------------------------------------------------------------------------ */

/* Addition en lecture seule d'une commande passée (page Confirmation et Mon compte › Détail commande). */
function chalet_addition_commande( $order ) {
	$devise = array( 'currency' => $order->get_currency() );
	?>
	<div class="addition">
		<div class="addition__fond">
			<h2 class="addition__titre">L’addition</h2>
			<div class="addition__detail">
				<?php foreach ( $order->get_items() as $item ) : ?>
					<p class="addition__ligne addition__ligne--plat">
						<span class="addition__libelle"><?php echo esc_html( chalet_nom_plat_commande( $item ) ); ?> ×<?php echo absint( $item->get_quantity() ); ?></span>
						<span class="addition__points" aria-hidden="true"></span>
						<span class="addition__montant"><?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?></span>
					</p>
					<?php $options = chalet_options_plat_commande( $item ); ?>
					<?php if ( $options ) : ?>
						<p class="addition__options"><?php echo esc_html( $options ); ?></p>
					<?php endif; ?>
				<?php endforeach; ?>
				<p class="addition__ligne">
					<span class="addition__libelle">Sous-total</span>
					<span class="addition__points" aria-hidden="true"></span>
					<span class="addition__montant"><?php echo wp_kses_post( wc_price( $order->get_subtotal(), $devise ) ); ?></span>
				</p>
				<?php if ( $order->get_total_discount() > 0 ) : ?>
					<p class="addition__ligne addition__ligne--plat">
						<span class="addition__libelle">Réduction</span>
						<span class="addition__points" aria-hidden="true"></span>
						<span class="addition__montant">−<?php echo wp_kses_post( wc_price( $order->get_total_discount(), $devise ) ); ?></span>
					</p>
				<?php endif; ?>
				<?php if ( $order->get_shipping_method() ) : ?>
					<p class="addition__ligne addition__ligne--plat">
						<span class="addition__libelle"><?php echo esc_html( $order->get_shipping_method() ); ?></span>
						<span class="addition__points" aria-hidden="true"></span>
						<span class="addition__montant"><?php echo (float) $order->get_shipping_total() > 0 ? wp_kses_post( wc_price( $order->get_shipping_total(), $devise ) ) : 'Gratuit'; ?></span>
					</p>
				<?php endif; ?>
				<?php foreach ( $order->get_refunds() as $remboursement ) : ?>
					<p class="addition__ligne addition__ligne--plat">
						<span class="addition__libelle">Remboursement</span>
						<span class="addition__points" aria-hidden="true"></span>
						<span class="addition__montant">−<?php echo wp_kses_post( wc_price( $remboursement->get_amount(), $devise ) ); ?></span>
					</p>
				<?php endforeach; ?>
				<p class="addition__ligne addition__ligne--total">
					<span class="addition__libelle">Total</span>
					<span class="addition__points" aria-hidden="true"></span>
					<span class="addition__montant"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></span>
				</p>
			</div>
		</div>
	</div>
	<?php
}

/* US-9.2 : statut « Prête à retirer », entre « En cours » et « Terminée ». */
function chalet_statut_prete( $statuts ) {
	$statuts['wc-prete'] = array(
		'label'                     => 'Prête à retirer',
		'public'                    => false,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		/* translators: %s: nombre de commandes. */
		'label_count'               => _n_noop( 'Prête à retirer <span class="count">(%s)</span>', 'Prêtes à retirer <span class="count">(%s)</span>' ),
	);
	return $statuts;
}
add_filter( 'woocommerce_register_shop_order_post_statuses', 'chalet_statut_prete' );

function chalet_liste_statuts( $statuts ) {
	$liste = array();
	foreach ( $statuts as $cle => $libelle ) {
		$liste[ $cle ] = $libelle;
		if ( 'wc-processing' === $cle ) {
			$liste['wc-prete'] = 'Prête à retirer';
		}
	}
	return $liste;
}
add_filter( 'wc_order_statuses', 'chalet_liste_statuts' );

/* Action groupée dans WooCommerce › Commandes (WooCommerce traite seul les actions « mark_<statut> »). */
function chalet_action_groupee_prete( $actions ) {
	$actions['mark_prete'] = 'Passer en Prête à retirer';
	return $actions;
}
add_filter( 'bulk_actions-woocommerce_page_wc-orders', 'chalet_action_groupee_prete' );
add_filter( 'bulk_actions-edit-shop_order', 'chalet_action_groupee_prete' );

/* Couleur du statut dans la liste des commandes du back-office. */
function chalet_couleur_statut_admin() {
	echo '<style>.order-status.status-prete{background:#e5af4a;color:#1e1a17}</style>';
}
add_action( 'admin_head', 'chalet_couleur_statut_admin' );

/* US-9.2 : e-mail « Votre commande est prête » (classe dans inc/, gabarits dans woocommerce/emails/). */
function chalet_email_commande_prete( $emails ) {
	require_once get_stylesheet_directory() . '/inc/class-chalet-email-commande-prete.php';
	$emails['Chalet_Email_Commande_Prete'] = new Chalet_Email_Commande_Prete();
	return $emails;
}
add_filter( 'woocommerce_email_classes', 'chalet_email_commande_prete' );

/* US-9.3 : le client peut annuler tant que la commande n'est pas prête (stock rétabli par WooCommerce). */
function chalet_statuts_annulables( $statuts ) {
	return array_unique( array_merge( $statuts, array( 'on-hold', 'processing' ) ) );
}
add_filter( 'woocommerce_valid_order_statuses_for_cancel', 'chalet_statuts_annulables' );

/* Couleur du badge selon le statut (composant Badge du Design System). */
function chalet_classe_badge( $statut ) {
	$classes = array(
		'processing' => 'badge--statut',
		'prete'      => 'badge--statut',
		'on-hold'    => 'badge--alerte',
		'pending'    => 'badge--alerte',
		'cancelled'  => 'badge--erreur',
		'refunded'   => 'badge--erreur',
		'failed'     => 'badge--erreur',
	);
	return $classes[ $statut ] ?? '';
}

/* Frise de suivi (Mon compte › Détail commande) : étapes, étape en cours et message selon le statut. */
function chalet_suivi_commande( $order ) {
	$statut  = $order->get_status();
	$retrait = $order->has_shipping_method( 'local_pickup' );
	if ( in_array( $statut, array( 'cancelled', 'refunded', 'failed' ), true ) ) {
		$messages = array(
			'cancelled' => 'Cette commande a été annulée. Le stock des plats a été rétabli.',
			'refunded'  => 'Cette commande a été annulée et remboursée.',
			'failed'    => 'Le paiement de cette commande n’a pas abouti.',
		);
		return array( 'etapes' => array(), 'message' => $messages[ $statut ] );
	}
	$etapes = array(
		array( 'Reçue', wc_format_datetime( $order->get_date_created(), 'd/m · H \h i' ) ),
		array( 'En préparation', 'Tout est fait maison' ),
		$retrait ? array( 'Prête à retirer', 'Au comptoir, avec votre numéro' ) : array( 'En livraison', 'Vers votre adresse' ),
		$retrait ? array( 'Retirée', 'cod' === $order->get_payment_method() ? 'Paiement au retrait' : 'Bon appétit !' ) : array( 'Livrée', 'Bon appétit !' ),
	);
	$position = array( 'pending' => 0, 'on-hold' => 0, 'processing' => 1, 'prete' => 2, 'completed' => 4 );
	$courante = $position[ $statut ] ?? 0;
	foreach ( $etapes as $i => $etape ) {
		$etapes[ $i ][2] = $i < $courante ? 'faite' : ( $i === $courante ? 'en-cours' : 'a-venir' );
	}
	$messages = array(
		'pending'    => 'Nous attendons votre paiement pour lancer la préparation.',
		'on-hold'    => 'Nous attendons votre virement pour lancer la préparation.',
		'processing' => 'Votre commande est en cuisine. Nous vous prévenons par e-mail dès qu’elle est prête.',
		'prete'      => $retrait ? 'Votre commande vous attend au comptoir du Chalet.' : 'Votre commande est en route.',
		'completed'  => 'Commande terminée. Merci et à bientôt au Chalet !',
	);
	return array( 'etapes' => $etapes, 'message' => $messages[ $statut ] ?? '' );
}

/* --------------------------------------------------------------------------
 * 8 quater. IT-10 : codes promo (créés dans Marketing › Codes promo).
 * ------------------------------------------------------------------------ */

/* Ligne de l'addition pour un code appliqué : « Code CHALET10 · Retirer ……… −2,38 € ».
   La classe woocommerce-remove-coupon est reconnue par les scripts du panier et de la commande (retrait sans rechargement). */
function chalet_ligne_code_promo( $code ) {
	$montant = WC()->cart->get_coupon_discount_amount( $code, WC()->cart->display_cart_ex_tax );
	$retrait = add_query_arg( 'remove_coupon', rawurlencode( $code ), is_checkout() ? wc_get_checkout_url() : wc_get_cart_url() );
	?>
	<p class="addition__ligne addition__ligne--plat addition__ligne--code">
		<span class="addition__libelle">Code <?php echo esc_html( mb_strtoupper( $code ) ); ?></span>
		<a class="woocommerce-remove-coupon addition__retirer" href="<?php echo esc_url( $retrait ); ?>" data-coupon="<?php echo esc_attr( $code ); ?>">Retirer<span class="screen-reader-text"> le code <?php echo esc_html( mb_strtoupper( $code ) ); ?></span></a>
		<span class="addition__points" aria-hidden="true"></span>
		<span class="addition__montant">−<?php echo wp_kses_post( wc_price( $montant ) ); ?></span>
	</p>
	<?php
}

/* Champ « Un code promo ? » de l'addition du panier. L'addition est hors du formulaire du panier :
   l'attribut form="chalet-panier" rattache le champ et le bouton au formulaire (traitement natif de WooCommerce). */
function chalet_champ_code_promo() {
	?>
	<div class="code-promo">
		<label class="code-promo__libelle" for="coupon_code">Un code promo ?</label>
		<div class="code-promo__ligne">
			<input class="code-promo__saisie input-text" type="text" name="coupon_code" id="coupon_code" form="chalet-panier" placeholder="Saisissez votre code" autocomplete="off">
			<button class="code-promo__bouton" type="submit" name="apply_coupon" value="Appliquer" form="chalet-panier">Appliquer</button>
		</div>
	</div>
	<?php
}

/* US-10.3 : messages des codes promo, clairs et utiles pour le client. */
function chalet_message_code_promo( $message, $code_message, $coupon ) {
	$code = $coupon ? mb_strtoupper( $coupon->get_code() ) : '';
	if ( WC_Coupon::WC_COUPON_SUCCESS === $code_message ) {
		return 'Le code ' . $code . ' a été appliqué : la réduction apparaît dans l’addition.';
	}
	if ( WC_Coupon::WC_COUPON_REMOVED === $code_message ) {
		return 'Le code ' . $code . ' a été retiré.';
	}
	return $message;
}
add_filter( 'woocommerce_coupon_message', 'chalet_message_code_promo', 10, 3 );

function chalet_erreur_code_promo( $erreur, $code_erreur, $coupon ) {
	$code = $coupon ? mb_strtoupper( $coupon->get_code() ) : '';
	switch ( $code_erreur ) {
		case WC_Coupon::E_WC_COUPON_NOT_EXIST:
			return 'Le code « ' . esc_html( $code ) . ' » n’existe pas. Vérifiez l’orthographe.';
		case WC_Coupon::E_WC_COUPON_EXPIRED:
			$date = $coupon->get_date_expires();
			return 'Le code ' . esc_html( $code ) . ' a expiré' . ( $date ? ' le ' . $date->date_i18n( 'd/m/Y' ) : '' ) . '.';
		case WC_Coupon::E_WC_COUPON_NOT_APPLICABLE:
			$plats = array_filter( array_map( 'get_the_title', $coupon->get_product_ids() ) );
			return 'Le code ' . esc_html( $code ) . ( $plats ? ' ne s’applique qu’à : ' . esc_html( implode( ', ', $plats ) ) . '. Ajoutez l’un de ces plats.' : ' ne s’applique à aucun plat de votre panier.' );
		case WC_Coupon::E_WC_COUPON_PLEASE_ENTER:
			return 'Saisissez un code promo.';
	}
	return $erreur;
}
add_filter( 'woocommerce_coupon_error', 'chalet_erreur_code_promo', 10, 3 );

/* --------------------------------------------------------------------------
 * 8 quinquies. IT-11 : L'équipe (type de publication « membre » créé avec SCF).
 *    Champs SCF du groupe « Fiche membre » : poste, depuis, citation, plat_signature.
 *    Templates : archive-membre.php (liste) et single-membre.php (fiche).
 * ------------------------------------------------------------------------ */

// Les membres dans leur ordre d'arrivée dans le back-office (le chef d'abord).
function chalet_ordre_membres( $requete ) {
	if ( ! is_admin() && $requete->is_main_query() && $requete->is_post_type_archive( 'membre' ) ) {
		$requete->set( 'orderby', 'date' );
		$requete->set( 'order', 'ASC' );
		$requete->set( 'posts_per_page', -1 );
	}
}
add_action( 'pre_get_posts', 'chalet_ordre_membres' );

// Carte d'un membre (composant Figma « Carte membre ») : liste et « Le reste de la brigade ».
function chalet_carte_membre( $membre_id, $titre = 'h2' ) {
	$nom      = get_the_title( $membre_id );
	$poste    = get_field( 'poste', $membre_id );
	$citation = get_field( 'citation', $membre_id );
	?>
	<li class="carte-membre">
		<div class="carte-membre__photo">
			<?php echo get_the_post_thumbnail( $membre_id, 'large', array( 'alt' => '' ) ); // Décorative ici : le nom suit. ?>
		</div>
		<div class="carte-membre__corps">
			<?php if ( $poste ) : ?>
				<p class="carte-membre__poste"><?php echo esc_html( $poste ); ?></p>
			<?php endif; ?>
			<<?php echo tag_escape( $titre ); ?> class="carte-membre__nom"><?php echo esc_html( $nom ); ?></<?php echo tag_escape( $titre ); ?>>
			<?php if ( $citation ) : ?>
				<p class="carte-membre__citation">« <?php echo esc_html( $citation ); ?> »</p>
			<?php endif; ?>
			<a class="carte-membre__bouton" href="<?php echo esc_url( get_permalink( $membre_id ) ); ?>">
				Voir le profil<span class="screen-reader-text"> de <?php echo esc_html( $nom ); ?></span>
			</a>
		</div>
	</li>
	<?php
}

/* --------------------------------------------------------------------------
 * 8 sexies. IT-12 : taxonomie « envie » (créée dans SCF › Taxonomies).
 *    Gabarit : taxonomy-envie.php (cartes des envies + grille des plats).
 * ------------------------------------------------------------------------ */

// Image d'une carte d'envie : champ SCF « image_envie » du terme s'il existe, sinon la photo d'un de ses plats.
function chalet_image_envie( $terme ) {
	$image = function_exists( 'get_field' ) ? get_field( 'image_envie', $terme ) : null;
	if ( $image ) {
		return is_array( $image ) ? $image['sizes']['medium_large'] : wp_get_attachment_image_url( $image, 'medium_large' );
	}
	$plats = get_posts(
		array(
			'post_type'      => 'product',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
			'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery
				array(
					'taxonomy' => 'envie',
					'terms'    => $terme->term_id,
				),
			),
		)
	);
	return $plats ? get_the_post_thumbnail_url( $plats[0], 'medium_large' ) : '';
}

// Cartes photo des envies (page d'une envie et bloc « Nos envies » de La Carte). $actif : envie en cours.
function chalet_cartes_envies( $actif = 0 ) {
	$envies = get_terms(
		array(
			'taxonomy'   => 'envie',
			'hide_empty' => true,
			'orderby'    => 'term_id', // Ordre de création dans le back-office, comme sur la maquette.
		)
	);
	if ( is_wp_error( $envies ) || ! $envies ) {
		return;
	}
	?>
	<nav aria-label="Les envies du Chalet">
		<ul class="envies">
			<?php foreach ( $envies as $terme ) : ?>
				<li>
					<a class="envie-carte" href="<?php echo esc_url( get_term_link( $terme ) ); ?>" style="--envie-image: url('<?php echo esc_url( chalet_image_envie( $terme ) ); ?>');"<?php echo $terme->term_id === $actif ? ' aria-current="page"' : ''; ?>>
						<span class="envie-carte__titre"><?php echo esc_html( $terme->name ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</nav>
	<?php
}

/* --------------------------------------------------------------------------
 * 8 septies. IT-13 : ventes croisées (champ « Ventes croisées » de chaque produit).
 *    Fiche produit : ardoise Entrée · Plat · Dessert. Panier : ardoise de suggestions.
 *    Tous les plats ont des options : chaque suggestion renvoie à sa fiche.
 * ------------------------------------------------------------------------ */

// Les moments du repas, dans l'ordre de l'ardoise.
function chalet_moments() {
	return array(
		'entree'  => 'Entrée',
		'plat'    => 'Plat',
		'dessert' => 'Dessert',
	);
}

// Moment du repas d'un produit, d'après le slug de sa catégorie (les-entrees, les-plats, les-desserts).
function chalet_moment_plat( $produit_id ) {
	foreach ( wp_get_post_terms( $produit_id, 'product_cat', array( 'fields' => 'slugs' ) ) as $slug ) {
		foreach ( array_keys( chalet_moments() ) as $moment ) {
			if ( false !== strpos( $slug, $moment ) ) {
				return $moment;
			}
		}
	}
	return '';
}

// Produits suggérables : visibles et en stock (un plat épuisé n'est pas proposé).
function chalet_suggestions( $ids ) {
	$produits = array();
	foreach ( $ids as $id ) {
		$produit = wc_get_product( $id );
		if ( $produit && $produit->is_visible() && $produit->is_in_stock() ) {
			$produits[] = $produit;
		}
	}
	return $produits;
}

// Prix le plus bas d'un plat (les options peuvent le faire varier).
function chalet_prix_depart( $produit ) {
	return (float) ( $produit->is_type( 'variable' ) ? $produit->get_variation_price( 'min', true ) : wc_get_price_to_display( $produit ) );
}

// Lien « Choisir les options » vers la fiche d'un plat.
function chalet_bouton_suggestion( $produit ) {
	printf(
		'<a class="ardoise__bouton" href="%s">Choisir les options<span class="screen-reader-text"> : %s</span></a>',
		esc_url( $produit->get_permalink() ),
		esc_html( $produit->get_name() )
	);
}

// Fiche produit : « Composez votre repas » (plat consulté + ventes croisées des autres moments).
function chalet_ardoise_repas() {
	global $product;
	if ( ! $product ) {
		return;
	}
	$moment_actuel = chalet_moment_plat( $product->get_id() );
	if ( ! $moment_actuel ) {
		return;
	}
	$noms                    = chalet_moments();
	$cases                   = array_fill_keys( array_keys( $noms ), null );
	$cases[ $moment_actuel ] = $product;
	foreach ( chalet_suggestions( $product->get_cross_sell_ids() ) as $suggestion ) {
		$moment = chalet_moment_plat( $suggestion->get_id() );
		if ( $moment && null === $cases[ $moment ] ) {
			$cases[ $moment ] = $suggestion;
		}
	}
	$cases = array_filter( $cases );
	if ( count( $cases ) < 2 ) {
		return; // Aucune suggestion : pas d'ardoise.
	}
	$total = array_sum( array_map( 'chalet_prix_depart', $cases ) );
	?>
	<section class="ardoise ardoise--repas" aria-labelledby="ardoise-titre">
		<p class="ardoise__surtitre">Pour un repas complet</p>
		<h2 class="ardoise__titre" id="ardoise-titre">Composez votre repas</h2>
		<p class="ardoise__texte">Une entrée, un plat, un dessert : le chef vous suggère ses accords.</p>
		<ul class="ardoise__cases">
			<?php foreach ( $cases as $moment => $plat ) : ?>
				<?php $choix = $plat->get_id() === $product->get_id(); ?>
				<li class="ardoise__case<?php echo $choix ? ' ardoise__case--choix' : ''; ?>">
					<p class="ardoise__moment">
						<?php echo esc_html( $noms[ $moment ] ); ?>
						<?php if ( $choix ) : ?>
							<span class="ardoise__choix">Votre choix</span>
						<?php endif; ?>
					</p>
					<?php echo $plat->get_image( 'woocommerce_thumbnail', array( 'class' => 'ardoise__visuel', 'alt' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<h3 class="ardoise__nom"><?php echo esc_html( $plat->get_name() ); ?></h3>
					<p class="ardoise__prix"><?php echo wp_kses_post( wc_price( chalet_prix_depart( $plat ) ) ); ?></p>
					<?php if ( $choix ) : ?>
						<p class="ardoise__rappel">Choisissez vos options ci-dessus</p>
					<?php else : ?>
						<?php chalet_bouton_suggestion( $plat ); ?>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
		<p class="ardoise__total">
			<span><?php echo 3 === count( $cases ) ? 'Votre repas complet, à partir de' : 'Votre repas, à partir de'; ?></span>
			<span class="ardoise__points" aria-hidden="true"></span>
			<strong><?php echo wp_kses_post( wc_price( $total ) ); ?></strong>
		</p>
	</section>
	<?php
}
// IT-14 : affichée dans l'onglet Description (voir chalet_onglet_description()).

// Panier : « Vous oubliez quelque chose ? » (ventes croisées des plats du panier, le moment manquant d'abord).
function chalet_ardoise_panier() {
	$suggestions = chalet_suggestions( WC()->cart->get_cross_sells() ); // Sans les plats déjà dans le panier.
	if ( ! $suggestions ) {
		return;
	}
	$presents = array();
	foreach ( WC()->cart->get_cart() as $article ) {
		$presents[] = chalet_moment_plat( $article['product_id'] );
	}
	$titres  = array(
		'entree'  => array( 'Pour commencer ?', 'Il manque une entrée à votre repas : voici les accords du chef.' ),
		'dessert' => array( 'Une petite douceur pour finir ?', 'Il manque un dessert à votre repas : voici les accords du chef.' ),
		'plat'    => array( 'Et pour le plat ?', 'Il manque un plat à votre repas : voici les accords du chef.' ),
	);
	$manque  = '';
	foreach ( array_keys( $titres ) as $moment ) {
		if ( ! in_array( $moment, $presents, true ) ) {
			$manque = $moment;
			break;
		}
	}
	$texte = $manque ? $titres[ $manque ] : array( 'Les accords du chef', 'Pour compléter votre commande.' );
	// Le moment manquant en premier, puis les autres suggestions ; 3 au maximum.
	$moments = array();
	foreach ( $suggestions as $plat ) {
		$moments[ $plat->get_id() ] = chalet_moment_plat( $plat->get_id() ); // Lu une seule fois par plat.
	}
	usort(
		$suggestions,
		function ( $a, $b ) use ( $manque, $moments ) {
			return ( $moments[ $b->get_id() ] === $manque ) <=> ( $moments[ $a->get_id() ] === $manque );
		}
	);
	$suggestions = array_slice( $suggestions, 0, 3 );
	?>
	<section class="ardoise ardoise--panier" aria-labelledby="ardoise-panier-titre">
		<p class="ardoise__surtitre">Vous oubliez quelque chose ?</p>
		<h2 class="ardoise__titre" id="ardoise-panier-titre"><?php echo esc_html( $texte[0] ); ?></h2>
		<p class="ardoise__texte"><?php echo esc_html( $texte[1] ); ?></p>
		<ul class="ardoise__lignes">
			<?php foreach ( $suggestions as $plat ) : ?>
				<li class="ardoise__ligne">
					<?php echo $plat->get_image( 'woocommerce_gallery_thumbnail', array( 'class' => 'ardoise__miniature', 'alt' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<span class="ardoise__nom-ligne"><?php echo esc_html( $plat->get_name() ); ?></span>
					<span class="ardoise__points" aria-hidden="true"></span>
					<span class="ardoise__prix-ligne"><?php echo wp_kses_post( wc_price( chalet_prix_depart( $plat ) ) ); ?></span>
					<?php chalet_bouton_suggestion( $plat ); ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</section>
	<?php
}
add_action( 'woocommerce_after_cart_table', 'chalet_ardoise_panier' );

/* --------------------------------------------------------------------------
 * 8 octies. IT-14 : avis clients (onglet « Avis » de la fiche produit).
 *    Réglages WooCommerce : avis réservés aux acheteurs certifiés, note obligatoire ;
 *    Réglages › Commentaires : approbation manuelle (Produits › Avis).
 * ------------------------------------------------------------------------ */

// 5 étoiles (pleines jusqu'à la note arrondie), lues « Noté x sur 5 » par les lecteurs d'écran.
function chalet_etoiles( $note ) {
	$pleines = (int) round( $note );
	echo '<span class="etoiles" role="img" aria-label="' . esc_attr( sprintf( 'Noté %s sur 5', number_format_i18n( $note, 1 ) ) ) . '">';
	for ( $i = 1; $i <= 5; $i++ ) {
		printf(
			'<svg class="etoiles__etoile%s" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.5l2.9 6.1 6.6.8-4.9 4.6 1.3 6.6L12 17.3l-5.9 3.3 1.3-6.6-4.9-4.6 6.6-.8z"/></svg>',
			$i <= $pleines ? ' etoiles__etoile--pleine' : ''
		);
	}
	echo '</span>';
}

// Ornement « ✳ ✳ ✳ » du site officiel, au-dessus des titres sur fond caramel.
function chalet_ornement_etoiles() {
	$branches = '';
	foreach ( array( 12, 36, 60 ) as $x ) {
		$branches .= sprintf( '<path d="M%1$d 2v16M%2$d 10h16M%3$s 4.5l11 11M%4$s 4.5l-11 11"/>', $x, $x - 8, $x - 5.5, $x + 5.5 );
	}
	echo '<svg class="ornement" width="72" height="20" viewBox="0 0 72 20" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true">' . $branches . '</svg>'; // phpcs:ignore WordPress.Security.EscapeOutput
}

// Carte d'un avis (rappel de wp_list_comments) : citation, étoiles, signature « Acheteur certifié · date ».
function chalet_carte_avis( $comment, $args, $depth ) {
	$note     = (int) get_comment_meta( $comment->comment_ID, 'rating', true );
	$certifie = wc_review_is_from_verified_owner( $comment->comment_ID );
	?>
	<li <?php comment_class( 'carte-avis' ); ?> id="li-comment-<?php comment_ID(); ?>">
		<figure id="comment-<?php comment_ID(); ?>" class="carte-avis__corps">
			<blockquote class="carte-avis__citation"><?php comment_text(); ?></blockquote>
			<?php if ( $note && wc_review_ratings_enabled() ) : ?>
				<?php chalet_etoiles( $note ); ?>
			<?php endif; ?>
			<figcaption class="carte-avis__signature">
				<strong>— <?php comment_author(); ?></strong>
				<span><?php echo $certifie ? 'Acheteur certifié · ' : ''; ?><?php echo esc_html( get_comment_date( 'd/m/Y' ) ); ?></span>
			</figcaption>
			<?php if ( '0' === $comment->comment_approved ) : ?>
				<p class="carte-avis__attente">Merci ! Votre avis sera visible après validation par le restaurant.</p>
			<?php endif; ?>
		</figure>
	<?php
	// wp_list_comments() ferme le <li>.
}

/* --------------------------------------------------------------------------
 * 8 nonies. IT-X : sécurité des formulaires (inscription et avis), sans captcha.
 *    1. Champ piège (honeypot) : invisible pour un humain, rempli par les robots.
 *    2. Délai minimum : l'heure d'affichage est signée ; un envoi en moins de 3 s est refusé.
 *    S'ajoute aux protections natives : anti-flood de WordPress, avis réservés aux acheteurs, modération.
 * ------------------------------------------------------------------------ */

define( 'CHALET_DELAI_MINIMUM', 3 ); // Secondes : aucun humain ne remplit un formulaire plus vite.

// Champs ajoutés aux formulaires protégés.
function chalet_champs_antispam() {
	$heure = time();
	?>
	<p class="chalet-piege" aria-hidden="true">
		<label for="chalet_site_web">Ne pas remplir ce champ</label>
		<input type="text" id="chalet_site_web" name="chalet_site_web" value="" tabindex="-1" autocomplete="off">
	</p>
	<input type="hidden" name="chalet_heure" value="<?php echo esc_attr( $heure ); ?>">
	<input type="hidden" name="chalet_signature" value="<?php echo esc_attr( wp_hash( 'chalet-antispam|' . $heure ) ); ?>">
	<?php
}
add_action( 'woocommerce_register_form', 'chalet_champs_antispam' );                  // Mon compte › Créer un compte.
add_action( 'woocommerce_after_checkout_registration_form', 'chalet_champs_antispam' ); // Création de compte pendant la commande.
add_action( 'comment_form', 'chalet_champs_antispam' );                               // Formulaire d'avis.

// Vrai si l'envoi ressemble à celui d'un robot (champ piège rempli, heure absente ou falsifiée, envoi trop rapide).
function chalet_envoi_suspect() {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- Formulaires déjà protégés par leur propre nonce (WooCommerce / WordPress).
	$piege     = isset( $_POST['chalet_site_web'] ) ? sanitize_text_field( wp_unslash( $_POST['chalet_site_web'] ) ) : '';
	$heure     = isset( $_POST['chalet_heure'] ) ? absint( $_POST['chalet_heure'] ) : 0;
	$signature = isset( $_POST['chalet_signature'] ) ? sanitize_text_field( wp_unslash( $_POST['chalet_signature'] ) ) : '';
	// phpcs:enable
	if ( '' !== $piege || ! $heure ) {
		return true;
	}
	if ( ! hash_equals( wp_hash( 'chalet-antispam|' . $heure ), $signature ) ) {
		return true; // Heure modifiée par le robot.
	}
	return ( time() - $heure ) < CHALET_DELAI_MINIMUM;
}

// Inscription (Mon compte et commande) : erreur affichée en haut du formulaire, sans créer le compte.
function chalet_inscription_antispam( $erreurs ) {
	if ( chalet_envoi_suspect() ) {
		$erreurs->add( 'chalet_antispam', 'Votre inscription n’a pas pu être validée. Patientez quelques secondes, puis réessayez.' );
	}
	return $erreurs;
}
add_filter( 'woocommerce_registration_errors', 'chalet_inscription_antispam' );

// Avis : l'avis suspect n'est pas enregistré ; retour sur l'onglet Avis avec un message.
function chalet_avis_antispam( $donnees ) {
	if ( current_user_can( 'moderate_comments' ) ) {
		return $donnees; // Réponses du restaurant depuis le back-office (sans les champs du formulaire public).
	}
	if ( 'product' === get_post_type( $donnees['comment_post_ID'] ) && chalet_envoi_suspect() ) {
		wp_safe_redirect( add_query_arg( 'avis_bloque', 1, get_permalink( $donnees['comment_post_ID'] ) ) . '#reviews' );
		exit;
	}
	return $donnees;
}
add_filter( 'preprocess_comment', 'chalet_avis_antispam' );

// Message affiché dans le formulaire d'avis après un envoi bloqué.
function chalet_message_avis_bloque() {
	if ( isset( $_GET['avis_bloque'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Simple affichage.
		echo '<div class="woocommerce-error" role="alert">Votre avis n’a pas pu être envoyé. Patientez quelques secondes, puis réessayez.</div>';
	}
}
add_action( 'comment_form_top', 'chalet_message_avis_bloque' );

/* --------------------------------------------------------------------------
 * 8 decies. IT-15 : frais de préparation sous un seuil de commande.
 *    Seuil et montant réglés dans WooCommerce › Réglages › Général (montant à 0 = règle désactivée).
 * ------------------------------------------------------------------------ */

// Réglages ajoutés à la fin de l'onglet Général de WooCommerce.
function chalet_reglages_frais( $reglages ) {
	$reglages[] = array(
		'title' => 'Frais de préparation',
		'type'  => 'title',
		'desc'  => 'Frais fixes ajoutés au panier quand le montant des plats est inférieur au seuil. Mettre le montant à 0 pour désactiver la règle.',
		'id'    => 'chalet_frais',
	);
	$reglages[] = array(
		'title'             => 'Seuil (€)',
		'desc_tip'          => 'En dessous de ce montant de plats (après code promo), les frais s’appliquent.',
		'id'                => 'chalet_frais_seuil',
		'type'              => 'number',
		'default'           => '20',
		'custom_attributes' => array( 'min' => 0, 'step' => '0.01' ),
	);
	$reglages[] = array(
		'title'             => 'Montant des frais (€)',
		'id'                => 'chalet_frais_montant',
		'type'              => 'number',
		'default'           => '0',
		'custom_attributes' => array( 'min' => 0, 'step' => '0.01' ),
	);
	$reglages[] = array(
		'type' => 'sectionend',
		'id'   => 'chalet_frais',
	);
	return $reglages;
}
add_filter( 'woocommerce_general_settings', 'chalet_reglages_frais' );

// Montant manquant pour atteindre le seuil (0 si la règle est désactivée ou le seuil atteint).
function chalet_reste_avant_seuil( $panier ) {
	$seuil   = (float) get_option( 'chalet_frais_seuil', 20 );
	$montant = (float) get_option( 'chalet_frais_montant', 0 );
	if ( $montant <= 0 || $panier->is_empty() ) {
		return 0;
	}
	$plats = (float) $panier->get_cart_contents_total() + (float) $panier->get_cart_contents_tax(); // Après code promo.
	return max( 0, $seuil - $plats );
}

/* --------------------------------------------------------------------------
 * 8 undecies. IT-16 : page d'accueil (front-page.php), 3 derniers produits ajoutés.
 * ------------------------------------------------------------------------ */

// Grille « Nouveau à la carte » : les 3 derniers produits publiés, avec la carte produit de La Carte.
function chalet_nouveautes() {
	$produits = wc_get_products(
		array(
			'status'     => 'publish',
			'visibility' => 'catalog',
			'limit'      => 3,
			'orderby'    => array(
				'date' => 'DESC',
				'ID'   => 'DESC', // Produits créés à la même seconde : le dernier créé d'abord.
			),
			'return'     => 'ids',
		)
	);
	if ( ! $produits ) {
		return;
	}
	$GLOBALS['chalet_nouveautes'] = true; // Badge « Nouveau » sur ces cartes (chalet_badge_carte()).
	echo '<div class="woocommerce">';
	woocommerce_product_loop_start();
	foreach ( $produits as $id ) {
		$GLOBALS['post'] = get_post( $id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		setup_postdata( $GLOBALS['post'] );
		wc_get_template_part( 'content', 'product' );
	}
	woocommerce_product_loop_end();
	echo '</div>';
	wp_reset_postdata();
	$GLOBALS['chalet_nouveautes'] = false;
}

// Ajout des frais au calcul du panier : recalculé à chaque changement (quantité, plat retiré, code promo).
function chalet_frais_preparation( $panier ) {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return;
	}
	if ( chalet_reste_avant_seuil( $panier ) > 0 ) {
		$panier->add_fee( 'Frais de préparation', (float) get_option( 'chalet_frais_montant', 0 ) );
	}
}
add_action( 'woocommerce_cart_calculate_fees', 'chalet_frais_preparation' );

// Phrase d'incitation sous la ligne des frais (addition du panier).
function chalet_incitation_frais() {
	$reste = chalet_reste_avant_seuil( WC()->cart );
	if ( $reste > 0 ) {
		echo '<p class="addition__incitation">Plus que ' . wp_kses_post( wc_price( $reste ) ) . ' de plats pour ne plus payer de frais de préparation.</p>';
	}
}

/* --------------------------------------------------------------------------
 * 9. Pied de page.
 * ------------------------------------------------------------------------ */
function chalet_pied() {
	$img = get_stylesheet_directory_uri() . '/assets/img/';
	?>
	<div class="pied">
		<div class="pied__logo">
			<img src="<?php echo esc_url( $img . 'logo-taureau.png' ); ?>" alt="Le Chalet" width="119" height="109">
		</div>
		<nav aria-label="Liens du restaurant">
			<ul class="pied__liens">
				<?php foreach ( chalet_liens_restaurant() as $texte => $url ) : ?>
					<li><a class="pied__lien" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $texte ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</nav>
		<p class="pied__infos">© Le Chalet. Tél. : <a href="tel:+33555775181">05 55 77 51 81</a>. 5 rue des Filles Notre-Dame, 87000 Limoges</p>
		<?php chalet_reseaux( 'pied__reseaux' ); ?>
	</div>
	<?php
}
