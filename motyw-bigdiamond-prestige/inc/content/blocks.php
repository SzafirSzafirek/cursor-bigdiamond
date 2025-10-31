<?php
/**
 * Custom Gutenberg Blocks
 *
 * Registers custom ACF blocks for content building.
 *
 * @package BigDIAMOND_White_Prestige
 * @since   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register custom blocks
 *
 * @since 1.0.0
 */
function bdwp_register_blocks() {
	if ( ! function_exists( 'acf_register_block_type' ) ) {
		return;
	}

	// Hero Block
	acf_register_block_type( array(
		'name'            => 'bdwp-hero',
		'title'           => __( 'Hero Banner', 'bigdiamond-white-prestige' ),
		'description'     => __( 'Hero section z du?ym zdj?ciem i CTA', 'bigdiamond-white-prestige' ),
		'render_template' => 'template-parts/blocks/hero.php',
		'category'        => 'bigdiamond',
		'icon'            => 'format-image',
		'keywords'        => array( 'hero', 'banner', 'image' ),
		'supports'        => array(
			'align' => array( 'wide', 'full' ),
		),
	) );

	// Product Showcase Block
	acf_register_block_type( array(
		'name'            => 'bdwp-products',
		'title'           => __( 'Wyr??nione produkty', 'bigdiamond-white-prestige' ),
		'description'     => __( 'Siatka wybranych produkt?w', 'bigdiamond-white-prestige' ),
		'render_template' => 'template-parts/blocks/products.php',
		'category'        => 'bigdiamond',
		'icon'            => 'products',
		'keywords'        => array( 'products', 'shop', 'showcase' ),
	) );

	// Testimonials Block
	acf_register_block_type( array(
		'name'            => 'bdwp-testimonials',
		'title'           => __( 'Opinie klient?w', 'bigdiamond-white-prestige' ),
		'description'     => __( 'Slider z opiniami', 'bigdiamond-white-prestige' ),
		'render_template' => 'template-parts/blocks/testimonials.php',
		'category'        => 'bigdiamond',
		'icon'            => 'format-quote',
		'keywords'        => array( 'testimonial', 'review', 'quote' ),
	) );
}
add_action( 'acf/init', 'bdwp_register_blocks' );

/**
 * Register custom block category
 *
 * @param array $categories Existing categories.
 * @return array Modified categories.
 * @since 1.0.0
 */
function bdwp_block_category( $categories ) {
	return array_merge(
		array(
			array(
				'slug'  => 'bigdiamond',
				'title' => __( 'BigDIAMOND', 'bigdiamond-white-prestige' ),
			),
		),
		$categories
	);
}
add_filter( 'block_categories_all', 'bdwp_block_category' );
