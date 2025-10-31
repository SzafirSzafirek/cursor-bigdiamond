<?php
/**
 * Custom Shortcodes
 *
 * Registers reusable shortcodes for content.
 *
 * @package BigDIAMOND_White_Prestige
 * @since   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contact information shortcode
 *
 * Usage: [bdwp_contact type="phone"]
 *
 * @param array $atts Shortcode attributes.
 * @return string Contact information.
 * @since 1.0.0
 */
function bdwp_contact_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'type' => 'phone',
	), $atts, 'bdwp_contact' );

	switch ( $atts['type'] ) {
		case 'phone':
			$value = get_option( 'bdwp_phone', '+48 123 456 789' );
			return '<a href="tel:' . esc_attr( str_replace( ' ', '', $value ) ) . '">' . esc_html( $value ) . '</a>';

		case 'email':
			$value = get_option( 'bdwp_email', 'kontakt@bigdiamond.pl' );
			return '<a href="mailto:' . esc_attr( $value ) . '">' . esc_html( $value ) . '</a>';

		case 'address':
			return esc_html( get_option( 'bdwp_street', '' ) . ', ' . get_option( 'bdwp_postal', '' ) . ' Krak?w' );

		default:
			return '';
	}
}
add_shortcode( 'bdwp_contact', 'bdwp_contact_shortcode' );

/**
 * Product carousel shortcode
 *
 * Usage: [bdwp_products category="pierscionki" limit="6"]
 *
 * @param array $atts Shortcode attributes.
 * @return string Product carousel HTML.
 * @since 1.0.0
 */
function bdwp_products_shortcode( $atts ) {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return '';
	}

	$atts = shortcode_atts( array(
		'category' => '',
		'limit'    => 6,
		'orderby'  => 'date',
		'order'    => 'DESC',
	), $atts, 'bdwp_products' );

	$args = array(
		'post_type'      => 'product',
		'posts_per_page' => intval( $atts['limit'] ),
		'orderby'        => $atts['orderby'],
		'order'          => $atts['order'],
	);

	if ( ! empty( $atts['category'] ) ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'product_cat',
				'field'    => 'slug',
				'terms'    => sanitize_text_field( $atts['category'] ),
			),
		);
	}

	$query = new WP_Query( $args );

	if ( ! $query->have_posts() ) {
		return '';
	}

	ob_start();

	echo '<div class="bdwp-products-carousel grid grid-cols-1 md:grid-cols-3 gap-6">';

	while ( $query->have_posts() ) {
		$query->the_post();
		wc_get_template_part( 'content', 'product' );
	}

	echo '</div>';

	wp_reset_postdata();

	return ob_get_clean();
}
add_shortcode( 'bdwp_products', 'bdwp_products_shortcode' );

/**
 * Button shortcode
 *
 * Usage: [bdwp_button url="/sklep" style="primary"]Przejd? do sklepu[/bdwp_button]
 *
 * @param array  $atts    Shortcode attributes.
 * @param string $content Button text.
 * @return string Button HTML.
 * @since 1.0.0
 */
function bdwp_button_shortcode( $atts, $content = null ) {
	$atts = shortcode_atts( array(
		'url'    => '#',
		'style'  => 'primary',
		'target' => '_self',
	), $atts, 'bdwp_button' );

	$class = 'btn btn-' . sanitize_html_class( $atts['style'] );

	return sprintf(
		'<a href="%s" class="%s" target="%s">%s</a>',
		esc_url( $atts['url'] ),
		esc_attr( $class ),
		esc_attr( $atts['target'] ),
		wp_kses_post( $content )
	);
}
add_shortcode( 'bdwp_button', 'bdwp_button_shortcode' );
