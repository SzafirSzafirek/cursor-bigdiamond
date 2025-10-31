<?php
/**
 * WooCommerce Checkout Customization
 *
 * Customizes checkout fields, validation, RODO compliance,
 * and checkout flow for optimal UX.
 *
 * @package BigDIAMOND_White_Prestige
 * @since   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customize checkout fields
 *
 * @param array $fields Checkout fields.
 * @return array Modified fields.
 * @since 1.0.0
 */
function bdwp_custom_checkout_fields( $fields ) {
	// Remove unnecessary fields
	unset( $fields['billing']['billing_company'] );
	unset( $fields['billing']['billing_address_2'] );
	unset( $fields['shipping']['shipping_company'] );
	unset( $fields['shipping']['shipping_address_2'] );

	// Make phone required
	$fields['billing']['billing_phone']['required'] = true;
	$fields['billing']['billing_phone']['priority'] = 25;

	// Reorder fields for better flow
	$fields['billing']['billing_first_name']['priority'] = 10;
	$fields['billing']['billing_last_name']['priority'] = 20;
	$fields['billing']['billing_phone']['priority'] = 25;
	$fields['billing']['billing_email']['priority'] = 30;
	$fields['billing']['billing_postcode']['priority'] = 65;
	$fields['billing']['billing_city']['priority'] = 70;
	$fields['billing']['billing_address_1']['priority'] = 75;

	// Update labels and placeholders
	$fields['billing']['billing_first_name']['label'] = __( 'Imi?', 'bigdiamond-white-prestige' );
	$fields['billing']['billing_first_name']['placeholder'] = __( 'Wpisz imi?', 'bigdiamond-white-prestige' );

	$fields['billing']['billing_last_name']['label'] = __( 'Nazwisko', 'bigdiamond-white-prestige' );
	$fields['billing']['billing_last_name']['placeholder'] = __( 'Wpisz nazwisko', 'bigdiamond-white-prestige' );

	$fields['billing']['billing_phone']['label'] = __( 'Telefon', 'bigdiamond-white-prestige' );
	$fields['billing']['billing_phone']['placeholder'] = __( '+48 000 000 000', 'bigdiamond-white-prestige' );

	$fields['billing']['billing_email']['label'] = __( 'Adres e-mail', 'bigdiamond-white-prestige' );
	$fields['billing']['billing_email']['placeholder'] = __( 'twoj@email.pl', 'bigdiamond-white-prestige' );

	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'bdwp_custom_checkout_fields' );

/**
 * Add RODO compliance checkboxes
 *
 * @param array $checkout Checkout object.
 * @since 1.0.0
 */
function bdwp_add_rodo_checkboxes( $checkout ) {
	echo '<div class="rodo-compliance-section mt-6 p-6 bg-bd-cream rounded-lg">';

	woocommerce_form_field( 'terms_and_conditions', array(
		'type'     => 'checkbox',
		'class'    => array( 'form-row-wide', 'rodo-checkbox' ),
		'label'    => sprintf(
			/* translators: %s: terms and conditions link */
			__( 'Akceptuj? <a href="%s" target="_blank" class="text-bd-gold underline">regulamin sklepu</a> oraz <a href="%s" target="_blank" class="text-bd-gold underline">polityk? prywatno?ci</a>', 'bigdiamond-white-prestige' ),
			esc_url( get_permalink( wc_terms_and_conditions_page_id() ) ),
			esc_url( get_privacy_policy_url() )
		),
		'required' => true,
	), $checkout->get_value( 'terms_and_conditions' ) );

	woocommerce_form_field( 'marketing_consent', array(
		'type'     => 'checkbox',
		'class'    => array( 'form-row-wide', 'marketing-checkbox' ),
		'label'    => __( 'Wyra?am zgod? na otrzymywanie informacji handlowych drog? elektroniczn? (opcjonalnie)', 'bigdiamond-white-prestige' ),
		'required' => false,
	), $checkout->get_value( 'marketing_consent' ) );

	echo '</div>';
}
add_action( 'woocommerce_checkout_after_terms_and_conditions', 'bdwp_add_rodo_checkboxes' );

/**
 * Validate RODO checkboxes
 *
 * @param array  $data   Posted checkout data.
 * @param object $errors WP_Error object.
 * @since 1.0.0
 */
function bdwp_validate_rodo_checkboxes( $data, $errors ) {
	if ( empty( $_POST['terms_and_conditions'] ) ) {
		$errors->add( 'terms', __( 'Musisz zaakceptowa? regulamin i polityk? prywatno?ci.', 'bigdiamond-white-prestige' ) );
	}
}
add_action( 'woocommerce_after_checkout_validation', 'bdwp_validate_rodo_checkboxes', 10, 2 );

/**
 * Save custom checkout fields
 *
 * @param int $order_id Order ID.
 * @since 1.0.0
 */
function bdwp_save_custom_checkout_fields( $order_id ) {
	if ( ! empty( $_POST['marketing_consent'] ) ) {
		update_post_meta( $order_id, '_marketing_consent', 'yes' );

		// Add customer to marketing list (integrate with newsletter plugin)
		$order = wc_get_order( $order_id );
		$email = $order->get_billing_email();

		// Example: Add to newsletter
		do_action( 'bdwp_add_to_newsletter', $email );
	}
}
add_action( 'woocommerce_checkout_update_order_meta', 'bdwp_save_custom_checkout_fields' );

/**
 * Add estimated delivery time to checkout
 *
 * @since 1.0.0
 */
function bdwp_display_estimated_delivery() {
	$business_days = 3; // Default delivery time
	$estimated_date = bdwp_calculate_delivery_date( $business_days );

	echo '<div class="estimated-delivery bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">';
	echo '<div class="flex items-start gap-3">';
	echo '<svg class="w-6 h-6 text-blue-600 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>';
	echo '<div>';
	echo '<h3 class="font-semibold text-lg mb-1">' . esc_html__( 'Szacowany czas dostawy', 'bigdiamond-white-prestige' ) . '</h3>';
	echo '<p class="text-bd-gray-700">';
	printf(
		/* translators: %s: estimated delivery date */
		esc_html__( 'Twoje zam?wienie zostanie dostarczone do %s', 'bigdiamond-white-prestige' ),
		'<strong>' . esc_html( $estimated_date ) . '</strong>'
	);
	echo '</p>';
	echo '<p class="text-sm text-bd-gray-600 mt-2">';
	echo esc_html__( 'Darmowa, ubezpieczona przesy?ka kurierska', 'bigdiamond-white-prestige' );
	echo '</p>';
	echo '</div>';
	echo '</div>';
	echo '</div>';
}
add_action( 'woocommerce_checkout_before_customer_details', 'bdwp_display_estimated_delivery' );

/**
 * Calculate estimated delivery date
 *
 * @param int $business_days Number of business days.
 * @return string Formatted date.
 * @since 1.0.0
 */
function bdwp_calculate_delivery_date( $business_days ) {
	$current_date = new DateTime();
	$days_added = 0;

	while ( $days_added < $business_days ) {
		$current_date->modify( '+1 day' );

		// Skip weekends
		if ( $current_date->format( 'N' ) < 6 ) {
			$days_added++;
		}
	}

	return $current_date->format( 'd.m.Y' );
}

/**
 * Add trust badges to checkout
 *
 * @since 1.0.0
 */
function bdwp_checkout_trust_badges() {
	$badges = array(
		array(
			'icon'  => 'check',
			'title' => __( 'Bezpieczne p?atno?ci', 'bigdiamond-white-prestige' ),
		),
		array(
			'icon'  => 'check',
			'title' => __( '30 dni na zwrot', 'bigdiamond-white-prestige' ),
		),
		array(
			'icon'  => 'check',
			'title' => __( 'Certyfikat autentyczno?ci', 'bigdiamond-white-prestige' ),
		),
	);

	echo '<div class="trust-badges flex flex-wrap gap-4 mb-6">';

	foreach ( $badges as $badge ) {
		echo '<div class="trust-badge flex items-center gap-2">';
		echo '<span class="text-green-600">' . bdwp_get_icon( $badge['icon'], 'w-5 h-5' ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<span class="text-sm font-medium">' . esc_html( $badge['title'] ) . '</span>';
		echo '</div>';
	}

	echo '</div>';
}
add_action( 'woocommerce_review_order_before_payment', 'bdwp_checkout_trust_badges' );

/**
 * Customize "Place Order" button text
 *
 * @param string $button_text Button text.
 * @return string Modified button text.
 * @since 1.0.0
 */
function bdwp_place_order_button_text( $button_text ) {
	return __( 'Z??? zam?wienie', 'bigdiamond-white-prestige' );
}
add_filter( 'woocommerce_order_button_text', 'bdwp_place_order_button_text' );

/**
 * Add custom order notes placeholder
 *
 * @param array $fields Checkout fields.
 * @return array Modified fields.
 * @since 1.0.0
 */
function bdwp_custom_order_notes( $fields ) {
	$fields['order']['order_comments']['placeholder'] = __( 'Uwagi do zam?wienia (opcjonalnie)', 'bigdiamond-white-prestige' );
	$fields['order']['order_comments']['label'] = __( 'Dodatkowe informacje', 'bigdiamond-white-prestige' );

	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'bdwp_custom_order_notes' );

/**
 * Optimize checkout page styles
 *
 * @since 1.0.0
 */
function bdwp_checkout_styles() {
	if ( ! is_checkout() ) {
		return;
	}

	?>
	<style>
		.woocommerce-checkout .woocommerce-form-coupon-toggle {
			display: none;
		}

		.woocommerce-checkout .form-row input.input-text,
		.woocommerce-checkout .form-row select {
			border-radius: 0.375rem;
			border-color: #e5e5e5;
			padding: 0.75rem 1rem;
		}

		.woocommerce-checkout .form-row input.input-text:focus,
		.woocommerce-checkout .form-row select:focus {
			border-color: #D4AF37;
			outline: none;
			box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
		}

		.woocommerce-checkout #place_order {
			background-color: #D4AF37;
			border: none;
			border-radius: 0.5rem;
			padding: 1rem 2rem;
			font-size: 1.125rem;
			font-weight: 600;
			transition: all 0.3s;
		}

		.woocommerce-checkout #place_order:hover {
			background-color: #C19F2F;
			transform: translateY(-2px);
			box-shadow: 0 4px 12px rgba(212, 175, 55, 0.3);
		}
	</style>
	<?php
}
add_action( 'wp_head', 'bdwp_checkout_styles' );

/**
 * Add gift wrapping option
 *
 * @since 1.0.0
 */
function bdwp_gift_wrapping_option() {
	woocommerce_form_field( 'gift_wrapping', array(
		'type'     => 'checkbox',
		'class'    => array( 'form-row-wide' ),
		'label'    => __( 'Dodaj eleganckie opakowanie prezentowe (+20 z?)', 'bigdiamond-white-prestige' ),
		'required' => false,
	) );
}
add_action( 'woocommerce_before_order_notes', 'bdwp_gift_wrapping_option' );

/**
 * Calculate gift wrapping fee
 *
 * @since 1.0.0
 */
function bdwp_gift_wrapping_fee() {
	if ( isset( $_POST['post_data'] ) ) {
		parse_str( $_POST['post_data'], $post_data );
	} else {
		$post_data = $_POST; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}

	if ( isset( $post_data['gift_wrapping'] ) && $post_data['gift_wrapping'] ) {
		WC()->cart->add_fee( __( 'Opakowanie prezentowe', 'bigdiamond-white-prestige' ), 20 );
	}
}
add_action( 'woocommerce_cart_calculate_fees', 'bdwp_gift_wrapping_fee' );

/**
 * Save gift wrapping to order meta
 *
 * @param int $order_id Order ID.
 * @since 1.0.0
 */
function bdwp_save_gift_wrapping( $order_id ) {
	if ( ! empty( $_POST['gift_wrapping'] ) ) {
		update_post_meta( $order_id, '_gift_wrapping', 'yes' );
	}
}
add_action( 'woocommerce_checkout_update_order_meta', 'bdwp_save_gift_wrapping' );
