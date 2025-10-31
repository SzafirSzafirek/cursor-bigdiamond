<?php
/**
 * Theme Setup and Configuration
 *
 * Registers theme supports, navigation menus, widget areas,
 * and initial configuration hooks.
 *
 * @package BigDIAMOND_White_Prestige
 * @since   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Setup
 *
 * @since 1.0.0
 */
function bdwp_theme_setup() {
	// Load text domain for translations
	load_child_theme_textdomain( 'bigdiamond-white-prestige', BDWP_THEME_DIR . '/languages' );

	// Add theme support for various features
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'style',
		'script',
	) );

	// Add custom image sizes for jewelry products
	add_image_size( 'bdwp-product-thumb', 400, 400, true );      // Square thumbnail
	add_image_size( 'bdwp-product-medium', 800, 800, true );     // Medium detail view
	add_image_size( 'bdwp-product-large', 1200, 1200, true );    // High-res zoom
	add_image_size( 'bdwp-hero', 1920, 800, true );              // Hero images
	add_image_size( 'bdwp-banner', 1200, 400, true );            // Category banners

	// Register navigation menus
	register_nav_menus( array(
		'primary'   => __( 'Primary Navigation', 'bigdiamond-white-prestige' ),
		'footer'    => __( 'Footer Navigation', 'bigdiamond-white-prestige' ),
		'legal'     => __( 'Legal Links', 'bigdiamond-white-prestige' ),
		'account'   => __( 'Account Menu', 'bigdiamond-white-prestige' ),
	) );

	// Add editor styles
	add_editor_style( 'assets/css/editor-style.css' );
}
add_action( 'after_setup_theme', 'bdwp_theme_setup' );

/**
 * Register Widget Areas
 *
 * @since 1.0.0
 */
function bdwp_widgets_init() {
	// Shop sidebar
	register_sidebar( array(
		'name'          => __( 'Shop Sidebar', 'bigdiamond-white-prestige' ),
		'id'            => 'shop-sidebar',
		'description'   => __( 'Filters and widgets for shop pages', 'bigdiamond-white-prestige' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="widget-title font-display">',
		'after_title'   => '</h3>',
	) );

	// Footer columns
	for ( $i = 1; $i <= 4; $i++ ) {
		register_sidebar( array(
			'name'          => sprintf( __( 'Footer Column %d', 'bigdiamond-white-prestige' ), $i ),
			'id'            => 'footer-' . $i,
			'description'   => sprintf( __( 'Footer column %d widgets', 'bigdiamond-white-prestige' ), $i ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h4 class="widget-title">',
			'after_title'   => '</h4>',
		) );
	}
}
add_action( 'widgets_init', 'bdwp_widgets_init' );

/**
 * Content Width
 *
 * @since 1.0.0
 */
if ( ! isset( $content_width ) ) {
	$content_width = 1280;
}

/**
 * Body Classes
 *
 * @param array $classes Existing body classes.
 * @return array Modified body classes.
 * @since 1.0.0
 */
function bdwp_body_classes( $classes ) {
	// Add luxury theme identifier
	$classes[] = 'bd-white-prestige';

	// Add page-specific classes
	if ( is_singular( 'product' ) ) {
		$classes[] = 'single-product-layout';
	}

	if ( is_shop() || is_product_category() || is_product_tag() ) {
		$classes[] = 'shop-layout';
	}

	return $classes;
}
add_filter( 'body_class', 'bdwp_body_classes' );
