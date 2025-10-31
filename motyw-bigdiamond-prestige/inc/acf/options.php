<?php
/**
 * ACF Options Pages
 *
 * Registers ACF options pages for theme settings,
 * business information, and configurator integration.
 *
 * @package BigDIAMOND_White_Prestige
 * @since   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register ACF options pages
 *
 * @since 1.0.0
 */
function bdwp_acf_options_pages() {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}

	// Main theme options
	acf_add_options_page( array(
		'page_title' => __( 'Ustawienia motywu', 'bigdiamond-white-prestige' ),
		'menu_title' => __( 'Ustawienia motywu', 'bigdiamond-white-prestige' ),
		'menu_slug'  => 'theme-settings',
		'capability' => 'manage_options',
		'icon_url'   => 'dashicons-admin-generic',
		'position'   => 60,
	) );

	// Business information
	acf_add_options_sub_page( array(
		'page_title'  => __( 'Informacje o firmie', 'bigdiamond-white-prestige' ),
		'menu_title'  => __( 'Firma', 'bigdiamond-white-prestige' ),
		'parent_slug' => 'theme-settings',
	) );

	// Ring configurator settings
	acf_add_options_sub_page( array(
		'page_title'  => __( 'Konfigurator obr?czek', 'bigdiamond-white-prestige' ),
		'menu_title'  => __( 'Konfigurator', 'bigdiamond-white-prestige' ),
		'parent_slug' => 'theme-settings',
	) );
}
add_action( 'acf/init', 'bdwp_acf_options_pages' );
