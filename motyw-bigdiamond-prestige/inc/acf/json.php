<?php
/**
 * ACF JSON Save/Load Points
 *
 * Configures ACF to save field groups as JSON for version control
 * and faster field group loading.
 *
 * @package BigDIAMOND_White_Prestige
 * @since   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Set ACF JSON save point
 *
 * @param string $path Default path.
 * @return string Modified path.
 * @since 1.0.0
 */
function bdwp_acf_json_save_point( $path ) {
	return BDWP_THEME_DIR . '/acf-json';
}
add_filter( 'acf/settings/save_json', 'bdwp_acf_json_save_point' );

/**
 * Set ACF JSON load point
 *
 * @param array $paths Default paths.
 * @return array Modified paths.
 * @since 1.0.0
 */
function bdwp_acf_json_load_point( $paths ) {
	// Remove original path
	unset( $paths[0] );

	// Add theme path
	$paths[] = BDWP_THEME_DIR . '/acf-json';

	return $paths;
}
add_filter( 'acf/settings/load_json', 'bdwp_acf_json_load_point' );
