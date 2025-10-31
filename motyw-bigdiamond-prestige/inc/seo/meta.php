<?php
/**
 * SEO Meta Tags
 *
 * Manages meta tags, Open Graph, Twitter Cards,
 * and other SEO-related meta information.
 *
 * @package BigDIAMOND_White_Prestige
 * @since   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add custom meta tags to head
 *
 * @since 1.0.0
 */
function bdwp_custom_meta_tags() {
	// Viewport
	echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">' . "\n";

	// Theme color for mobile browsers
	echo '<meta name="theme-color" content="#D4AF37">' . "\n";

	// Apple mobile web app
	echo '<meta name="apple-mobile-web-app-capable" content="yes">' . "\n";
	echo '<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">' . "\n";

	// Format detection
	echo '<meta name="format-detection" content="telephone=no">' . "\n";
}
add_action( 'wp_head', 'bdwp_custom_meta_tags', 1 );

/**
 * Add Twitter Card meta tags
 *
 * @since 1.0.0
 */
function bdwp_twitter_card_meta() {
	// Only add if not handled by SEO plugin
	if ( defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) ) {
		return;
	}

	$twitter_handle = '@bigdiamond'; // Update with actual handle

	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	echo '<meta name="twitter:site" content="' . esc_attr( $twitter_handle ) . '">' . "\n";

	if ( is_singular() ) {
		echo '<meta name="twitter:title" content="' . esc_attr( get_the_title() ) . '">' . "\n";

		$excerpt = get_the_excerpt();
		if ( $excerpt ) {
			echo '<meta name="twitter:description" content="' . esc_attr( bdwp_truncate( wp_strip_all_tags( $excerpt ), 200 ) ) . '">' . "\n";
		}

		if ( has_post_thumbnail() ) {
			$image_url = get_the_post_thumbnail_url( get_the_ID(), 'large' );
			echo '<meta name="twitter:image" content="' . esc_url( $image_url ) . '">' . "\n";
		}
	}
}
add_action( 'wp_head', 'bdwp_twitter_card_meta' );

/**
 * Add site verification meta tags
 *
 * @since 1.0.0
 */
function bdwp_verification_meta() {
	// Google Search Console
	$google_verification = get_option( 'bdwp_google_verification' );
	if ( $google_verification ) {
		echo '<meta name="google-site-verification" content="' . esc_attr( $google_verification ) . '">' . "\n";
	}

	// Bing Webmaster Tools
	$bing_verification = get_option( 'bdwp_bing_verification' );
	if ( $bing_verification ) {
		echo '<meta name="msvalidate.01" content="' . esc_attr( $bing_verification ) . '">' . "\n";
	}

	// Pinterest
	$pinterest_verification = get_option( 'bdwp_pinterest_verification' );
	if ( $pinterest_verification ) {
		echo '<meta name="p:domain_verify" content="' . esc_attr( $pinterest_verification ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'bdwp_verification_meta' );

/**
 * Remove unwanted meta tags
 *
 * @since 1.0.0
 */
function bdwp_remove_unwanted_meta() {
	// Remove generator meta tag
	remove_action( 'wp_head', 'wp_generator' );

	// Remove Windows Live Writer manifest
	remove_action( 'wp_head', 'wlwmanifest_link' );

	// Remove RSD link
	remove_action( 'wp_head', 'rsd_link' );

	// Remove shortlink
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );

	// Remove REST API links (keep if using REST API)
	// remove_action( 'wp_head', 'rest_output_link_wp_head' );
	// remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
}
add_action( 'init', 'bdwp_remove_unwanted_meta' );

/**
 * Add robots meta tag
 *
 * @since 1.0.0
 */
function bdwp_robots_meta() {
	$robots = array();

	// Default directives
	$robots[] = 'index';
	$robots[] = 'follow';
	$robots[] = 'max-image-preview:large';
	$robots[] = 'max-snippet:-1';
	$robots[] = 'max-video-preview:-1';

	// Noindex specific pages
	if ( is_search() || is_404() ) {
		$robots = array( 'noindex', 'follow' );
	}

	if ( is_cart() || is_checkout() || is_account_page() ) {
		$robots = array( 'noindex', 'nofollow' );
	}

	// Filter robots directives
	$robots = apply_filters( 'bdwp_robots_meta', $robots );

	if ( ! empty( $robots ) ) {
		echo '<meta name="robots" content="' . esc_attr( implode( ', ', $robots ) ) . '">' . "\n";
	}
}
// Uncomment if not using SEO plugin
// add_action( 'wp_head', 'bdwp_robots_meta', 1 );

/**
 * Add author meta tag
 *
 * @since 1.0.0
 */
function bdwp_author_meta() {
	if ( ! is_singular( 'post' ) ) {
		return;
	}

	$author_name = get_the_author();
	if ( $author_name ) {
		echo '<meta name="author" content="' . esc_attr( $author_name ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'bdwp_author_meta' );

/**
 * Add geo meta tags for local business
 *
 * @since 1.0.0
 */
function bdwp_geo_meta() {
	if ( ! is_front_page() ) {
		return;
	}

	// Krak?w coordinates
	echo '<meta name="geo.region" content="PL-MA">' . "\n";
	echo '<meta name="geo.placename" content="Krak?w">' . "\n";
	echo '<meta name="geo.position" content="50.0647;19.9450">' . "\n";
	echo '<meta name="ICBM" content="50.0647, 19.9450">' . "\n";
}
add_action( 'wp_head', 'bdwp_geo_meta' );
