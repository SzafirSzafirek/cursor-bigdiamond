<?php
/**
 * Product SEO Optimization
 *
 * Automatic meta generation, image alt tags, and product-specific
 * SEO enhancements for jewelry products.
 *
 * @package BigDIAMOND_White_Prestige
 * @since   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Auto-generate product image alt tags
 *
 * @param array $attr Image attributes.
 * @param WP_Post $attachment Attachment post.
 * @param string $size Image size.
 * @return array Modified attributes.
 * @since 1.0.0
 */
function bdwp_product_image_alt( $attr, $attachment, $size ) {
	// Only process product images
	if ( get_post_type( $attachment->post_parent ) !== 'product' ) {
		return $attr;
	}

	// If alt is empty, generate from filename or product name
	if ( empty( $attr['alt'] ) ) {
		$product = wc_get_product( $attachment->post_parent );

		if ( $product ) {
			$product_name = $product->get_name();
			$material = wp_get_post_terms( $product->get_id(), 'pa_material', array( 'fields' => 'names' ) );
			$stone = wp_get_post_terms( $product->get_id(), 'pa_kamien', array( 'fields' => 'names' ) );

			$alt_parts = array( $product_name );

			if ( ! empty( $material ) && ! is_wp_error( $material ) ) {
				$alt_parts[] = implode( ', ', $material );
			}

			if ( ! empty( $stone ) && ! is_wp_error( $stone ) ) {
				$alt_parts[] = implode( ', ', $stone );
			}

			$alt_parts[] = 'BigDIAMOND Krak?w';

			$attr['alt'] = implode( ' - ', $alt_parts );
		} else {
			// Fallback to filename
			$filename = basename( get_attached_file( $attachment->ID ) );
			$attr['alt'] = sanitize_text_field( str_replace( array( '-', '_' ), ' ', pathinfo( $filename, PATHINFO_FILENAME ) ) );
		}
	}

	// Auto-generate title if empty
	if ( empty( $attr['title'] ) ) {
		$attr['title'] = $attr['alt'];
	}

	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'bdwp_product_image_alt', 10, 3 );

/**
 * Optimize product meta title
 *
 * @param string $title Page title.
 * @return string Modified title.
 * @since 1.0.0
 */
function bdwp_product_meta_title( $title ) {
	if ( ! is_singular( 'product' ) ) {
		return $title;
	}

	global $product;

	if ( ! $product ) {
		return $title;
	}

	$product_name = $product->get_name();
	$price = $product->get_price();
	$material = wp_get_post_terms( $product->get_id(), 'pa_material', array( 'fields' => 'names' ) );

	$title_parts = array( $product_name );

	if ( ! empty( $material ) && ! is_wp_error( $material ) ) {
		$title_parts[] = implode( ', ', $material );
	}

	if ( $price ) {
		$title_parts[] = 'od ' . wc_price( $price );
	}

	$title_parts[] = 'BigDIAMOND Krak?w';

	return implode( ' | ', $title_parts );
}
// Uncomment if custom title generation is needed (may conflict with SEO plugins)
// add_filter( 'wpseo_title', 'bdwp_product_meta_title' );
// add_filter( 'rank_math/frontend/title', 'bdwp_product_meta_title' );

/**
 * Optimize product meta description
 *
 * @param string $description Meta description.
 * @return string Modified description.
 * @since 1.0.0
 */
function bdwp_product_meta_description( $description ) {
	if ( ! is_singular( 'product' ) ) {
		return $description;
	}

	global $product;

	if ( ! $product ) {
		return $description;
	}

	$short_desc = $product->get_short_description();

	if ( $short_desc ) {
		$description = wp_strip_all_tags( $short_desc );
		$description = bdwp_truncate( $description, 160, '...' );
	}

	return $description;
}
// Uncomment if custom description is needed (may conflict with SEO plugins)
// add_filter( 'wpseo_metadesc', 'bdwp_product_meta_description' );
// add_filter( 'rank_math/frontend/description', 'bdwp_product_meta_description' );

/**
 * Add Open Graph product tags
 *
 * @since 1.0.0
 */
function bdwp_product_og_tags() {
	if ( ! is_singular( 'product' ) ) {
		return;
	}

	global $product;

	if ( ! $product ) {
		return;
	}

	?>
	<meta property="og:type" content="product" />
	<meta property="og:title" content="<?php echo esc_attr( $product->get_name() ); ?>" />
	<meta property="og:description" content="<?php echo esc_attr( wp_strip_all_tags( $product->get_short_description() ) ); ?>" />
	<meta property="og:url" content="<?php echo esc_url( get_permalink() ); ?>" />
	<meta property="og:image" content="<?php echo esc_url( wp_get_attachment_image_url( $product->get_image_id(), 'full' ) ); ?>" />
	<meta property="product:price:amount" content="<?php echo esc_attr( $product->get_price() ); ?>" />
	<meta property="product:price:currency" content="<?php echo esc_attr( get_woocommerce_currency() ); ?>" />
	<meta property="product:availability" content="<?php echo $product->is_in_stock() ? 'in stock' : 'out of stock'; ?>" />
	<meta property="product:brand" content="BigDIAMOND" />
	<?php
}
add_action( 'wp_head', 'bdwp_product_og_tags' );

/**
 * Auto-generate image captions from product data
 *
 * @param int $attachment_id Attachment ID.
 * @since 1.0.0
 */
function bdwp_auto_generate_caption( $attachment_id ) {
	$parent_id = wp_get_post_parent_id( $attachment_id );

	if ( get_post_type( $parent_id ) !== 'product' ) {
		return;
	}

	// Check if caption already exists
	if ( wp_get_attachment_caption( $attachment_id ) ) {
		return;
	}

	$product = wc_get_product( $parent_id );

	if ( ! $product ) {
		return;
	}

	$caption = $product->get_name() . ' - ' . get_bloginfo( 'name' );

	wp_update_post( array(
		'ID'           => $attachment_id,
		'post_excerpt' => $caption,
	) );
}
add_action( 'add_attachment', 'bdwp_auto_generate_caption' );

/**
 * Add canonical URL for product variations
 *
 * @since 1.0.0
 */
function bdwp_product_canonical() {
	if ( ! is_singular( 'product' ) ) {
		return;
	}

	global $product;

	if ( ! $product || ! $product->is_type( 'variable' ) ) {
		return;
	}

	// Always use parent product URL as canonical
	echo '<link rel="canonical" href="' . esc_url( get_permalink( $product->get_id() ) ) . '" />' . "\n";
}
// Uncomment if needed (may conflict with SEO plugins)
// add_action( 'wp_head', 'bdwp_product_canonical' );

/**
 * Optimize category meta description
 *
 * @since 1.0.0
 */
function bdwp_category_meta_description() {
	if ( ! is_product_category() ) {
		return;
	}

	$term = get_queried_object();

	if ( ! $term || ! $term->description ) {
		return;
	}

	$description = bdwp_truncate( wp_strip_all_tags( $term->description ), 160 );

	echo '<meta name="description" content="' . esc_attr( $description ) . '" />' . "\n";
}
// Uncomment if needed (may conflict with SEO plugins)
// add_action( 'wp_head', 'bdwp_category_meta_description' );

/**
 * Add hreflang tags for multilingual setup
 *
 * @since 1.0.0
 */
function bdwp_hreflang_tags() {
	// Add hreflang tags if site has multiple language versions
	// Example implementation for future multilingual support
}
// add_action( 'wp_head', 'bdwp_hreflang_tags' );
