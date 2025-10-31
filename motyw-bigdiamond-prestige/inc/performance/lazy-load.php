<?php
/**
 * Lazy Loading Optimization
 *
 * Handles lazy loading for images, iframes, and other resources
 * to improve page load performance and reduce initial payload.
 *
 * @package BigDIAMOND_White_Prestige
 * @since   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add loading="lazy" to images (WordPress 5.5+)
 *
 * @param array $attr Image attributes.
 * @return array Modified attributes.
 * @since 1.0.0
 */
function bdwp_add_lazy_loading( $attr ) {
	// Skip for hero/above-fold images
	if ( doing_action( 'woocommerce_before_single_product_summary' ) ) {
		$attr['loading'] = 'eager';
		$attr['fetchpriority'] = 'high';
		return $attr;
	}

	// Add lazy loading to other images
	if ( ! isset( $attr['loading'] ) ) {
		$attr['loading'] = 'lazy';
	}

	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'bdwp_add_lazy_loading' );

/**
 * Add width and height attributes to images
 *
 * @param string $html Image HTML.
 * @param int    $attachment_id Attachment ID.
 * @param string $size Image size.
 * @return string Modified HTML.
 * @since 1.0.0
 */
function bdwp_add_image_dimensions( $html, $attachment_id, $size ) {
	// Skip if dimensions already present
	if ( strpos( $html, ' width="' ) !== false ) {
		return $html;
	}

	$image_meta = wp_get_attachment_metadata( $attachment_id );

	if ( ! isset( $image_meta['width'], $image_meta['height'] ) ) {
		return $html;
	}

	// Get dimensions for specific size
	if ( is_string( $size ) && isset( $image_meta['sizes'][ $size ] ) ) {
		$width  = $image_meta['sizes'][ $size ]['width'];
		$height = $image_meta['sizes'][ $size ]['height'];
	} else {
		$width  = $image_meta['width'];
		$height = $image_meta['height'];
	}

	// Add dimensions to img tag
	$html = str_replace( '<img ', '<img width="' . esc_attr( $width ) . '" height="' . esc_attr( $height ) . '" ', $html );

	return $html;
}
add_filter( 'wp_get_attachment_image', 'bdwp_add_image_dimensions', 10, 3 );

/**
 * Lazy load iframes (YouTube, Google Maps, etc.)
 *
 * @param string $content Post content.
 * @return string Modified content.
 * @since 1.0.0
 */
function bdwp_lazy_load_iframes( $content ) {
	if ( is_admin() || is_feed() || wp_is_json_request() ) {
		return $content;
	}

	// Add loading="lazy" to iframes
	$content = preg_replace(
		'/<iframe\s+(?!.*?loading=)/i',
		'<iframe loading="lazy" ',
		$content
	);

	return $content;
}
add_filter( 'the_content', 'bdwp_lazy_load_iframes' );
add_filter( 'widget_text', 'bdwp_lazy_load_iframes' );

/**
 * Lazy load Gravatar images
 *
 * @param string $avatar Avatar HTML.
 * @return string Modified avatar HTML.
 * @since 1.0.0
 */
function bdwp_lazy_load_gravatars( $avatar ) {
	$avatar = str_replace( '<img ', '<img loading="lazy" ', $avatar );
	return $avatar;
}
add_filter( 'get_avatar', 'bdwp_lazy_load_gravatars' );

/**
 * Add srcset and sizes attributes for responsive images
 *
 * @param array  $attr       Image attributes.
 * @param object $attachment Attachment object.
 * @param string $size       Image size.
 * @return array Modified attributes.
 * @since 1.0.0
 */
function bdwp_responsive_images( $attr, $attachment, $size ) {
	// WordPress handles srcset by default, but we can customize sizes attribute

	// Customize sizes for product images
	if ( is_singular( 'product' ) ) {
		$attr['sizes'] = '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 600px';
	}

	// Customize sizes for shop archives
	if ( is_shop() || is_product_category() ) {
		$attr['sizes'] = '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw';
	}

	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'bdwp_responsive_images', 10, 3 );

/**
 * Lazy load product gallery images
 *
 * @since 1.0.0
 */
function bdwp_lazy_load_product_gallery() {
	?>
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			// Lazy load gallery thumbnails
			const galleryThumbs = document.querySelectorAll('.woocommerce-product-gallery__image img');
			
			if ('loading' in HTMLImageElement.prototype) {
				galleryThumbs.forEach((img, index) => {
					// First image eager, rest lazy
					if (index > 0) {
						img.loading = 'lazy';
					}
				});
			}
		});
	</script>
	<?php
}
add_action( 'wp_footer', 'bdwp_lazy_load_product_gallery' );

/**
 * Optimize background images with lazy loading
 *
 * @since 1.0.0
 */
function bdwp_lazy_load_backgrounds() {
	?>
	<script>
		// Intersection Observer for background images
		if ('IntersectionObserver' in window) {
			const bgObserver = new IntersectionObserver((entries) => {
				entries.forEach(entry => {
					if (entry.isIntersecting) {
						const bg = entry.target;
						const bgImage = bg.dataset.bgImage;
						if (bgImage) {
							bg.style.backgroundImage = `url(${bgImage})`;
							bgObserver.unobserve(bg);
						}
					}
				});
			});

			document.querySelectorAll('[data-bg-image]').forEach(bg => {
				bgObserver.observe(bg);
			});
		}
	</script>
	<?php
}
add_action( 'wp_footer', 'bdwp_lazy_load_backgrounds', 100 );
