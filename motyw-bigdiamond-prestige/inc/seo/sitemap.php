<?php
/**
 * XML Sitemap Customization
 *
 * Enhances WordPress core sitemap or integrates with SEO plugins
 * to include custom post types and taxonomies.
 *
 * @package BigDIAMOND_White_Prestige
 * @since   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add custom post types to WordPress core sitemap
 *
 * @param array $post_types Post types.
 * @return array Modified post types.
 * @since 1.0.0
 */
function bdwp_sitemap_post_types( $post_types ) {
	// Add custom design projects to sitemap
	$post_types['custom_project'] = (object) array(
		'name'   => 'custom_project',
		'object' => get_post_type_object( 'custom_project' ),
	);

	return $post_types;
}
add_filter( 'wp_sitemaps_post_types', 'bdwp_sitemap_post_types' );

/**
 * Exclude specific product categories from sitemap
 *
 * @param array $args Query arguments.
 * @param string $post_type Post type.
 * @return array Modified arguments.
 * @since 1.0.0
 */
function bdwp_sitemap_exclude_categories( $args, $post_type ) {
	if ( 'product' !== $post_type ) {
		return $args;
	}

	// Exclude uncategorized or hidden products
	$excluded_categories = array( 'uncategorized', 'hidden' );

	if ( ! empty( $excluded_categories ) ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'product_cat',
				'field'    => 'slug',
				'terms'    => $excluded_categories,
				'operator' => 'NOT IN',
			),
		);
	}

	return $args;
}
add_filter( 'wp_sitemaps_posts_query_args', 'bdwp_sitemap_exclude_categories', 10, 2 );

/**
 * Add product taxonomies to sitemap
 *
 * @param array $taxonomies Taxonomies.
 * @return array Modified taxonomies.
 * @since 1.0.0
 */
function bdwp_sitemap_taxonomies( $taxonomies ) {
	// Include product categories and tags
	$taxonomies['product_cat'] = (object) array(
		'name'   => 'product_cat',
		'object' => get_taxonomy( 'product_cat' ),
	);

	$taxonomies['product_tag'] = (object) array(
		'name'   => 'product_tag',
		'object' => get_taxonomy( 'product_tag' ),
	);

	return $taxonomies;
}
add_filter( 'wp_sitemaps_taxonomies', 'bdwp_sitemap_taxonomies' );

/**
 * Set sitemap entry priority and frequency
 *
 * @param array  $entry Sitemap entry.
 * @param string $post_type Post type.
 * @param WP_Post $post Post object.
 * @return array Modified entry.
 * @since 1.0.0
 */
function bdwp_sitemap_entry( $entry, $post_type, $post ) {
	// Higher priority for products
	if ( 'product' === $post_type ) {
		$entry['priority'] = 0.8;
		$entry['changefreq'] = 'weekly';
	}

	// Lower priority for old posts
	if ( 'post' === $post_type ) {
		$post_age = time() - strtotime( $post->post_date );
		$days_old = $post_age / DAY_IN_SECONDS;

		if ( $days_old > 365 ) {
			$entry['priority'] = 0.3;
			$entry['changefreq'] = 'yearly';
		} else {
			$entry['priority'] = 0.6;
			$entry['changefreq'] = 'monthly';
		}
	}

	return $entry;
}
// Uncomment if priority/changefreq customization is needed
// add_filter( 'wp_sitemaps_posts_entry', 'bdwp_sitemap_entry', 10, 3 );

/**
 * Add image sitemap for products
 *
 * @param array  $entry Sitemap entry.
 * @param string $post_type Post type.
 * @param WP_Post $post Post object.
 * @return array Modified entry.
 * @since 1.0.0
 */
function bdwp_sitemap_product_images( $entry, $post_type, $post ) {
	if ( 'product' !== $post_type ) {
		return $entry;
	}

	$product = wc_get_product( $post->ID );

	if ( ! $product ) {
		return $entry;
	}

	$images = array();

	// Main image
	if ( $product->get_image_id() ) {
		$image_url = wp_get_attachment_image_url( $product->get_image_id(), 'full' );
		if ( $image_url ) {
			$images[] = array(
				'loc'     => $image_url,
				'title'   => $product->get_name(),
				'caption' => $product->get_short_description(),
			);
		}
	}

	// Gallery images
	$gallery_ids = $product->get_gallery_image_ids();
	foreach ( $gallery_ids as $image_id ) {
		$image_url = wp_get_attachment_image_url( $image_id, 'full' );
		if ( $image_url ) {
			$images[] = array(
				'loc'   => $image_url,
				'title' => get_the_title( $image_id ),
			);
		}
	}

	if ( ! empty( $images ) ) {
		$entry['images'] = $images;
	}

	return $entry;
}
// Uncomment if image sitemap is needed
// add_filter( 'wp_sitemaps_posts_entry', 'bdwp_sitemap_product_images', 10, 3 );
