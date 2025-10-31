<?php
/**
 * BigDIAMOND White Prestige Theme Functions
 *
 * Main entry point for theme functionality. Implements modular architecture
 * with organized includes for WooCommerce, SEO, performance, and custom features.
 *
 * @package BigDIAMOND_White_Prestige
 * @since   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Constants
 */
define( 'BDWP_VERSION', '1.0.0' );
define( 'BDWP_THEME_DIR', get_stylesheet_directory() );
define( 'BDWP_THEME_URI', get_stylesheet_directory_uri() );
define( 'BDWP_INC_DIR', BDWP_THEME_DIR . '/inc' );
define( 'BDWP_ASSETS_URI', BDWP_THEME_URI . '/assets' );

/**
 * Theme Support and Setup
 */
require_once BDWP_INC_DIR . '/core/setup.php';
require_once BDWP_INC_DIR . '/core/assets.php';
require_once BDWP_INC_DIR . '/core/helpers.php';

/**
 * WooCommerce Integration
 * Loaded conditionally if WooCommerce is active
 */
if ( class_exists( 'WooCommerce' ) ) {
	require_once BDWP_INC_DIR . '/woo/setup.php';
	require_once BDWP_INC_DIR . '/woo/catalog.php';
	require_once BDWP_INC_DIR . '/woo/product.php';
	require_once BDWP_INC_DIR . '/woo/pricing.php';
	require_once BDWP_INC_DIR . '/woo/checkout.php';
	require_once BDWP_INC_DIR . '/woo/account.php';
	require_once BDWP_INC_DIR . '/woo/schema.php';
	require_once BDWP_INC_DIR . '/woo/emails.php';
}

/**
 * SEO & Schema.org
 */
require_once BDWP_INC_DIR . '/seo/seo-product.php';
require_once BDWP_INC_DIR . '/seo/schema.php';
require_once BDWP_INC_DIR . '/seo/meta.php';
require_once BDWP_INC_DIR . '/seo/sitemap.php';

/**
 * Performance Optimization
 */
require_once BDWP_INC_DIR . '/performance/critical-css.php';
require_once BDWP_INC_DIR . '/performance/lazy-load.php';
require_once BDWP_INC_DIR . '/performance/cache.php';

/**
 * Custom Design Projects Module
 */
require_once BDWP_INC_DIR . '/custom-design/post-type.php';
require_once BDWP_INC_DIR . '/custom-design/fields.php';
require_once BDWP_INC_DIR . '/custom-design/workflow.php';
require_once BDWP_INC_DIR . '/custom-design/emails.php';
require_once BDWP_INC_DIR . '/custom-design/rest.php';

/**
 * Ring Configurator Integration
 */
require_once BDWP_INC_DIR . '/ring-configurator/routes.php';
require_once BDWP_INC_DIR . '/ring-configurator/webhooks.php';
require_once BDWP_INC_DIR . '/ring-configurator/mapping.php';
require_once BDWP_INC_DIR . '/ring-configurator/security.php';

/**
 * ACF Configuration
 */
if ( function_exists( 'acf_add_options_page' ) ) {
	require_once BDWP_INC_DIR . '/acf/options.php';
	require_once BDWP_INC_DIR . '/acf/json.php';
}

/**
 * Content Modules (Blocks, Shortcodes)
 */
require_once BDWP_INC_DIR . '/content/blocks.php';
require_once BDWP_INC_DIR . '/content/shortcodes.php';

/**
 * Theme Initialization Hook
 *
 * Use this hook for third-party integrations or custom initialization logic.
 *
 * @since 1.0.0
 */
do_action( 'bdwp_init' );
