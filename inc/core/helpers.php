<?php
/**
 * Helper Functions
 *
 * Utility functions used throughout the theme for common tasks,
 * data formatting, and reusable logic.
 *
 * @package BigDIAMOND_White_Prestige
 * @since   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get template part with data
 *
 * Enhanced version of get_template_part that allows passing data.
 *
 * @param string $slug Template slug.
 * @param string $name Optional template name.
 * @param array  $args Optional arguments to pass to template.
 * @return void
 * @since 1.0.0
 */
function bdwp_get_template_part( $slug, $name = null, $args = array() ) {
	// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	if ( ! empty( $args ) ) {
		set_query_var( 'template_args', $args );
	}

	get_template_part( $slug, $name );

	if ( ! empty( $args ) ) {
		set_query_var( 'template_args', null );
	}
}

/**
 * Get SVG icon
 *
 * Returns inline SVG markup for icons.
 *
 * @param string $icon Icon name.
 * @param string $class Optional CSS classes.
 * @return string SVG markup.
 * @since 1.0.0
 */
function bdwp_get_icon( $icon, $class = '' ) {
	$icons = array(
		'cart' => '<svg class="' . esc_attr( $class ) . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
		'search' => '<svg class="' . esc_attr( $class ) . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>',
		'heart' => '<svg class="' . esc_attr( $class ) . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>',
		'user' => '<svg class="' . esc_attr( $class ) . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>',
		'check' => '<svg class="' . esc_attr( $class ) . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>',
		'arrow-right' => '<svg class="' . esc_attr( $class ) . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>',
		'close' => '<svg class="' . esc_attr( $class ) . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>',
		'star' => '<svg class="' . esc_attr( $class ) . '" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>',
	);

	return isset( $icons[ $icon ] ) ? $icons[ $icon ] : '';
}

/**
 * Format price range
 *
 * @param float $min Minimum price.
 * @param float $max Maximum price.
 * @return string Formatted price range.
 * @since 1.0.0
 */
function bdwp_format_price_range( $min, $max ) {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return '';
	}

	if ( $min === $max ) {
		return wc_price( $min );
	}

	return sprintf(
		/* translators: 1: minimum price, 2: maximum price */
		__( '%1$s &ndash; %2$s', 'bigdiamond-white-prestige' ),
		wc_price( $min ),
		wc_price( $max )
	);
}

/**
 * Get product attributes for display
 *
 * @param int $product_id Product ID.
 * @param array $exclude Attributes to exclude.
 * @return array Formatted attributes.
 * @since 1.0.0
 */
function bdwp_get_product_attributes( $product_id, $exclude = array( 'pa_gramatura', 'pa_rozmiar' ) ) {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return array();
	}

	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		return array();
	}

	$attributes = array();
	foreach ( $product->get_attributes() as $attribute ) {
		$name = $attribute->get_name();

		// Skip excluded attributes
		if ( in_array( $name, $exclude, true ) ) {
			continue;
		}

		$label = wc_attribute_label( $name );
		$values = $attribute->is_taxonomy()
			? wc_get_product_terms( $product_id, $name, array( 'fields' => 'names' ) )
			: $attribute->get_options();

		$attributes[] = array(
			'label' => $label,
			'value' => is_array( $values ) ? implode( ', ', $values ) : $values,
		);
	}

	return $attributes;
}

/**
 * Truncate text with ellipsis
 *
 * @param string $text Text to truncate.
 * @param int    $length Maximum length.
 * @param string $suffix Suffix to append.
 * @return string Truncated text.
 * @since 1.0.0
 */
function bdwp_truncate( $text, $length = 150, $suffix = '...' ) {
	if ( mb_strlen( $text ) <= $length ) {
		return $text;
	}

	return mb_substr( $text, 0, $length ) . $suffix;
}

/**
 * Get reading time estimate
 *
 * @param string $content Post content.
 * @return int Reading time in minutes.
 * @since 1.0.0
 */
function bdwp_reading_time( $content ) {
	$word_count = str_word_count( wp_strip_all_tags( $content ) );
	$reading_time = ceil( $word_count / 200 ); // 200 words per minute

	return max( 1, $reading_time );
}

/**
 * Check if user can see prices
 *
 * Useful for wholesale or B2B scenarios.
 *
 * @return bool
 * @since 1.0.0
 */
function bdwp_can_see_prices() {
	return apply_filters( 'bdwp_can_see_prices', true );
}

/**
 * Get business schema data
 *
 * @return array Business information for schema markup.
 * @since 1.0.0
 */
function bdwp_get_business_schema() {
	return array(
		'@type'           => 'JewelryStore',
		'name'            => 'BigDIAMOND',
		'description'     => get_bloginfo( 'description' ),
		'url'             => home_url(),
		'logo'            => BDWP_ASSETS_URI . '/images/logo.png',
		'image'           => BDWP_ASSETS_URI . '/images/store-front.jpg',
		'telephone'       => get_option( 'bdwp_phone', '' ),
		'email'           => get_option( 'bdwp_email', '' ),
		'address'         => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => get_option( 'bdwp_street', '' ),
			'addressLocality' => 'Krak?w',
			'postalCode'      => get_option( 'bdwp_postal', '' ),
			'addressCountry'  => 'PL',
		),
		'openingHoursSpecification' => array(
			array(
				'@type'     => 'OpeningHoursSpecification',
				'dayOfWeek' => array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday' ),
				'opens'     => '10:00',
				'closes'    => '18:00',
			),
			array(
				'@type'     => 'OpeningHoursSpecification',
				'dayOfWeek' => 'Saturday',
				'opens'     => '10:00',
				'closes'    => '14:00',
			),
		),
		'priceRange'      => '???',
		'paymentAccepted' => 'Cash, Credit Card, Bank Transfer',
	);
}
