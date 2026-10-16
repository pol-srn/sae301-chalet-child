<?php
/**
 * Thème enfant Le Chalet (Storefront) – SAé 3.01.
 *
 * IT-2 : apparence générale (US-2.1), page La Carte (US-2.3), fiche produit simple (US-2.4).
 * IT-3 : niveaux de stock (US-3.1), sélecteur d'options en boutons (US-3.2, US-3.3).
 * IT-4 : champs personnalisés Origine et Allergènes créés avec SCF (US-4.1), étiquette du plat avant le prix (US-4.3, US-4.4).
 * IT-5 : page Panier « Vos plats » + « L'addition » (US-5.3), choix du mode de retrait (US-5.2, US-5.4).
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
	if ( is_shop() ) {
		return 'La Carte';
	}
	if ( is_product_category() ) {
		return single_term_title( '', false );
	}
	if ( is_product() ) {
		$categories = get_the_terms( get_the_ID(), 'product_cat' );
		return ( $categories && ! is_wp_error( $categories ) ) ? $categories[0]->name : 'La Carte';
	}
	if ( is_singular() ) {
		return get_the_title();
	}
	return get_bloginfo( 'name' );
}

function chalet_liens_restaurant() {
	return array(
		'Accueil'          => 'https://lechaletlimoges.com/',
		'La Carte'         => wc_get_page_permalink( 'shop' ),
		'Notre histoire'   => 'https://lechaletlimoges.com/about',
		'Contact'          => 'https://lechaletlimoges.com/contact',
		'Mentions légales' => 'https://lechaletlimoges.com/mentions-l%C3%A9gales',
	);
}

function chalet_compteur_panier() {
	$nombre = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
	return '<span class="entete__compteur">' . absint( $nombre ) . '</span>';
}

function chalet_entete() {
	$img   = get_stylesheet_directory_uri() . '/assets/img/';
	$balise = is_product() ? 'p' : 'h1'; // Sur la fiche, le h1 est le nom du produit.
	?>
	<div class="entete">
		<div class="entete__barre">
			<p class="entete__horaires"><span class="entete__badge">Horaires</span> 12h-14h, 19h30-22h30</p>
			<ul class="entete__actions">
				<li><a class="entete__lien entete__lien--reservation" href="https://lechaletlimoges.com/">Réservation</a></li>
				<li>
					<a class="entete__lien entete__lien--panier" href="<?php echo esc_url( wc_get_cart_url() ); ?>">
						<span class="screen-reader-text">Panier</span><?php echo chalet_compteur_panier(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</a>
				</li>
				<li>
					<details class="entete__menu">
						<summary class="entete__lien entete__lien--menu"><span class="screen-reader-text">Menu</span></summary>
						<?php
						// Menu « Primaire » de Storefront (Apparence > Menus). S'il n'existe pas encore : liste par défaut.
						wp_nav_menu(
							array(
								'theme_location' => 'primary',
								'container'      => false,
								'menu_class'     => 'entete__sous-menu',
								'depth'          => 1,
								'fallback_cb'    => 'chalet_menu_par_defaut',
							)
						);
						?>
					</details>
				</li>
			</ul>
		</div>
		<a class="entete__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<img src="<?php echo esc_url( $img . 'logo-le-chalet.png' ); ?>" alt="Le Chalet – retour à la carte" width="286" height="42">
		</a>
		<<?php echo $balise; // phpcs:ignore ?> class="entete__titre"><?php echo esc_html( chalet_titre_page() ); ?></<?php echo $balise; // phpcs:ignore ?>>
	</div>
	<?php
}

// Menu burger par défaut : les pages du site (La Carte, chaque catégorie, Panier).
function chalet_menu_par_defaut() {
	$liens      = array( 'La Carte' => wc_get_page_permalink( 'shop' ) );
	$categories = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
			'orderby'    => 'term_id',
		)
	);
	if ( ! is_wp_error( $categories ) ) {
		foreach ( $categories as $categorie ) {
			$liens[ $categorie->name ] = get_term_link( $categorie );
		}
	}
	$liens['Panier'] = wc_get_cart_url();

	echo '<ul class="entete__sous-menu">';
	foreach ( $liens as $texte => $url ) {
		echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $texte ) . '</a></li>';
	}
	echo '</ul>';
}

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
	unset( $onglets['reviews'], $onglets['additional_information'] );
	return $onglets;
}
add_filter( 'woocommerce_product_tabs', 'chalet_onglets_produit', 98 );
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
 * 8. IT-5 : panier.
 * ------------------------------------------------------------------------ */
function chalet_reglages_panier() {
	remove_action( 'storefront_page', 'storefront_page_header', 10 );
	remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
}
add_action( 'init', 'chalet_reglages_panier' );

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

function chalet_info_retrait() {
	return get_option( 'woocommerce_store_address' ) . ', du mardi au samedi aux heures d’ouverture';
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
		<ul class="pied__reseaux">
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
	</div>
	<?php
}
