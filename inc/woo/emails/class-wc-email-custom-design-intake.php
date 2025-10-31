<?php
/**
 * Custom Design Intake Email
 *
 * Sent when a new custom design project is submitted.
 *
 * @package BigDIAMOND_White_Prestige
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Email_Custom_Design_Intake' ) ) :

	/**
	 * Custom Design Intake Email Class
	 */
	class WC_Email_Custom_Design_Intake extends WC_Email {

		/**
		 * Constructor
		 */
		public function __construct() {
			$this->id             = 'custom_design_intake';
			$this->customer_email = true;
			$this->title          = __( 'Projekt na zam?wienie - Potwierdzenie', 'bigdiamond-white-prestige' );
			$this->description    = __( 'Email wysy?any do klienta po z?o?eniu projektu na zam?wienie.', 'bigdiamond-white-prestige' );
			$this->template_html  = 'emails/custom-design-intake.php';
			$this->template_plain = 'emails/plain/custom-design-intake.php';
			$this->template_base  = BDWP_THEME_DIR . '/woocommerce/';
			$this->placeholders   = array(
				'{project_number}' => '',
				'{customer_name}'  => '',
			);

			// Triggers
			add_action( 'bdwp_custom_design_intake_notification', array( $this, 'trigger' ), 10, 1 );

			parent::__construct();
		}

		/**
		 * Get email subject
		 *
		 * @return string
		 */
		public function get_default_subject() {
			return __( 'Dzi?kujemy za zg?oszenie projektu #{project_number}', 'bigdiamond-white-prestige' );
		}

		/**
		 * Get email heading
		 *
		 * @return string
		 */
		public function get_default_heading() {
			return __( 'Potwierdzenie przyj?cia projektu', 'bigdiamond-white-prestige' );
		}

		/**
		 * Trigger the email
		 *
		 * @param int $project_id Project ID.
		 */
		public function trigger( $project_id ) {
			$this->setup_locale();

			if ( $project_id ) {
				$this->object = get_post( $project_id );
				$this->recipient = get_post_meta( $project_id, '_customer_email', true );

				$this->placeholders['{project_number}'] = $project_id;
				$this->placeholders['{customer_name}']  = get_post_meta( $project_id, '_customer_name', true );
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
