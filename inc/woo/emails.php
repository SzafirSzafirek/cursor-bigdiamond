<?php
/**
 * WooCommerce Email Customization
 *
 * Registers custom email templates, modifies email content,
 * and adds custom transactional emails.
 *
 * @package BigDIAMOND_White_Prestige
 * @since   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register custom email templates
 *
 * @param array $emails Email classes.
 * @return array Modified email classes.
 * @since 1.0.0
 */
function bdwp_register_custom_emails( $emails ) {
	// Include custom email classes
	require_once BDWP_INC_DIR . '/woo/emails/class-wc-email-custom-design-intake.php';
	require_once BDWP_INC_DIR . '/woo/emails/class-wc-email-custom-design-update.php';
	require_once BDWP_INC_DIR . '/woo/emails/class-wc-email-ring-configuration-summary.php';

	$emails['WC_Email_Custom_Design_Intake'] = new WC_Email_Custom_Design_Intake();
	$emails['WC_Email_Custom_Design_Update'] = new WC_Email_Custom_Design_Update();
	$emails['WC_Email_Ring_Configuration_Summary'] = new WC_Email_Ring_Configuration_Summary();

	return $emails;
}
add_filter( 'woocommerce_email_classes', 'bdwp_register_custom_emails' );

/**
 * Customize email header
 *
 * @param string $email_heading Email heading.
 * @param WC_Email $email Email object.
 * @since 1.0.0
 */
function bdwp_email_header( $email_heading, $email ) {
	// Add custom styling or modify header
	return $email_heading;
}
add_filter( 'woocommerce_email_heading_customer_new_account', 'bdwp_email_header', 10, 2 );

/**
 * Add custom content before email footer
 *
 * @param string   $email_id Email ID.
 * @param WC_Order $order    Order object.
 * @since 1.0.0
 */
function bdwp_email_footer_content( $email_id, $order ) {
	if ( ! in_array( $email_id, array( 'customer_processing_order', 'customer_completed_order' ), true ) ) {
		return;
	}

	?>
	<div style="margin-top: 40px; padding: 30px; background-color: #FAFAFA; border-radius: 8px; text-align: center;">
		<h3 style="color: #2F2F2F; margin-bottom: 16px; font-family: 'Playfair Display', Georgia, serif;">
			<?php esc_html_e( 'Dzi?kujemy za zaufanie BigDIAMOND', 'bigdiamond-white-prestige' ); ?>
		</h3>
		<p style="color: #737373; margin-bottom: 20px;">
			<?php esc_html_e( 'Masz pytania? Nasz zesp?? jest do Twojej dyspozycji', 'bigdiamond-white-prestige' ); ?>
		</p>
		<div style="margin-bottom: 20px;">
			<a href="tel:+48123456789" style="color: #D4AF37; text-decoration: none; font-weight: 600;">
				?? +48 123 456 789
			</a>
			<span style="margin: 0 10px; color: #D4D4D4;">|</span>
			<a href="mailto:kontakt@bigdiamond.pl" style="color: #D4AF37; text-decoration: none; font-weight: 600;">
				?? kontakt@bigdiamond.pl
			</a>
		</div>
		<div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid #E5E5E5;">
			<p style="color: #737373; font-size: 14px; margin-bottom: 8px;">
				<?php esc_html_e( 'Atelier BigDIAMOND', 'bigdiamond-white-prestige' ); ?>
			</p>
			<p style="color: #A3A3A3; font-size: 12px;">
				<?php echo esc_html( get_option( 'bdwp_street', 'ul. Floria?ska 1' ) ); ?><br>
				<?php echo esc_html( get_option( 'bdwp_postal', '31-000' ) ); ?> Krak?w
			</p>
		</div>
	</div>
	<?php
}
add_action( 'woocommerce_email_before_order_table', 'bdwp_email_footer_content', 20, 2 );

/**
 * Add care instructions to completed order emails
 *
 * @param WC_Order $order       Order object.
 * @param bool     $sent_to_admin Whether sent to admin.
 * @param bool     $plain_text   Whether plain text email.
 * @param WC_Email $email       Email object.
 * @since 1.0.0
 */
function bdwp_care_instructions_email( $order, $sent_to_admin, $plain_text, $email ) {
	if ( 'customer_completed_order' !== $email->id || $sent_to_admin ) {
		return;
	}

	?>
	<div style="margin-top: 40px; padding: 30px; background-color: #F9F9F9; border-left: 4px solid #D4AF37;">
		<h3 style="color: #2F2F2F; margin-bottom: 16px;">
			<?php esc_html_e( '?? Piel?gnacja Twojej bi?uterii', 'bigdiamond-white-prestige' ); ?>
		</h3>
		<ul style="color: #525252; line-height: 1.8; padding-left: 20px;">
			<li><?php esc_html_e( 'Przechowuj w oryginalnym opakowaniu, osobno od innych przedmiot?w', 'bigdiamond-white-prestige' ); ?></li>
			<li><?php esc_html_e( 'Unikaj kontaktu z chemikaliami, perfumami i kosmetykami', 'bigdiamond-white-prestige' ); ?></li>
			<li><?php esc_html_e( 'Czy?? delikatnie mi?kk? ?ciereczk?', 'bigdiamond-white-prestige' ); ?></li>
			<li><?php esc_html_e( 'Zalecamy profesjonalne czyszczenie raz w roku', 'bigdiamond-white-prestige' ); ?></li>
		</ul>
		<p style="margin-top: 16px; color: #737373; font-size: 14px;">
			<?php esc_html_e( 'Oferujemy bezp?atne czyszczenie i konserwacj? w naszym Atelier.', 'bigdiamond-white-prestige' ); ?>
		</p>
	</div>
	<?php
}
add_action( 'woocommerce_email_after_order_table', 'bdwp_care_instructions_email', 10, 4 );

/**
 * Customize "From" name and email
 *
 * @param string $from_name From name.
 * @return string Modified from name.
 * @since 1.0.0
 */
function bdwp_email_from_name( $from_name ) {
	return 'BigDIAMOND Krak?w';
}
add_filter( 'woocommerce_email_from_name', 'bdwp_email_from_name' );

/**
 * Customize email subject lines
 *
 * @param string   $subject Email subject.
 * @param WC_Order $order   Order object.
 * @return string Modified subject.
 * @since 1.0.0
 */
function bdwp_custom_email_subject( $subject, $order ) {
	// Add order number to all subjects
	if ( $order && method_exists( $order, 'get_order_number' ) ) {
		$subject = str_replace( '{order_number}', $order->get_order_number(), $subject );
	}

	return $subject;
}
add_filter( 'woocommerce_email_subject_customer_processing_order', 'bdwp_custom_email_subject', 10, 2 );
add_filter( 'woocommerce_email_subject_customer_completed_order', 'bdwp_custom_email_subject', 10, 2 );

/**
 * Add preheader text to emails
 *
 * @param string $email_heading Email heading.
 * @param string $email_id      Email ID.
 * @return string
 * @since 1.0.0
 */
function bdwp_email_preheader( $email_heading, $email_id ) {
	$preheaders = array(
		'customer_new_account'       => __( 'Witaj w BigDIAMOND! Twoje konto zosta?o utworzone.', 'bigdiamond-white-prestige' ),
		'customer_processing_order'  => __( 'Przyj?li?my Twoje zam?wienie i zaraz rozpoczniemy realizacj?.', 'bigdiamond-white-prestige' ),
		'customer_completed_order'   => __( 'Twoje zam?wienie zosta?o zrealizowane i wys?ane.', 'bigdiamond-white-prestige' ),
		'customer_refunded_order'    => __( 'Zwrot ?rodk?w zosta? zrealizowany.', 'bigdiamond-white-prestige' ),
	);

	if ( isset( $preheaders[ $email_id ] ) ) {
		$preheader = '<div style="display:none;font-size:1px;color:#ffffff;line-height:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;">';
		$preheader .= esc_html( $preheaders[ $email_id ] );
		$preheader .= '</div>';

		return $preheader . $email_heading;
	}

	return $email_heading;
}
add_filter( 'woocommerce_email_header_text', 'bdwp_email_preheader', 10, 2 );

/**
 * Modify email styles
 *
 * @param string $css CSS styles.
 * @return string Modified CSS.
 * @since 1.0.0
 */
function bdwp_email_styles( $css ) {
	$custom_css = "
		#wrapper {
			background-color: #FAFAFA;
			font-family: 'Inter', Arial, sans-serif;
		}

		#template_header {
			background-color: #2F2F2F;
			border-top: 4px solid #D4AF37;
			padding: 40px 20px;
		}

		#template_header h1 {
			color: #FFFFFF;
			font-family: 'Playfair Display', Georgia, serif;
			font-size: 28px;
			font-weight: 600;
		}

		#template_body {
			background-color: #FFFFFF;
		}

		#template_footer {
			background-color: #F5F5F5;
			border-top: 1px solid #E5E5E5;
		}

		a {
			color: #D4AF37;
			text-decoration: underline;
		}

		.button {
			background-color: #D4AF37;
			color: #FFFFFF;
			padding: 12px 24px;
			text-decoration: none;
			border-radius: 6px;
			display: inline-block;
			font-weight: 600;
		}

		.button:hover {
			background-color: #C19F2F;
		}
	";

	return $css . $custom_css;
}
add_filter( 'woocommerce_email_styles', 'bdwp_email_styles' );

/**
 * Add logo to email header
 *
 * @since 1.0.0
 */
function bdwp_email_header_logo() {
	$logo_url = BDWP_ASSETS_URI . '/images/logo-email.png';

	echo '<div style="text-align: center; margin-bottom: 30px;">';
	echo '<img src="' . esc_url( $logo_url ) . '" alt="BigDIAMOND" style="max-width: 200px; height: auto;" />';
	echo '</div>';
}
add_action( 'woocommerce_email_header', 'bdwp_email_header_logo', 5 );

/**
 * Trigger custom design intake email
 *
 * @param int $project_id Project ID.
 * @since 1.0.0
 */
function bdwp_trigger_custom_design_intake_email( $project_id ) {
	$mailer = WC()->mailer();
	$emails = $mailer->get_emails();

	if ( isset( $emails['WC_Email_Custom_Design_Intake'] ) ) {
		$emails['WC_Email_Custom_Design_Intake']->trigger( $project_id );
	}
}

/**
 * Trigger ring configuration summary email
 *
 * @param int   $order_id Order ID.
 * @param array $config_data Configuration data.
 * @since 1.0.0
 */
function bdwp_trigger_ring_config_email( $order_id, $config_data ) {
	$mailer = WC()->mailer();
	$emails = $mailer->get_emails();

	if ( isset( $emails['WC_Email_Ring_Configuration_Summary'] ) ) {
		$emails['WC_Email_Ring_Configuration_Summary']->trigger( $order_id, $config_data );
	}
}
