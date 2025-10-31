<?php
/**
 * Caching Optimization
 *
 * Browser caching headers, object caching, and transient management
 * for improved performance and reduced server load.
 *
 * @package BigDIAMOND_White_Prestige
 * @since   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Set browser cache headers for static assets
 *
 * @since 1.0.0
 */
function bdwp_browser_cache_headers() {
	if ( is_admin() || is_user_logged_in() ) {
		return;
	}

	// Cache static pages for 1 hour
	if ( ! is_singular( 'product' ) && ! is_cart() && ! is_checkout() && ! is_account_page() ) {
		header( 'Cache-Control: public, max-age=3600' );
	}

	// Don't cache WooCommerce pages
	if ( is_cart() || is_checkout() || is_account_page() ) {
		header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		header( 'Pragma: no-cache' );
	}
}
add_action( 'send_headers', 'bdwp_browser_cache_headers' );

/**
 * Cache product query results
 *
 * @param array $args Query arguments.
 * @return array Products or cached results.
 * @since 1.0.0
 */
function bdwp_cache_product_query( $args ) {
	$cache_key = 'bdwp_products_' . md5( wp_json_encode( $args ) );
	$cached = get_transient( $cache_key );

	if ( false !== $cached ) {
		return $cached;
	}

	$query = new WP_Query( $args );
	$products = $query->posts;

	// Cache for 1 hour
	set_transient( $cache_key, $products, HOUR_IN_SECONDS );

	return $products;
}

/**
 * Clear product cache on product update
 *
 * @param int $product_id Product ID.
 * @since 1.0.0
 */
function bdwp_clear_product_cache( $product_id ) {
	if ( get_post_type( $product_id ) !== 'product' ) {
		return;
	}

	// Clear all product query caches
	global $wpdb;
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_bdwp_products_%'" );
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_bdwp_products_%'" );
}
add_action( 'save_post_product', 'bdwp_clear_product_cache' );
add_action( 'woocommerce_update_product', 'bdwp_clear_product_cache' );

/**
 * Cache navigation menus
 *
 * @param string $menu_html Menu HTML.
 * @param object $args Menu arguments.
 * @return string Cached or fresh menu HTML.
 * @since 1.0.0
 */
function bdwp_cache_nav_menu( $menu_html, $args ) {
	if ( is_user_logged_in() ) {
		return $menu_html;
	}

	$cache_key = 'bdwp_nav_menu_' . md5( serialize( $args ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize

	$cached_menu = get_transient( $cache_key );

	if ( false !== $cached_menu ) {
		return $cached_menu;
	}

	// Cache menu for 24 hours
	set_transient( $cache_key, $menu_html, DAY_IN_SECONDS );

	return $menu_html;
}
add_filter( 'wp_nav_menu', 'bdwp_cache_nav_menu', 10, 2 );

/**
 * Clear navigation cache on menu update
 *
 * @param int $menu_id Menu ID.
 * @since 1.0.0
 */
function bdwp_clear_nav_cache( $menu_id ) {
	global $wpdb;
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_bdwp_nav_menu_%'" );
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_bdwp_nav_menu_%'" );
}
add_action( 'wp_update_nav_menu', 'bdwp_clear_nav_cache' );

/**
 * Cache widget output
 *
 * @param array  $instance Widget instance.
 * @param object $widget   Widget object.
 * @param array  $args     Widget arguments.
 * @return array Modified instance.
 * @since 1.0.0
 */
function bdwp_cache_widget_output( $instance, $widget, $args ) {
	if ( is_admin() || is_user_logged_in() ) {
		return $instance;
	}

	$cache_key = 'bdwp_widget_' . $widget->id;
	$cached = get_transient( $cache_key );

	if ( false !== $cached ) {
		echo $cached; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		return false; // Skip normal widget output
	}

	ob_start();
	return $instance;
}
// Uncomment if widget caching is needed
// add_filter( 'widget_display_callback', 'bdwp_cache_widget_output', 10, 3 );

/**
 * Implement fragment caching for expensive operations
 *
 * @param string $key     Cache key.
 * @param callable $callback Callback function.
 * @param int    $expiration Expiration time in seconds.
 * @return mixed Cached or fresh data.
 * @since 1.0.0
 */
function bdwp_fragment_cache( $key, $callback, $expiration = HOUR_IN_SECONDS ) {
	$cached = get_transient( 'bdwp_fragment_' . $key );

	if ( false !== $cached ) {
		return $cached;
	}

	$data = call_user_func( $callback );

	set_transient( 'bdwp_fragment_' . $key, $data, $expiration );

	return $data;
}

/**
 * Cache product attributes
 *
 * @param int $product_id Product ID.
 * @return array Product attributes.
 * @since 1.0.0
 */
function bdwp_cache_product_attributes( $product_id ) {
	return bdwp_fragment_cache(
		'product_attrs_' . $product_id,
		function() use ( $product_id ) {
			return bdwp_get_product_attributes( $product_id );
		},
		DAY_IN_SECONDS
	);
}

/**
 * Optimize WooCommerce cart fragments
 *
 * @param array $fragments Cart fragments.
 * @return array Modified fragments.
 * @since 1.0.0
 */
function bdwp_optimize_cart_fragments( $fragments ) {
	// Reduce cart fragment refresh rate
	if ( ! defined( 'BDWP_CART_FRAGMENT_REFRESH' ) ) {
		define( 'BDWP_CART_FRAGMENT_REFRESH', DAY_IN_SECONDS );
	}

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'bdwp_optimize_cart_fragments' );

/**
 * Disable cart fragmentation on non-cart pages
 *
 * @since 1.0.0
 */
function bdwp_disable_cart_fragmentation() {
	if ( is_admin() || is_cart() || is_checkout() ) {
		return;
	}

	// Disable cart fragments on non-shop pages
	if ( ! is_shop() && ! is_product() && ! is_product_category() && ! is_product_tag() ) {
		wp_dequeue_script( 'wc-cart-fragments' );
	}
}
add_action( 'wp_enqueue_scripts', 'bdwp_disable_cart_fragmentation', 100 );

/**
 * Clean expired transients daily
 *
 * @since 1.0.0
 */
function bdwp_clean_expired_transients() {
	global $wpdb;

	$time = time();
	$sql = "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_bdwp_%' AND option_value < {$time}";

	$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
}
add_action( 'wp_scheduled_delete', 'bdwp_clean_expired_transients' );
