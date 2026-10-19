<?php
/**
 * IT-9 / US-9.2 : e-mail « Votre commande est prête », envoyé quand la commande passe en « Prête à retirer ».
 * Réglable comme les e-mails natifs dans WooCommerce › Réglages › E-mails.
 *
 * @package chalet-child
 */

defined( 'ABSPATH' ) || exit;

class Chalet_Email_Commande_Prete extends WC_Email {

	public function __construct() {
		$this->id             = 'customer_ready_order';
		$this->customer_email = true;
		$this->title          = 'Commande prête à retirer';
		$this->description    = 'Envoyé au client quand sa commande passe au statut « Prête à retirer ».';
		$this->template_html  = 'emails/customer-ready-order.php';
		$this->template_plain = 'emails/plain/customer-ready-order.php';
		$this->placeholders   = array(
			'{order_date}'   => '',
			'{order_number}' => '',
		);

		add_action( 'woocommerce_order_status_prete', array( $this, 'trigger' ), 10, 2 );

		parent::__construct();
	}

	public function get_default_subject() {
		return 'Votre commande n° {order_number} est prête';
	}

	public function get_default_heading() {
		return 'Votre commande est prête';
	}

	public function get_default_additional_content() {
		return 'Bon appétit !';
	}

	public function trigger( $order_id, $order = false ) {
		$this->setup_locale();

		if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
			$order = wc_get_order( $order_id );
		}
		if ( is_a( $order, 'WC_Order' ) ) {
			$this->object                         = $order;
			$this->recipient                      = $order->get_billing_email();
			$this->placeholders['{order_date}']   = wc_format_datetime( $order->get_date_created() );
			$this->placeholders['{order_number}'] = $order->get_order_number();
		}

		if ( $this->is_enabled() && $this->get_recipient() ) {
			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}

		$this->restore_locale();
	}

	public function get_content_html() {
		return wc_get_template_html( $this->template_html, $this->arguments( false ) );
	}

	public function get_content_plain() {
		return wc_get_template_html( $this->template_plain, $this->arguments( true ) );
	}

	private function arguments( $texte ) {
		return array(
			'order'              => $this->object,
			'email_heading'      => $this->get_heading(),
			'additional_content' => $this->get_additional_content(),
			'sent_to_admin'      => false,
			'plain_text'         => $texte,
			'email'              => $this,
		);
	}
}
