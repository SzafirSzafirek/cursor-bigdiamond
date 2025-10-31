<?php
/**
 * General Schema.org Structured Data
 *
 * Non-product schema markup for the site, including
 * LocalBusiness, FAQ, Article, and other schema types.
 *
 * @package BigDIAMOND_White_Prestige
 * @since   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Article schema for blog posts
 *
 * @since 1.0.0
 */
function bdwp_article_schema() {
	if ( ! is_singular( 'post' ) ) {
		return;
	}

	$post = get_post();
	$author = get_the_author_meta( 'display_name', $post->post_author );
	$modified = get_the_modified_date( 'c' );
	$published = get_the_date( 'c' );

	$schema = array(
		'@context'      => 'https://schema.org',
		'@type'         => 'BlogPosting',
		'headline'      => get_the_title(),
		'description'   => get_the_excerpt(),
		'datePublished' => $published,
		'dateModified'  => $modified,
		'author'        => array(
			'@type' => 'Person',
			'name'  => $author,
		),
		'publisher'     => array(
			'@type' => 'Organization',
			'name'  => get_bloginfo( 'name' ),
			'logo'  => array(
				'@type' => 'ImageObject',
				'url'   => BDWP_ASSETS_URI . '/images/logo.png',
			),
		),
	);

	// Add featured image
	if ( has_post_thumbnail() ) {
		$image_id = get_post_thumbnail_id();
		$image = wp_get_attachment_image_src( $image_id, 'full' );

		if ( $image ) {
			$schema['image'] = array(
				'@type'  => 'ImageObject',
				'url'    => $image[0],
				'width'  => $image[1],
				'height' => $image[2],
			);
		}
	}

	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\n";
}
add_action( 'wp_footer', 'bdwp_article_schema' );

/**
 * FAQ schema for FAQ pages
 *
 * @since 1.0.0
 */
function bdwp_faq_page_schema() {
	// Check if page has FAQ ACF fields
	if ( ! is_page() || ! function_exists( 'get_field' ) ) {
		return;
	}

	$faq_items = get_field( 'bdwp_faq_items' );

	if ( empty( $faq_items ) ) {
		return;
	}

	$questions = array();

	foreach ( $faq_items as $item ) {
		$questions[] = array(
			'@type'          => 'Question',
			'name'           => $item['question'],
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => wp_strip_all_tags( $item['answer'] ),
			),
		);
	}

	$schema = array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => $questions,
	);

	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\n";
}
add_action( 'wp_footer', 'bdwp_faq_page_schema' );

/**
 * ContactPage schema
 *
 * @since 1.0.0
 */
function bdwp_contact_page_schema() {
	if ( ! is_page( 'kontakt' ) ) {
		return;
	}

	$business_data = bdwp_get_business_schema();

	$schema = array(
		'@context' => 'https://schema.org',
		'@type'    => 'ContactPage',
		'name'     => get_the_title(),
		'url'      => get_permalink(),
		'about'    => array(
			'@type'    => 'Organization',
			'name'     => $business_data['name'],
			'telephone' => $business_data['telephone'],
			'email'    => $business_data['email'],
			'address'  => $business_data['address'],
		),
	);

	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\n";
}
add_action( 'wp_footer', 'bdwp_contact_page_schema' );

/**
 * Store Hours schema
 *
 * @since 1.0.0
 */
function bdwp_store_hours_schema() {
	if ( ! is_page( 'o-nas' ) && ! is_front_page() ) {
		return;
	}

	$business_data = bdwp_get_business_schema();

	$schema = array(
		'@context' => 'https://schema.org',
		'@type'    => 'Store',
		'name'     => $business_data['name'],
		'address'  => $business_data['address'],
		'openingHoursSpecification' => $business_data['openingHoursSpecification'],
		'telephone' => $business_data['telephone'],
		'url'      => home_url(),
	);

	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\n";
}
add_action( 'wp_footer', 'bdwp_store_hours_schema' );
