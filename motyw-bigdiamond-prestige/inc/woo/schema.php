<?php
/**
 * WooCommerce Schema.org Structured Data
 *
 * Generates JSON-LD schema for products, offers, reviews,
 * and e-commerce specific markup for SEO.
 *
 * @package BigDIAMOND_White_Prestige
 * @since   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Output Product schema on single product pages
 *
 * @since 1.0.0
 */
function bdwp_product_schema() {
	if ( ! is_singular( 'product' ) ) {
		return;
	}

	global $product;

	if ( ! $product ) {
		return;
	}

	$schema = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Product',
		'name'        => $product->get_name(),
		'description' => wp_strip_all_tags( $product->get_short_description() ?: $product->get_description() ),
		'sku'         => $product->get_sku(),
		'image'       => array(),
		'brand'       => array(
			'@type' => 'Brand',
			'name'  => 'BigDIAMOND',
		),
	);

	// Images
	$image_ids = array( $product->get_image_id() );
	$gallery_ids = $product->get_gallery_image_ids();
	if ( $gallery_ids ) {
		$image_ids = array_merge( $image_ids, $gallery_ids );
	}

	foreach ( array_filter( $image_ids ) as $image_id ) {
		$image_url = wp_get_attachment_image_url( $image_id, 'full' );
		if ( $image_url ) {
			$schema['image'][] = $image_url;
		}
	}

	// Offers
	$schema['offers'] = array(
		'@type'         => 'Offer',
		'url'           => get_permalink(),
		'priceCurrency' => get_woocommerce_currency(),
		'price'         => $product->get_price(),
		'availability'  => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
		'priceValidUntil' => gmdate( 'Y-12-31' ),
		'seller'        => array(
			'@type' => 'Organization',
			'name'  => 'BigDIAMOND',
		),
	);

	// Sale price
	if ( $product->is_on_sale() && $product->get_sale_price() ) {
		$schema['offers']['price'] = $product->get_sale_price();
	}

	// Reviews / Aggregate Rating
	$rating_count = $product->get_rating_count();
	$average_rating = $product->get_average_rating();

	if ( $rating_count > 0 && $average_rating > 0 ) {
		$schema['aggregateRating'] = array(
			'@type'       => 'AggregateRating',
			'ratingValue' => $average_rating,
			'reviewCount' => $rating_count,
			'bestRating'  => '5',
			'worstRating' => '1',
		);
	}

	// Product attributes
	$attributes = bdwp_get_product_attributes( $product->get_id() );
	if ( ! empty( $attributes ) ) {
		$schema['additionalProperty'] = array();

		foreach ( $attributes as $attribute ) {
			$schema['additionalProperty'][] = array(
				'@type' => 'PropertyValue',
				'name'  => $attribute['label'],
				'value' => $attribute['value'],
			);
		}
	}

	// Material (for jewelry)
	$material_terms = wp_get_post_terms( $product->get_id(), 'pa_material', array( 'fields' => 'names' ) );
	if ( ! empty( $material_terms ) && ! is_wp_error( $material_terms ) ) {
		$schema['material'] = implode( ', ', $material_terms );
	}

	// GTIN/UPC/EAN (if available)
	$gtin = get_post_meta( $product->get_id(), '_gtin', true );
	if ( $gtin ) {
		$schema['gtin'] = $gtin;
	}

	// Output schema
	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\n";
}
add_action( 'wp_footer', 'bdwp_product_schema' );

/**
 * BreadcrumbList schema
 *
 * @since 1.0.0
 */
function bdwp_breadcrumb_schema() {
	if ( ! is_singular( 'product' ) && ! is_product_category() && ! is_product_tag() ) {
		return;
	}

	$breadcrumbs = array(
		array(
			'@type'    => 'ListItem',
			'position' => 1,
			'name'     => __( 'Strona g??wna', 'bigdiamond-white-prestige' ),
			'item'     => home_url(),
		),
		array(
			'@type'    => 'ListItem',
			'position' => 2,
			'name'     => __( 'Sklep', 'bigdiamond-white-prestige' ),
			'item'     => get_permalink( wc_get_page_id( 'shop' ) ),
		),
	);

	$position = 3;

	// Category breadcrumbs
	if ( is_product_category() ) {
		$term = get_queried_object();
		$breadcrumbs[] = array(
			'@type'    => 'ListItem',
			'position' => $position,
			'name'     => $term->name,
			'item'     => get_term_link( $term ),
		);
	} elseif ( is_singular( 'product' ) ) {
		global $product;
		$categories = get_the_terms( $product->get_id(), 'product_cat' );

		if ( $categories && ! is_wp_error( $categories ) ) {
			$main_category = reset( $categories );
			$breadcrumbs[] = array(
				'@type'    => 'ListItem',
				'position' => $position++,
				'name'     => $main_category->name,
				'item'     => get_term_link( $main_category ),
			);
		}

		$breadcrumbs[] = array(
			'@type'    => 'ListItem',
			'position' => $position,
			'name'     => get_the_title(),
			'item'     => get_permalink(),
		);
	}

	$schema = array(
		'@context'        => 'https://schema.org',
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $breadcrumbs,
	);

	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}
add_action( 'wp_footer', 'bdwp_breadcrumb_schema' );

/**
 * Organization / LocalBusiness schema (site-wide)
 *
 * @since 1.0.0
 */
function bdwp_organization_schema() {
	if ( ! is_front_page() && ! is_shop() ) {
		return;
	}

	$business_data = bdwp_get_business_schema();

	$schema = array(
		'@context' => 'https://schema.org',
		'@type'    => 'Organization',
		'@id'      => home_url() . '#organization',
		'name'     => $business_data['name'],
		'url'      => $business_data['url'],
		'logo'     => array(
			'@type' => 'ImageObject',
			'url'   => $business_data['logo'],
		),
		'contactPoint' => array(
			'@type'       => 'ContactPoint',
			'telephone'   => $business_data['telephone'],
			'contactType' => 'Customer Service',
			'email'       => $business_data['email'],
			'areaServed'  => 'PL',
			'availableLanguage' => 'Polish',
		),
		'sameAs' => array(
			'https://www.facebook.com/bigdiamond',
			'https://www.instagram.com/bigdiamond',
		),
	);

	// Add LocalBusiness data if on homepage
	if ( is_front_page() ) {
		$schema['@type'] = array( 'Organization', 'JewelryStore', 'LocalBusiness' );
		$schema['address'] = $business_data['address'];
		$schema['openingHoursSpecification'] = $business_data['openingHoursSpecification'];
		$schema['priceRange'] = $business_data['priceRange'];
		$schema['paymentAccepted'] = $business_data['paymentAccepted'];
	}

	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\n";
}
add_action( 'wp_footer', 'bdwp_organization_schema' );

/**
 * WebSite schema with SearchAction
 *
 * @since 1.0.0
 */
function bdwp_website_schema() {
	if ( ! is_front_page() ) {
		return;
	}

	$schema = array(
		'@context' => 'https://schema.org',
		'@type'    => 'WebSite',
		'@id'      => home_url() . '#website',
		'name'     => get_bloginfo( 'name' ),
		'url'      => home_url(),
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => array(
				'@type'       => 'EntryPoint',
				'urlTemplate' => home_url( '/?s={search_term_string}&post_type=product' ),
			),
			'query-input' => 'required name=search_term_string',
		),
	);

	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}
add_action( 'wp_footer', 'bdwp_website_schema' );

/**
 * ItemList schema for product archives
 *
 * @since 1.0.0
 */
function bdwp_product_list_schema() {
	if ( ! is_shop() && ! is_product_category() && ! is_product_tag() ) {
		return;
	}

	global $wp_query;

	$items = array();
	$position = 1;

	if ( $wp_query->have_posts() ) {
		while ( $wp_query->have_posts() ) {
			$wp_query->the_post();
			$product = wc_get_product( get_the_ID() );

			if ( ! $product ) {
				continue;
			}

			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $position++,
				'url'      => get_permalink(),
			);

			// Limit to first 50 products for performance
			if ( $position > 50 ) {
				break;
			}
		}
		wp_reset_postdata();
	}

	if ( empty( $items ) ) {
		return;
	}

	$schema = array(
		'@context'        => 'https://schema.org',
		'@type'           => 'ItemList',
		'itemListElement' => $items,
	);

	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}
add_action( 'wp_footer', 'bdwp_product_list_schema' );

/**
 * Review schema for individual product reviews
 *
 * @param array $comment Comment data.
 * @since 1.0.0
 */
function bdwp_review_schema( $comment ) {
	if ( get_comment_type() !== 'review' ) {
		return;
	}

	$rating = intval( get_comment_meta( $comment->comment_ID, 'rating', true ) );

	if ( ! $rating ) {
		return;
	}

	$schema = array(
		'@context'      => 'https://schema.org',
		'@type'         => 'Review',
		'author'        => array(
			'@type' => 'Person',
			'name'  => get_comment_author( $comment ),
		),
		'datePublished' => get_comment_date( 'c', $comment ),
		'reviewBody'    => get_comment_text( $comment ),
		'reviewRating'  => array(
			'@type'       => 'Rating',
			'ratingValue' => $rating,
			'bestRating'  => '5',
			'worstRating' => '1',
		),
	);

	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}
// Uncomment if individual review schema is needed
// add_action( 'woocommerce_review_after_comment_text', 'bdwp_review_schema' );
