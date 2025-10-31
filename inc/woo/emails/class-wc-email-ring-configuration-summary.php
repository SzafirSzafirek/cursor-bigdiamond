<?php
/**
 * Ring Configuration Summary Email
 *
 * Sent after customer completes ring configuration.
 *
 * @package BigDIAMOND_White_Prestige
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Email_Ring_Configuration_Summary' ) ) :

	/**
	 * Ring Configuration Summary Email Class
	 */
	class WC_Email_Ring_Configuration_Summary extends WC_Email {

		/**
		 * Configuration data
		 *
		 * @var array
		 */
		public $config_data;

		/**
		 * Constructor
		 */
		public function __construct() {
			$this->id             = 'ring_configuration_summary';
			$this->customer_email = true;
			$this->title          = __( 'Podsumowanie konfiguracji obr?czek', 'bigdiamond-white-prestige' );
			$this->description    = __( 'Email z podsumowaniem skonfigurowanych obr?czek.', 'bigdiamond-white-prestige' );
			$this->template_html  = 'emails/ring-configuration-summary.php';
			$this->template_plain = 'emails/plain/ring-configuration-summary.php';
			$this->template_base  = BDWP_THEME_DIR . '/woocommerce/';
			$this->placeholders   = array(
				'{order_number}'   => '',
				'{customer_name}'  => '',
			);

			// Triggers
			add_action( 'bdwp_ring_configuration_completed', array( $this, 'trigger' ), 10, 2 );

			parent::__construct();
		}

		/**
		 * Get email subject
		 *
		 * @return string
		 */
		public function get_default_subject() {
			return __( 'Twoje skonfigurowane obr?czki - Zam?wienie #{order_number}', 'bigdiamond-white-prestige' );
		}

		/**
		 * Get email heading
		 *
		 * @return string
		 */
		public function get_default_heading() {
			return __( 'Podsumowanie konfiguracji obr?czek', 'bigdiamond-white-prestige' );
		}

		/**
		 * Trigger the email
		 *
		 * @param int   $order_id Order ID.
		 * @param array $config_data Configuration data.
		 */
		public function trigger( $order_id, $config_data = array() ) {
			$this->setup_locale();

			if ( $order_id && ! is_a( $order_id, 'WC_Order' ) ) {
				$this->object = wc_get_order( $order_id );
			}

			if ( is_a( $this->object, 'WC_Order' ) ) {
				$this->recipient = $this->object->get_billing_email();
				$this->config_data = $config_data;

				$this->placeholders['{order_number}'] = $this->object->get_order_number();
				$this->placeholders['{customer_name}'] = $this->object->get_billing_first_name();
			}

			if ( $this->is_enabled() && $this->get_recipient() ) {
				$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
			}

			$this->restore_locale();
		}

		/**
		 * Get email HTML content
		 *
		 * @return string
		 */
		public function get_content_html() {
			return wc_get_template_html(
				$this->template_html,
				array(
					'order'          => $this->object,
					'config_data'    => $this->config_data,
					'email_heading'  => $this->get_heading(),
					'additional_content' => $this->get_additional_content(),
					'sent_to_admin'  => false,
					'plain_text'     => false,
					'email'          => $this,
				),
				'',
				$this->template_base
			);
		}

		/**
		 * Get email plain content
		 *
		 * @return string
		 */
		public function get_content_plain() {
			return wc_get_template_html(
				$this->template_plain,
				array(
					'order'          => $this->object,
					'config_data'    => $this->config_data,
					'email_heading'  => $this->get_heading(),
					'additional_content' => $this->get_additional_content(),
					'sent_to_admin'  => false,
					'plain_text'     => true,
					'email'          => $this,
				),
				'',
				$this->template_base
			);
		}
	}

endif;
