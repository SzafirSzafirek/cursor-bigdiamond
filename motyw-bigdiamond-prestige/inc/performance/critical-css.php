<?php
/**
 * Critical CSS Management
 *
 * Handles inline critical CSS for above-the-fold content,
 * improves First Contentful Paint (FCP) and Largest Contentful Paint (LCP).
 *
 * @package BigDIAMOND_White_Prestige
 * @since   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Output critical CSS inline
 *
 * @since 1.0.0
 */
function bdwp_inline_critical_css() {
	$critical_css = bdwp_get_critical_css();

	if ( empty( $critical_css ) ) {
		return;
	}

	echo '<style id="bdwp-critical-css">' . $critical_css . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
add_action( 'wp_head', 'bdwp_inline_critical_css', 1 );

/**
 * Get critical CSS based on page type
 *
 * @return string Critical CSS.
 * @since 1.0.0
 */
function bdwp_get_critical_css() {
	$css = '';

	// Base critical CSS (typography, layout)
	$css .= '
		*,:after,:before{box-sizing:border-box;border:0 solid}
		html{line-height:1.5;-webkit-text-size-adjust:100%;tab-size:4;font-family:Inter,ui-sans-serif,system-ui,-apple-system,sans-serif}
		body{margin:0;line-height:inherit;font-family:Inter,sans-serif;color:#2F2F2F;background:#FAFAFA}
		h1,h2,h3,h4,h5,h6{font-family:"Playfair Display",Georgia,serif;font-weight:600;line-height:1.2}
		h1{font-size:2.25rem}h2{font-size:1.875rem}h3{font-size:1.5rem}
		a{color:#D4AF37;text-decoration:none}a:hover{text-decoration:underline}
		img{max-width:100%;height:auto;display:block}
		.container{max-width:1280px;margin:0 auto;padding:0 1rem}
	';

	// Homepage-specific critical CSS
	if ( is_front_page() ) {
		$css .= '
			.hero-section{min-height:80vh;display:flex;align-items:center;justify-content:center}
			.hero-title{font-size:3rem;font-family:"Playfair Display",serif;color:#2F2F2F;margin-bottom:1rem}
		';
	}

	// Product page critical CSS
	if ( is_singular( 'product' ) ) {
		$css .= '
			.product-gallery{margin-bottom:2rem}
			.product-title{font-size:2.25rem;margin-bottom:1rem}
			.product-price{font-size:1.875rem;color:#D4AF37;font-weight:600}
			.single_add_to_cart_button{background:#D4AF37;color:#FFF;padding:1rem 2rem;border:0;font-weight:600;cursor:pointer}
		';
	}

	// Shop page critical CSS
	if ( is_shop() || is_product_category() ) {
		$css .= '
			.products{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:2rem}
			.product-card{background:#FFF;border-radius:8px;overflow:hidden}
			.product-image-wrapper{aspect-ratio:1;overflow:hidden}
		';
	}

	return apply_filters( 'bdwp_critical_css', $css );
}

/**
 * Load non-critical CSS asynchronously
 *
 * @param string $html   Link tag HTML.
 * @param string $handle Style handle.
 * @return string Modified link tag.
 * @since 1.0.0
 */
function bdwp_async_css( $html, $handle ) {
	// Skip critical styles
	if ( 'bdwp-main' === $handle ) {
		// Load main styles with media="print" and switch to "all" on load
		$html = str_replace( "media='all'", "media='print' onload=\"this.media='all'\"", $html );
		$html .= '<noscript>' . str_replace( "media='print' onload=\"this.media='all'\"", "media='all'", $html ) . '</noscript>';
	}

	return $html;
}
add_filter( 'style_loader_tag', 'bdwp_async_css', 10, 2 );

/**
 * Preload key assets
 *
 * @since 1.0.0
 */
function bdwp_preload_assets() {
	// Preload critical fonts
	echo '<link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">' . "\n";
	echo '<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap"></noscript>' . "\n";

	// Preload logo
	if ( is_front_page() ) {
		echo '<link rel="preload" href="' . esc_url( BDWP_ASSETS_URI . '/images/logo.svg' ) . '" as="image">' . "\n";
	}

	// Preload hero image
	if ( is_front_page() && function_exists( 'get_field' ) ) {
		$hero_image = get_field( 'hero_image' );
		if ( $hero_image ) {
			echo '<link rel="preload" href="' . esc_url( $hero_image['url'] ) . '" as="image">' . "\n";
		}
	}

	// Preload first product image on PDP
	if ( is_singular( 'product' ) ) {
		global $product;
		if ( $product && $product->get_image_id() ) {
			$image_url = wp_get_attachment_image_url( $product->get_image_id(), 'full' );
			if ( $image_url ) {
				echo '<link rel="preload" href="' . esc_url( $image_url ) . '" as="image">' . "\n";
			}
		}
	}
}
add_action( 'wp_head', 'bdwp_preload_assets', 2 );

/**
 * DNS prefetch for external resources
 *
 * @param array  $urls          URLs to print for resource hints.
 * @param string $relation_type The relation type.
 * @return array Modified URLs.
 * @since 1.0.0
 */
function bdwp_dns_prefetch( $urls, $relation_type ) {
	if ( 'dns-prefetch' === $relation_type ) {
		$urls[] = 'https://fonts.googleapis.com';
		$urls[] = 'https://fonts.gstatic.com';

		// Add other external domains
		// $urls[] = 'https://your-cdn.com';
	}

	return $urls;
}
add_filter( 'wp_resource_hints', 'bdwp_dns_prefetch', 10, 2 );

/**
 * Remove query strings from static resources
 *
 * @param string $src Source URL.
 * @return string Modified URL.
 * @since 1.0.0
 */
function bdwp_remove_query_strings( $src ) {
	if ( strpos( $src, '?ver=' ) ) {
		$src = remove_query_arg( 'ver', $src );
	}

	return $src;
}
add_filter( 'style_loader_src', 'bdwp_remove_query_strings' );
add_filter( 'script_loader_src', 'bdwp_remove_query_strings' );
