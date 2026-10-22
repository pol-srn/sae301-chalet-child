<?php
/**
 * Facture PDF du Chalet (IT-17, US-17.1 à US-17.3), d'après le modèle « Simple » de PDF Invoices & Packing Slips.
 * Modèle choisi dans WooCommerce › Factures PDF › Général › Sélectionnez un modèle : « chalet ».
 * Mise en page en tableaux : le moteur PDF (dompdf) ne gère ni flexbox ni grid.
 *
 * @package chalet-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$type     = $this->get_type();
$commande = $this->order;
$mode     = $commande->get_shipping_method();
$totaux   = $this->get_woocommerce_totals();
?>
<?php do_action( 'wpo_wcpdf_before_document', $type, $commande ); ?>

<table class="head container">
	<tr>
		<td class="header">
			<?php if ( $this->has_header_logo() ) : ?>
				<?php $this->header_logo(); ?>
			<?php else : ?>
				<p class="facture__marque"><?php $this->shop_name(); ?></p>
			<?php endif; ?>
		</td>
		<td class="shop-info">
			<div class="shop-name"><h3><?php $this->shop_name(); ?></h3></div>
			<div class="shop-address"><?php $this->shop_address(); ?></div>
			<?php if ( ! empty( $this->get_shop_phone_number() ) ) : ?>
				<div class="shop-phone-number"><?php $this->shop_phone_number(); ?></div>
			<?php endif; ?>
			<?php if ( ! empty( $this->get_shop_email_address() ) ) : ?>
				<div class="shop-email-address"><?php $this->shop_email_address(); ?></div>
			<?php endif; ?>
		</td>
	</tr>
</table>

<div class="facture__filet"></div>

<table class="facture__titre-zone">
	<tr>
		<td class="facture__titre-cellule">
			<h1 class="facture__titre"><?php $this->title(); ?></h1>
			<p class="facture__numero"><?php $this->number( $type ); ?></p>
		</td>
		<td class="facture__references">
			<table>
				<tr>
					<th>Date de facturation</th>
					<td><?php $this->date( $type ); ?></td>
				</tr>
				<tr>
					<th>Commande</th>
					<td>n° <?php $this->order_number(); ?> du <?php $this->order_date(); ?></td>
				</tr>
				<?php if ( $this->get_payment_method() ) : ?>
					<tr>
						<th>Paiement</th>
						<td><?php $this->payment_method(); ?></td>
					</tr>
				<?php endif; ?>
				<?php if ( $mode ) : ?>
					<tr>
						<th>Mode</th>
						<td><?php echo esc_html( $mode ); ?></td>
					</tr>
				<?php endif; ?>
			</table>
		</td>
	</tr>
</table>

<div class="facture__client">
	<p class="facture__surtitre">Facturé à</p>
	<p><?php $this->billing_address(); ?></p>
	<?php if ( isset( $this->settings['display_email'] ) ) : ?>
		<div class="billing-email"><?php $this->billing_email(); ?></div>
	<?php endif; ?>
	<?php if ( isset( $this->settings['display_phone'] ) ) : ?>
		<div class="billing-phone"><?php $this->billing_phone(); ?></div>
	<?php endif; ?>
</div>

<?php do_action( 'wpo_wcpdf_before_order_details', $type, $commande ); ?>

<table class="order-details facture__plats">
	<thead>
		<tr>
			<th class="product">Plat</th>
			<th class="quantity">Qté</th>
			<th class="price">Prix unitaire</th>
			<th class="total">Total</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $this->get_order_items() as $item_id => $item ) : ?>
			<tr class="<?php echo esc_attr( $item['row_class'] ); ?>">
				<td class="product">
					<p class="item-name"><?php echo esc_html( $item['name'] ); ?></p>
					<?php if ( ! empty( $item['meta'] ) ) : ?>
						<div class="item-meta"><?php echo wp_kses_post( $item['meta'] ); ?></div>
					<?php endif; ?>
				</td>
				<td class="quantity"><?php echo esc_html( $item['quantity'] ); ?></td>
				<td class="price"><?php echo esc_html( $item['single_price'] ); ?></td>
				<td class="total"><?php echo esc_html( $item['order_price'] ); ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<table class="facture__totaux">
	<?php foreach ( $totaux as $cle => $total ) : ?>
		<?php
		if ( 'payment_method' === $cle ) {
			continue; // Déjà dans les références.
		}
		?>
		<?php if ( 'shipping' === $cle ) : // « Retrait au Chalet · Gratuit », comme L'addition du site. ?>
			<tr class="shipping">
				<th><?php echo esc_html( $mode ? $mode : rtrim( $total['label'], ' :' ) ); ?></th>
				<td><?php echo (float) $commande->get_shipping_total() > 0 ? wp_kses_post( wc_price( $commande->get_shipping_total(), array( 'currency' => $commande->get_currency() ) ) ) : 'Gratuit'; ?></td>
			</tr>
		<?php else : ?>
			<tr class="<?php echo esc_attr( $cle ); ?>">
				<th><?php echo esc_html( rtrim( $total['label'], ' :' ) ); ?></th>
				<td><?php echo esc_html( $total['value'] ); ?></td>
			</tr>
		<?php endif; ?>
	<?php endforeach; ?>
</table>
<p class="facture__tva">TVA non applicable, art. 293 B du CGI</p>

<?php if ( $this->get_shipping_notes() ) : ?>
	<div class="facture__note">
		<p class="facture__surtitre">Votre mot pour la cuisine</p>
		<div class="facture__note-texte">« <?php echo wp_kses_post( wp_strip_all_tags( $this->get_shipping_notes() ) ); ?> »</div>
	</div>
<?php endif; ?>

<?php do_action( 'wpo_wcpdf_after_order_details', $type, $commande ); ?>

<htmlpagefooter name="docFooter"><!-- requis pour le moteur mPDF -->
	<div id="footer">
		<p class="facture__merci">Merci et à bientôt au Chalet !</p>
		<?php $this->footer(); ?>
	</div>
</htmlpagefooter>

<?php do_action( 'wpo_wcpdf_after_document', $type, $commande ); ?>
