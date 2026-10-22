<?php
/**
 * Widget « Chalet : filtre de La Carte » (IT-18, US-18.1 à US-18.3).
 * Un widget par critère, placé dans la zone « Filtres de La Carte » (Apparence › Widgets) :
 * tri, budget, ou un attribut global de produit (Taille, Régime…).
 * Les champs font partie du formulaire du panneau (chalet_panneau_filtres()) : plusieurs choix,
 * puis « Voir les plats ». L'URL est ensuite mise au format de WooCommerce (chalet_url_filtres()).
 * Les nombres de plats sont recalculés à chaque choix par assets/js/filtres.js.
 *
 * @package chalet-child
 */

defined( 'ABSPATH' ) || exit;

class Chalet_Widget_Filtre extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'chalet_filtre',
			'Chalet : filtre de La Carte',
			array( 'description' => 'Tri, budget ou attribut (Taille, Régime) dans le panneau « Filtrer et trier ».' )
		);
	}

	// Critères proposés : tri, budget et chaque attribut global de produit.
	private function criteres() {
		$criteres = array(
			'tri'    => 'Trier par',
			'budget' => 'Budget (curseur de prix)',
		);
		foreach ( wc_get_attribute_taxonomies() as $attribut ) {
			$criteres[ wc_attribute_taxonomy_name( $attribut->attribute_name ) ] = 'Attribut : ' . $attribut->attribute_label;
		}
		return $criteres;
	}

	public function widget( $args, $instance ) {
		$critere = $instance['critere'] ?? '';
		$titre   = $instance['titre'] ?? '';
		if ( ! $critere ) {
			return;
		}
		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<fieldset class="filtre__choix"><legend class="filtre__titre">' . esc_html( $titre ) . '</legend>';
		if ( 'tri' === $critere ) {
			$this->tri();
		} elseif ( 'budget' === $critere ) {
			$this->budget();
		} elseif ( taxonomy_exists( $critere ) ) {
			$this->attribut( $critere );
		}
		echo '</fieldset>';
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput
	}

	private function tri() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Lecture du tri de l'URL.
		$actuel = isset( $_GET['orderby'] ) ? wc_clean( wp_unslash( $_GET['orderby'] ) ) : '';
		$choix  = array(
			''           => 'L’ordre de la carte',
			'price'      => 'Prix croissant',
			'price-desc' => 'Prix décroissant',
		);
		foreach ( $choix as $valeur => $libelle ) {
			printf(
				'<label class="filtre__ligne filtre__ligne--radio"><input type="radio" class="filtre__input" name="orderby" value="%1$s"%2$s><span class="filtre__nom">%3$s</span></label>',
				esc_attr( $valeur ),
				checked( $actuel, $valeur, false ),
				esc_html( $libelle )
			);
		}
	}

	// Curseur à deux poignées (deux champs range superposés), bornes = prix le plus bas et le plus haut de la carte.
	private function budget() {
		$bornes = chalet_bornes_prix();
		$prix   = chalet_prix_actuel();
		$min    = $prix ? max( $bornes[0], $prix[0] ) : $bornes[0];
		$max    = $prix ? min( $bornes[1], $prix[1] ) : $bornes[1];
		?>
		<div class="filtre__prix">
			<p class="filtre__montants">De <output class="filtre__montant-min"><?php echo esc_html( $min ); ?> €</output> à <output class="filtre__montant-max"><?php echo esc_html( $max ); ?> €</output></p>
			<div class="filtre__curseur">
				<span class="filtre__piste" aria-hidden="true"></span>
				<input type="range" class="filtre__plage filtre__plage--min" name="prix_min" min="<?php echo esc_attr( $bornes[0] ); ?>" max="<?php echo esc_attr( $bornes[1] ); ?>" step="1" value="<?php echo esc_attr( $min ); ?>" aria-label="Prix minimum (€)">
				<input type="range" class="filtre__plage filtre__plage--max" name="prix_max" min="<?php echo esc_attr( $bornes[0] ); ?>" max="<?php echo esc_attr( $bornes[1] ); ?>" step="1" value="<?php echo esc_attr( $max ); ?>" aria-label="Prix maximum (€)">
			</div>
		</div>
		<?php
	}

	private function attribut( $taxonomie ) {
		$termes = get_terms(
			array(
				'taxonomy'   => $taxonomie,
				'hide_empty' => true,
				'orderby'    => 'term_id', // Ordre de création (Petite, Bonne, Grosse faim), comme les envies de l'IT-12.
			)
		);
		if ( is_wp_error( $termes ) ) {
			return;
		}
		$choisis = WC_Query::get_layered_nav_chosen_attributes();
		$coches  = $choisis[ $taxonomie ]['terms'] ?? array();
		$nom     = 'filter_' . wc_attribute_taxonomy_slug( $taxonomie ) . '[]';
		foreach ( $termes as $terme ) {
			printf(
				'<label class="filtre__ligne"><input type="checkbox" class="filtre__input" name="%1$s" value="%2$s"%3$s><span class="filtre__nom">%4$s</span><span class="filtre__points" aria-hidden="true"></span><span class="filtre__nombre">%5$s</span></label>',
				esc_attr( $nom ),
				esc_attr( $terme->slug ),
				checked( in_array( $terme->slug, $coches, true ), true, false ),
				esc_html( $terme->name ),
				esc_html( sprintf( _n( '%d plat', '%d plats', $terme->count, 'chalet-child' ), $terme->count ) )
			);
		}
	}

	public function form( $instance ) {
		$titre   = $instance['titre'] ?? '';
		$critere = $instance['critere'] ?? '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'titre' ) ); ?>">Titre :</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'titre' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'titre' ) ); ?>" type="text" value="<?php echo esc_attr( $titre ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'critere' ) ); ?>">Critère :</label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'critere' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'critere' ) ); ?>">
				<?php foreach ( $this->criteres() as $valeur => $libelle ) : ?>
					<option value="<?php echo esc_attr( $valeur ); ?>"<?php selected( $critere, $valeur ); ?>><?php echo esc_html( $libelle ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php
		return '';
	}

	public function update( $nouveau, $ancien ) {
		return array(
			'titre'   => sanitize_text_field( $nouveau['titre'] ?? '' ),
			'critere' => array_key_exists( $nouveau['critere'] ?? '', $this->criteres() ) ? $nouveau['critere'] : '',
		);
	}
}
