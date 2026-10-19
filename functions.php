<?php
/**
 * Thème enfant Le Chalet (Storefront) – SAé 3.01.
 *
 * IT-2 : apparence générale (US-2.1), page La Carte (US-2.3), fiche produit simple (US-2.4).
 * IT-3 : niveaux de stock (US-3.1), sélecteur d'options en boutons (US-3.2, US-3.3).
 * IT-4 : champs personnalisés Origine et Allergènes créés avec SCF (US-4.1), étiquette du plat avant le prix (US-4.3, US-4.4).
 * IT-5 : page Panier « Vos plats » + « L'addition » (US-5.3), choix du mode de retrait (US-5.2, US-5.4).
 * IT-6 : page Commande « Bon de commande » + « L'addition », instructions de retrait modifiables (US-6.1, US-6.2).
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
	if ( is_order_received_page() ) {
		return 'Merci !';
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
	$liens['Mon compte'] = wc_get_page_permalink( 'myaccount' ); // IT-7.
	$liens['Panier']     = wc_get_cart_url();

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
 * 8. IT-5 et IT-6 : panier et commande.
 * ------------------------------------------------------------------------ */
function chalet_reglages_panier() {
	remove_action( 'storefront_page', 'storefront_page_header', 10 );
	remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
	remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
	remove_action( 'woocommerce_checkout_terms_and_conditions', 'wc_checkout_privacy_policy_text', 20 );
	remove_action( 'woocommerce_thankyou', 'woocommerce_order_details_table', 10 ); // Remplacé par l'addition de thankyou.php.
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
	?>
	<div class="retrait">
		<h2 class="retrait__titre"><?php echo esc_html( chalet_reglage_retrait( 'retrait_titre', 'Instructions pour le retrait' ) ); ?></h2>
		<ol class="retrait__etapes">
			<?php foreach ( chalet_etapes_retrait() as $etape ) : ?>
				<li><strong><?php echo esc_html( $etape['titre'] ); ?></strong><?php echo esc_html( $etape['texte'] ); ?></li>
			<?php endforeach; ?>
		</ol>
		<p class="retrait__infos"><?php echo esc_html( chalet_adresse_retrait() . ' · ' . chalet_horaires_retrait() ); ?></p>
	</div>
	<?php
}
add_action( 'woocommerce_before_checkout_form', 'chalet_bande_retrait', 5 );

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
	$ids = array( 'customer_on_hold_order', 'customer_processing_order', 'customer_completed_order' );
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
