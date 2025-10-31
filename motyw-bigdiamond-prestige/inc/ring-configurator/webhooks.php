<?php
/**
 * Ring Configurator Webhooks
 *
 * Handles incoming webhooks from external ring configurator,
 * validates data, and processes configuration results.
 *
 * @package BigDIAMOND_White_Prestige
 * @since   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register webhook REST endpoints
 *
 * @since 1.0.0
 */
function bdwp_register_ring_webhook_routes() {
	register_rest_route( 'bdwp/v1', '/rings/webhook', array(
		'methods'             => 'POST',
		'callback'            => 'bdwp_handle_ring_webhook',
		'permission_callback' => 'bdwp_validate_webhook_request',
	) );

	register_rest_route( 'bdwp/v1', '/rings/add-to-cart', array(
		'methods'             => 'POST',
		'callback'            => 'bdwp_add_rings_to_cart',
		'permission_callback' => '__return_true',
		'args'                => array(
			'config_id' => array(
				'required'          => true,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
		),
	) );
}
add_action( 'rest_api_init', 'bdwp_register_ring_webhook_routes' );

/**
 * Handle incoming ring configuration webhook
 *
 * @param WP_REST_Request $request Request object.
 * @return WP_REST_Response|WP_Error Response object.
 * @since 1.0.0
 */
function bdwp_handle_ring_webhook( $request ) {
	$body = $request->get_json_params();

	// Log webhook for debugging
	bdwp_log_webhook_request( $body );

	// Validate required fields
	$required_fields = array( 'config_id', 'ring1', 'ring2' );

	foreach ( $required_fields as $field ) {
		if ( ! isset( $body[ $field ] ) ) {
			return new WP_Error(
				'missing_field',
				sprintf(
					/* translators: %s: field name */
					__( 'Brakuj?ce pole: %s', 'bigdiamond-white-prestige' ),
					$field
				),
				array( 'status' => 400 )
			);
		}
	}

	// Sanitize and validate configuration data
	$config_data = array(
		'config_id' => sanitize_text_field( $body['config_id'] ),
		'ring1'     => bdwp_sanitize_ring_data( $body['ring1'] ),
		'ring2'     => bdwp_sanitize_ring_data( $body['ring2'] ),
		'customer'  => isset( $body['customer'] ) ? bdwp_sanitize_customer_data( $body['customer'] ) : array(),
		'timestamp' => time(),
	);

	// Store configuration temporarily (1 hour)
	set_transient( 'bdwp_ring_config_' . $config_data['config_id'], $config_data, HOUR_IN_SECONDS );

	// Optionally save to database for permanent storage
	bdwp_save_ring_configuration( $config_data );

	// Send confirmation email if customer data provided
	if ( ! empty( $config_data['customer']['email'] ) ) {
		do_action( 'bdwp_ring_configuration_completed', 0, $config_data );
	}

	return new WP_REST_Response( array(
		'success'     => true,
		'config_id'   => $config_data['config_id'],
		'redirect_url' => add_query_arg( 'config_id', $config_data['config_id'], home_url( '/konfigurator-obraczek/podsumowanie' ) ),
		'message'     => __( 'Konfiguracja zosta?a zapisana', 'bigdiamond-white-prestige' ),
	), 200 );
}

/**
 * Add configured rings to WooCommerce cart
 *
 * @param WP_REST_Request $request Request object.
 * @return WP_REST_Response|WP_Error Response object.
 * @since 1.0.0
 */
function bdwp_add_rings_to_cart( $request ) {
	$config_id = $request['config_id'];

	// Retrieve configuration
	$config_data = get_transient( 'bdwp_ring_config_' . $config_id );

	if ( ! $config_data ) {
		return new WP_Error(
			'config_not_found',
			__( 'Konfiguracja nie zosta?a znaleziona', 'bigdiamond-white-prestige' ),
			array( 'status' => 404 )
		);
	}

	// Map configuration to products or create custom line items
	$mapped_products = bdwp_map_config_to_products( $config_data );

	if ( empty( $mapped_products ) ) {
		return new WP_Error(
			'mapping_failed',
			__( 'Nie uda?o si? zmapowa? konfiguracji na produkty', 'bigdiamond-white-prestige' ),
			array( 'status' => 500 )
		);
	}

	// Add to cart
	foreach ( $mapped_products as $product_data ) {
		$cart_item_key = WC()->cart->add_to_cart(
			$product_data['product_id'],
			$product_data['quantity'],
			$product_data['variation_id'] ?? 0,
			$product_data['variation'] ?? array(),
			$product_data['cart_item_data'] ?? array()
		);

		if ( ! $cart_item_key ) {
			return new WP_Error(
				'cart_add_failed',
				__( 'Nie uda?o si? doda? produktu do koszyka', 'bigdiamond-white-prestige' ),
				array( 'status' => 500 )
			);
		}
	}

	return new WP_REST_Response( array(
		'success'      => true,
		'cart_url'     => wc_get_cart_url(),
		'items_added'  => count( $mapped_products ),
		'message'      => __( 'Obr?czki zosta?y dodane do koszyka', 'bigdiamond-white-prestige' ),
	), 200 );
}

/**
 * Sanitize ring data
 *
 * @param array $ring_data Raw ring data.
 * @return array Sanitized ring data.
 * @since 1.0.0
 */
function bdwp_sanitize_ring_data( $ring_data ) {
	return array(
		'material'    => sanitize_text_field( $ring_data['material'] ?? '' ),
		'finish'      => sanitize_text_field( $ring_data['finish'] ?? '' ),
		'width'       => floatval( $ring_data['width'] ?? 0 ),
		'thickness'   => floatval( $ring_data['thickness'] ?? 0 ),
		'size'        => sanitize_text_field( $ring_data['size'] ?? '' ),
		'stones'      => isset( $ring_data['stones'] ) && is_array( $ring_data['stones'] ) ? array_map( 'sanitize_text_field', $ring_data['stones'] ) : array(),
		'engraving'   => sanitize_text_field( $ring_data['engraving'] ?? '' ),
		'price'       => floatval( $ring_data['price'] ?? 0 ),
		'image'       => esc_url_raw( $ring_data['image'] ?? '' ),
		'specs'       => isset( $ring_data['specs'] ) && is_array( $ring_data['specs'] ) ? array_map( 'sanitize_text_field', $ring_data['specs'] ) : array(),
	);
}

/**
 * Sanitize customer data
 *
 * @param array $customer_data Raw customer data.
 * @return array Sanitized customer data.
 * @since 1.0.0
 */
function bdwp_sanitize_customer_data( $customer_data ) {
	return array(
		'email' => sanitize_email( $customer_data['email'] ?? '' ),
		'name'  => sanitize_text_field( $customer_data['name'] ?? '' ),
		'phone' => sanitize_text_field( $customer_data['phone'] ?? '' ),
	);
}

/**
 * Save ring configuration to database
 *
 * @param array $config_data Configuration data.
 * @return int|false Post ID or false on failure.
 * @since 1.0.0
 */
function bdwp_save_ring_configuration( $config_data ) {
	$post_id = wp_insert_post( array(
		'post_type'   => 'ring_configuration',
		'post_title'  => sprintf(
			/* translators: %s: configuration ID */
			__( 'Konfiguracja obr?czek %s', 'bigdiamond-white-prestige' ),
			$config_data['config_id']
		),
		'post_status' => 'publish',
		'post_content' => wp_json_encode( $config_data ),
	) );

	if ( ! is_wp_error( $post_id ) ) {
		update_post_meta( $post_id, '_config_id', $config_data['config_id'] );
		update_post_meta( $post_id, '_config_data', $config_data );

		if ( ! empty( $config_data['customer']['email'] ) ) {
			update_post_meta( $post_id, '_customer_email', $config_data['customer']['email'] );
		}

		return $post_id;
	}

	return false;
}

/**
 * Log webhook request for debugging
 *
 * @param array $data Webhook data.
 * @since 1.0.0
 */
function bdwp_log_webhook_request( $data ) {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log( 'Ring Configurator Webhook: ' . wp_json_encode( $data ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}

	// Optionally save to custom log table
	// bdwp_save_webhook_log( $data );
}

/**
 * Register ring configuration post type (for permanent storage)
 *
 * @since 1.0.0
 */
function bdwp_register_ring_configuration_post_type() {
	register_post_type( 'ring_configuration', array(
		'labels'              => array(
			'name'          => __( 'Konfiguracje obr?czek', 'bigdiamond-white-prestige' ),
			'singular_name' => __( 'Konfiguracja', 'bigdiamond-white-prestige' ),
		),
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => 'edit.php?post_type=product',
		'capability_type'     => 'post',
		'supports'            => array( 'title' ),
		'has_archive'         => false,
	) );
}
add_action( 'init', 'bdwp_register_ring_configuration_post_type' );
