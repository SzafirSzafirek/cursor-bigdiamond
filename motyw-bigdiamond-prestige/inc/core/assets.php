<?php
/**
 * Asset Management
 *
 * Handles enqueuing of styles and scripts with proper dependencies,
 * versioning, and conditional loading for optimal performance.
 *
 * @package BigDIAMOND_White_Prestige
 * @since   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue Theme Styles
 *
 * @since 1.0.0
 */
function bdwp_enqueue_styles() {
	$version = BDWP_VERSION;

	// Parent theme style (GeneratePress)
	wp_enqueue_style(
		'generatepress',
		get_template_directory_uri() . '/style.css',
		array(),
		$version
	);

	// Main theme stylesheet
	wp_enqueue_style(
		'bdwp-main',
		BDWP_ASSETS_URI . '/css/main.css',
		array( 'generatepress' ),
		$version,
		'all'
	);

	// WooCommerce styles (conditional)
	if ( class_exists( 'WooCommerce' ) ) {
		wp_enqueue_style(
			'bdwp-woocommerce',
			BDWP_ASSETS_URI . '/css/woocommerce.css',
			array( 'bdwp-main' ),
			$version
		);
	}

	// Custom Design module styles (conditional)
	if ( is_singular( 'custom_project' ) || is_post_type_archive( 'custom_project' ) ) {
		wp_enqueue_style(
			'bdwp-custom-design',
			BDWP_ASSETS_URI . '/css/custom-design.css',
			array( 'bdwp-main' ),
			$version
		);
	}

	// Google Fonts - Inter & Playfair Display
	wp_enqueue_style(
		'bdwp-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap',
		array(),
		null
	);
}
add_action( 'wp_enqueue_scripts', 'bdwp_enqueue_styles' );

/**
 * Enqueue Theme Scripts
 *
 * @since 1.0.0
 */
function bdwp_enqueue_scripts() {
	$version = BDWP_VERSION;

	// Main theme JavaScript
	wp_enqueue_script(
		'bdwp-main',
		BDWP_ASSETS_URI . '/js/main.js',
		array(),
		$version,
		true
	);

	// WooCommerce scripts (conditional)
	if ( class_exists( 'WooCommerce' ) ) {
		wp_enqueue_script(
			'bdwp-woo',
			BDWP_ASSETS_URI . '/js/woo.js',
			array( 'jquery', 'bdwp-main' ),
			$version,
			true
		);

		// Localize script with WooCommerce data
		wp_localize_script( 'bdwp-woo', 'bdwpWoo', array(
			'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
			'nonce'         => wp_create_nonce( 'bdwp-woo-nonce' ),
			'currencySymbol' => get_woocommerce_currency_symbol(),
			'i18n'          => array(
				'addedToCart' => __( 'Dodano do koszyka', 'bigdiamond-white-prestige' ),
				'error'       => __( 'Wyst?pi? b??d', 'bigdiamond-white-prestige' ),
			),
		) );
	}

	// Ring Configurator integration
	if ( is_page( 'konfigurator-obraczek' ) || is_singular( 'product' ) ) {
		wp_enqueue_script(
			'bdwp-configurator',
			BDWP_ASSETS_URI . '/js/configurator.js',
			array( 'bdwp-main' ),
			$version,
			true
		);

		wp_localize_script( 'bdwp-configurator', 'bdwpConfigurator', array(
			'apiUrl'        => rest_url( 'bdwp/v1/rings' ),
			'nonce'         => wp_create_nonce( 'wp_rest' ),
			'externalUrl'   => get_option( 'bdwp_ring_configurator_url', '' ),
		) );
	}

	// Comment reply script
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'bdwp_enqueue_scripts' );

/**
 * Add async/defer attributes to scripts
 *
 * @param string $tag    The script tag.
 * @param string $handle The script handle.
 * @param string $src    The script source URL.
 * @return string Modified script tag.
 * @since 1.0.0
 */
function bdwp_async_scripts( $tag, $handle, $src ) {
	// Scripts to load asynchronously
	$async_scripts = array(
		'bdwp-woo',
		'bdwp-configurator',
	);

	// Scripts to defer
	$defer_scripts = array(
		'bdwp-main',
	);

	if ( in_array( $handle, $async_scripts, true ) ) {
		return str_replace( '<script ', '<script async ', $tag );
	}

	if ( in_array( $handle, $defer_scripts, true ) ) {
		return str_replace( '<script ', '<script defer ', $tag );
	}

	return $tag;
}
add_filter( 'script_loader_tag', 'bdwp_async_scripts', 10, 3 );

/**
 * Add preconnect for external resources
 *
 * @since 1.0.0
 */
function bdwp_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = array(
			'href' => 'https://fonts.googleapis.com',
			'crossorigin',
		);
		$urls[] = array(
			'href' => 'https://fonts.gstatic.com',
			'crossorigin',
		);
	}

	return $urls;
}
add_filter( 'wp_resource_hints', 'bdwp_resource_hints', 10, 2 );

/**
 * Disable jQuery Migrate in production
 *
 * @param WP_Scripts $scripts WP_Scripts object.
 * @since 1.0.0
 */
function bdwp_remove_jquery_migrate( $scripts ) {
	if ( ! is_admin() && isset( $scripts->registered['jquery'] ) ) {
		$script = $scripts->registered['jquery'];

		if ( $script->deps ) {
			$script->deps = array_diff( $script->deps, array( 'jquery-migrate' ) );
		}
	}
}
add_action( 'wp_default_scripts', 'bdwp_remove_jquery_migrate' );

/**
 * Add fetchpriority attribute to hero images
 *
 * @param string $attr       Attributes for the image markup.
 * @param WP_Post $attachment Image attachment post.
 * @return string Modified attributes.
 * @since 1.0.0
 */
function bdwp_hero_image_fetchpriority( $attr, $attachment ) {
	// Add fetchpriority="high" to first product image or hero images
	if ( is_singular( 'product' ) && doing_action( 'woocommerce_before_single_product_summary' ) ) {
		$attr .= ' fetchpriority="high"';
	}

	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'bdwp_hero_image_fetchpriority', 10, 2 );
