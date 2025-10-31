<?php
/**
 * Custom Design Update Email
 *
 * Sent when a custom design project status is updated.
 *
 * @package BigDIAMOND_White_Prestige
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Email_Custom_Design_Update' ) ) :

	/**
	 * Custom Design Update Email Class
	 */
	class WC_Email_Custom_Design_Update extends WC_Email {

		/**
		 * Constructor
		 */
		public function __construct() {
			$this->id             = 'custom_design_update';
			$this->customer_email = true;
			$this->title          = __( 'Projekt na zam?wienie - Aktualizacja statusu', 'bigdiamond-white-prestige' );
			$this->description    = __( 'Email wysy?any przy zmianie statusu projektu.', 'bigdiamond-white-prestige' );
			$this->template_html  = 'emails/custom-design-update.php';
			$this->template_plain = 'emails/plain/custom-design-update.php';
			$this->template_base  = BDWP_THEME_DIR . '/woocommerce/';
			$this->placeholders   = array(
				'{project_number}' => '',
				'{status_label}'   => '',
			);

			// Triggers
			add_action( 'bdwp_custom_design_status_changed', array( $this, 'trigger' ), 10, 2 );

			parent::__construct();
		}

		/**
		 * Get email subject
		 *
		 * @return string
		 */
		public function get_default_subject() {
			return __( 'Aktualizacja projektu #{project_number} - {status_label}', 'bigdiamond-white-prestige' );
		}

		/**
		 * Get email heading
		 *
		 * @return string
		 */
		public function get_default_heading() {
			return __( 'Aktualizacja statusu projektu', 'bigdiamond-white-prestige' );
		}

		/**
		 * Trigger the email
		 *
		 * @param int    $project_id Project ID.
		 * @param string $new_status New status.
		 */
		public function trigger( $project_id, $new_status ) {
			$this->setup_locale();

			if ( $project_id ) {
				$this->object = get_post( $project_id );
				$this->recipient = get_post_meta( $project_id, '_customer_email', true );

				$this->placeholders['{project_number}'] = $project_id;
				$this->placeholders['{status_label}']   = bdwp_get_project_status_label( $new_status );
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
					'project'        => $this->object,
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
					'project'        => $this->object,
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
