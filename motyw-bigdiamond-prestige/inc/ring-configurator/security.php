<?php
/**
 * Ring Configurator Security
 *
 * Validates webhook signatures, authenticates requests,
 * and prevents unauthorized access to configurator endpoints.
 *
 * @package BigDIAMOND_White_Prestige
 * @since   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validate webhook request
 *
 * @param WP_REST_Request $request Request object.
 * @return bool|WP_Error True if valid, WP_Error otherwise.
 * @since 1.0.0
 */
function bdwp_validate_webhook_request( $request ) {
	// Check if webhook secret is configured
	$webhook_secret = get_option( 'bdwp_ring_webhook_secret' );

	if ( empty( $webhook_secret ) ) {
		// If no secret is set, allow request (development mode)
		// In production, this should return WP_Error
		return defined( 'WP_DEBUG' ) && WP_DEBUG;
	}

	// Get signature from header
	$signature = $request->get_header( 'X-BigDiamond-Signature' );

	if ( empty( $signature ) ) {
		return new WP_Error(
			'missing_signature',
			__( 'Brak podpisu w ??daniu', 'bigdiamond-white-prestige' ),
			array( 'status' => 401 )
		);
	}

	// Get request body
	$body = $request->get_body();

	// Compute expected signature
	$expected_signature = hash_hmac( 'sha256', $body, $webhook_secret );

	// Compare signatures (timing-safe)
	if ( ! hash_equals( $expected_signature, $signature ) ) {
		return new WP_Error(
			'invalid_signature',
			__( 'Nieprawid?owy podpis ??dania', 'bigdiamond-white-prestige' ),
			array( 'status' => 401 )
		);
	}

	// Check timestamp to prevent replay attacks
	$timestamp = $request->get_header( 'X-BigDiamond-Timestamp' );

	if ( $timestamp ) {
		$time_diff = abs( time() - intval( $timestamp ) );

		// Reject requests older than 5 minutes
		if ( $time_diff > 300 ) {
			return new WP_Error(
				'expired_request',
				__( '??danie wygas?o', 'bigdiamond-white-prestige' ),
				array( 'status' => 401 )
			);
		}
	}

	return true;
}

/**
 * Generate webhook signature for outgoing requests
 *
 * @param string $payload Request payload.
 * @return string HMAC signature.
 * @since 1.0.0
 */
function bdwp_generate_webhook_signature( $payload ) {
	$webhook_secret = get_option( 'bdwp_ring_webhook_secret' );

	return hash_hmac( 'sha256', $payload, $webhook_secret );
}

/**
 * Validate IP whitelist for webhooks
 *
 * @param WP_REST_Request $request Request object.
 * @return bool True if IP is allowed.
 * @since 1.0.0
 */
function bdwp_validate_webhook_ip( $request ) {
	$allowed_ips = get_option( 'bdwp_ring_webhook_allowed_ips', array() );

	if ( empty( $allowed_ips ) ) {
		return true; // No IP restriction
	}

	$client_ip = bdwp_get_client_ip();

	return in_array( $client_ip, $allowed_ips, true );
}

/**
 * Get client IP address
 *
 * @return string Client IP.
 * @since 1.0.0
 */
function bdwp_get_client_ip() {
	$ip = '';

	if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		$ip = explode( ',', $ip );
		$ip = trim( $ip[0] );
	} elseif ( isset( $_SERVER['HTTP_X_REAL_IP'] ) ) {
		$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_REAL_IP'] ) );
	} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
		$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
	}

	return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '';
}

/**
 * Rate limit webhook requests
 *
 * @param string $identifier Request identifier (IP or API key).
 * @return bool True if within rate limit.
 * @since 1.0.0
 */
function bdwp_check_webhook_rate_limit( $identifier ) {
	$transient_key = 'bdwp_webhook_rate_' . md5( $identifier );
	$requests = get_transient( $transient_key ) ?: 0;

	$max_requests = 60; // Max requests
	$time_window = 60;  // Per minute

	if ( $requests >= $max_requests ) {
		return false;
	}

	set_transient( $transient_key, $requests + 1, $time_window );

	return true;
}

/**
 * Log suspicious webhook activity
 *
 * @param WP_REST_Request $request Request object.
 * @param string          $reason  Reason for logging.
 * @since 1.0.0
 */
function bdwp_log_suspicious_webhook( $request, $reason ) {
	$log_entry = array(
		'timestamp' => time(),
		'ip'        => bdwp_get_client_ip(),
		'reason'    => $reason,
		'headers'   => $request->get_headers(),
		'body'      => $request->get_body(),
	);

	// Save to custom log table or file
	$log_file = WP_CONTENT_DIR . '/uploads/webhook-security.log';

	error_log( wp_json_encode( $log_entry ) . "\n", 3, $log_file ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log

	// Optionally send alert email to admin
	if ( get_option( 'bdwp_webhook_security_alerts' ) ) {
		wp_mail(
			get_option( 'admin_email' ),
			__( 'Podejrzana aktywno?? webhook', 'bigdiamond-white-prestige' ),
			sprintf(
				/* translators: 1: reason, 2: IP address */
				__( 'Wykryto podejrzan? aktywno?? webhook:\n\nPow?d: %1$s\nIP: %2$s\n\nCzas: %3$s', 'bigdiamond-white-prestige' ),
				$reason,
				bdwp_get_client_ip(),
				current_time( 'mysql' )
			)
		);
	}
}

/**
 * Sanitize and validate configuration data
 *
 * @param array $data Configuration data.
 * @return array|WP_Error Sanitized data or error.
 * @since 1.0.0
 */
function bdwp_validate_config_data( $data ) {
	$errors = array();

	// Validate ring 1
	if ( ! isset( $data['ring1'] ) || ! is_array( $data['ring1'] ) ) {
		$errors[] = __( 'Nieprawid?owe dane dla obr?czki 1', 'bigdiamond-white-prestige' );
	}

	// Validate ring 2
	if ( ! isset( $data['ring2'] ) || ! is_array( $data['ring2'] ) ) {
		$errors[] = __( 'Nieprawid?owe dane dla obr?czki 2', 'bigdiamond-white-prestige' );
	}

	// Validate prices
	if ( isset( $data['ring1']['price'] ) && ( ! is_numeric( $data['ring1']['price'] ) || $data['ring1']['price'] < 0 ) ) {
		$errors[] = __( 'Nieprawid?owa cena dla obr?czki 1', 'bigdiamond-white-prestige' );
	}

	if ( isset( $data['ring2']['price'] ) && ( ! is_numeric( $data['ring2']['price'] ) || $data['ring2']['price'] < 0 ) ) {
		$errors[] = __( 'Nieprawid?owa cena dla obr?czki 2', 'bigdiamond-white-prestige' );
	}

	if ( ! empty( $errors ) ) {
		return new WP_Error( 'validation_failed', implode( '; ', $errors ) );
	}

	return $data;
}
