<?php
/**
 * WooCommerce Single Product Page
 *
 * Customizes product detail page (PDP) with 4C information,
 * certificates, configurator CTA, and enhanced product information.
 *
 * @package BigDIAMOND_White_Prestige
 * @since   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customize single product layout
 *
 * @since 1.0.0
 */
function bdwp_single_product_layout() {
	// Remove default hooks
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );

	// Add custom hooks
	add_action( 'woocommerce_single_product_summary', 'bdwp_product_breadcrumb', 3 );
	add_action( 'woocommerce_single_product_summary', 'bdwp_product_title', 5 );
	add_action( 'woocommerce_single_product_summary', 'bdwp_product_price_rating', 10 );
	add_action( 'woocommerce_single_product_summary', 'bdwp_product_short_desc', 15 );
	add_action( 'woocommerce_single_product_summary', 'bdwp_product_attributes_table', 25 );
	add_action( 'woocommerce_single_product_summary', 'bdwp_product_availability', 35 );
	add_action( 'woocommerce_single_product_summary', 'bdwp_product_meta', 45 );

	// After summary sections
	add_action( 'woocommerce_after_single_product_summary', 'bdwp_why_bigdiamond_section', 5 );
	add_action( 'woocommerce_after_single_product_summary', 'bdwp_product_4c_panel', 8 );
	add_action( 'woocommerce_after_single_product_summary', 'bdwp_product_certificates', 12 );
	add_action( 'woocommerce_after_single_product_summary', 'bdwp_product_faq', 15 );
	add_action( 'woocommerce_after_single_product_summary', 'bdwp_related_products_section', 20 );
	add_action( 'woocommerce_after_single_product_summary', 'bdwp_configurator_cta', 25 );
}
add_action( 'init', 'bdwp_single_product_layout' );

/**
 * Product breadcrumb
 *
 * @since 1.0.0
 */
function bdwp_product_breadcrumb() {
	woocommerce_breadcrumb();
}

/**
 * Product title
 *
 * @since 1.0.0
 */
function bdwp_product_title() {
	echo '<h1 class="product-title text-4xl font-display font-bold text-bd-charcoal mb-4">';
	echo esc_html( get_the_title() );
	echo '</h1>';
}

/**
 * Product price and rating combined
 *
 * @since 1.0.0
 */
function bdwp_product_price_rating() {
	echo '<div class="flex items-center justify-between mb-6">';
	
	if ( bdwp_can_see_prices() ) {
		echo '<div class="product-price text-3xl font-semibold text-bd-gold">';
		woocommerce_template_single_price();
		echo '</div>';
	}

	echo '<div class="product-rating">';
	woocommerce_template_single_rating();
	echo '</div>';

	echo '</div>';
}

/**
 * Product short description
 *
 * @since 1.0.0
 */
function bdwp_product_short_desc() {
	global $product;

	$short_desc = $product->get_short_description();
	if ( $short_desc ) {
		echo '<div class="product-short-description text-bd-gray-700 leading-relaxed mb-6">';
		echo wp_kses_post( wpautop( $short_desc ) );
		echo '</div>';
	}
}

/**
 * Product attributes table
 *
 * Displays material, stone, color (excludes gramatura and rozmiar)
 *
 * @since 1.0.0
 */
function bdwp_product_attributes_table() {
	global $product;

	$attributes = bdwp_get_product_attributes( $product->get_id() );

	if ( ! empty( $attributes ) ) {
		echo '<div class="product-attributes bg-bd-cream rounded-lg p-6 mb-6">';
		echo '<h3 class="text-lg font-semibold mb-4">' . esc_html__( 'Specyfikacja', 'bigdiamond-white-prestige' ) . '</h3>';
		echo '<dl class="grid grid-cols-1 gap-3">';

		foreach ( $attributes as $attribute ) {
			echo '<div class="flex justify-between border-b border-bd-gray-200 pb-2">';
			echo '<dt class="text-bd-gray-600">' . esc_html( $attribute['label'] ) . '</dt>';
			echo '<dd class="font-medium text-bd-charcoal">' . esc_html( $attribute['value'] ) . '</dd>';
			echo '</div>';
		}

		echo '</dl>';
		echo '</div>';
	}
}

/**
 * Product availability information
 *
 * @since 1.0.0
 */
function bdwp_product_availability() {
	global $product;

	$availability = $product->get_availability();

	echo '<div class="product-availability flex items-center gap-2 mb-6">';

	if ( $product->is_in_stock() ) {
		echo '<span class="inline-flex items-center text-green-600">';
		echo bdwp_get_icon( 'check', 'w-5 h-5 mr-1' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo esc_html__( 'Dost?pny', 'bigdiamond-white-prestige' );
		echo '</span>';

		// Shipping time from ACF
		$shipping_time = get_field( 'bdwp_shipping_time', $product->get_id() );
		if ( $shipping_time ) {
			echo '<span class="text-bd-gray-600">? ' . esc_html( $shipping_time ) . '</span>';
		}
	} else {
		echo '<span class="text-red-600">' . esc_html__( 'Niedost?pny', 'bigdiamond-white-prestige' ) . '</span>';
	}

	echo '</div>';
}

/**
 * Product meta (SKU, Categories, Tags)
 *
 * @since 1.0.0
 */
function bdwp_product_meta() {
	global $product;

	echo '<div class="product-meta text-sm text-bd-gray-600 border-t border-bd-gray-200 pt-4 mt-6">';

	// SKU
	if ( wc_product_sku_enabled() && $product->get_sku() ) {
		echo '<div class="meta-item mb-2">';
		echo '<span class="font-medium">' . esc_html__( 'SKU:', 'bigdiamond-white-prestige' ) . '</span> ';
		echo '<span>' . esc_html( $product->get_sku() ) . '</span>';
		echo '</div>';
	}

	// Categories
	echo wc_get_product_category_list( $product->get_id(), ', ', '<div class="meta-item mb-2"><span class="font-medium">' . esc_html__( 'Kategorie:', 'bigdiamond-white-prestige' ) . '</span> ', '</div>' );

	// Tags
	echo wc_get_product_tag_list( $product->get_id(), ', ', '<div class="meta-item"><span class="font-medium">' . esc_html__( 'Tagi:', 'bigdiamond-white-prestige' ) . '</span> ', '</div>' );

	echo '</div>';
}

/**
 * "Why BigDIAMOND" section
 *
 * @since 1.0.0
 */
function bdwp_why_bigdiamond_section() {
	$reasons = array(
		array(
			'icon'  => 'check',
			'title' => __( 'Certyfikat autentyczno?ci', 'bigdiamond-white-prestige' ),
			'desc'  => __( 'Ka?dy produkt z certyfikatem i dokumentacj?', 'bigdiamond-white-prestige' ),
		),
		array(
			'icon'  => 'check',
			'title' => __( 'Gwarancja satysfakcji', 'bigdiamond-white-prestige' ),
			'desc'  => __( '30 dni na zwrot bez podania przyczyny', 'bigdiamond-white-prestige' ),
		),
		array(
			'icon'  => 'check',
			'title' => __( 'Darmowa wysy?ka', 'bigdiamond-white-prestige' ),
			'desc'  => __( 'Bezpieczna dostawa w ca?ej Polsce', 'bigdiamond-white-prestige' ),
		),
		array(
			'icon'  => 'check',
			'title' => __( 'Obs?uga na najwy?szym poziomie', 'bigdiamond-white-prestige' ),
			'desc'  => __( 'Ekspert jubilerski zawsze do dyspozycji', 'bigdiamond-white-prestige' ),
		),
	);

	echo '<section class="why-bigdiamond bg-bd-cream py-12 my-12">';
	echo '<div class="container mx-auto px-4">';
	echo '<h2 class="text-3xl font-display font-bold text-center mb-8">' . esc_html__( 'Dlaczego BigDIAMOND', 'bigdiamond-white-prestige' ) . '</h2>';
	echo '<div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">';

	foreach ( $reasons as $reason ) {
		echo '<div class="reason-card text-center">';
		echo '<div class="icon w-12 h-12 mx-auto mb-4 text-bd-gold">';
		echo bdwp_get_icon( $reason['icon'], 'w-full h-full' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>';
		echo '<h3 class="font-semibold text-lg mb-2">' . esc_html( $reason['title'] ) . '</h3>';
		echo '<p class="text-bd-gray-600">' . esc_html( $reason['desc'] ) . '</p>';
		echo '</div>';
	}

	echo '</div>';
	echo '</div>';
	echo '</section>';
}

/**
 * Product 4C panel (Cut, Color, Clarity, Carat) - for diamond products
 *
 * @since 1.0.0
 */
function bdwp_product_4c_panel() {
	global $product;

	// Check if product has diamond 4C data (via ACF)
	$has_4c = get_field( 'bdwp_has_4c', $product->get_id() );

	if ( ! $has_4c ) {
		return;
	}

	$four_c = array(
		'cut'     => get_field( 'bdwp_4c_cut', $product->get_id() ),
		'color'   => get_field( 'bdwp_4c_color', $product->get_id() ),
		'clarity' => get_field( 'bdwp_4c_clarity', $product->get_id() ),
		'carat'   => get_field( 'bdwp_4c_carat', $product->get_id() ),
	);

	echo '<section class="product-4c bg-white border border-bd-gray-200 rounded-lg p-8 my-12">';
	echo '<h2 class="text-2xl font-display font-bold mb-6">' . esc_html__( 'Charakterystyka diamentu - 4C', 'bigdiamond-white-prestige' ) . '</h2>';
	echo '<div class="grid md:grid-cols-4 gap-6">';

	$labels = array(
		'cut'     => __( 'Szlif (Cut)', 'bigdiamond-white-prestige' ),
		'color'   => __( 'Kolor (Color)', 'bigdiamond-white-prestige' ),
		'clarity' => __( 'Czysto?? (Clarity)', 'bigdiamond-white-prestige' ),
		'carat'   => __( 'Masa (Carat)', 'bigdiamond-white-prestige' ),
	);

	foreach ( $four_c as $key => $value ) {
		if ( $value ) {
			echo '<div class="4c-item text-center">';
			echo '<div class="text-3xl font-display font-bold text-bd-gold mb-2">' . esc_html( $value ) . '</div>';
			echo '<div class="text-sm text-bd-gray-600">' . esc_html( $labels[ $key ] ) . '</div>';
			echo '</div>';
		}
	}

	echo '</div>';
	echo '</section>';
}

/**
 * Product certificates
 *
 * @since 1.0.0
 */
function bdwp_product_certificates() {
	global $product;

	$certificates = get_field( 'bdwp_certificates', $product->get_id() );

	if ( ! $certificates ) {
		return;
	}

	echo '<section class="product-certificates my-12">';
	echo '<h2 class="text-2xl font-display font-bold mb-6">' . esc_html__( 'Certyfikaty i dokumentacja', 'bigdiamond-white-prestige' ) . '</h2>';
	echo '<div class="grid md:grid-cols-3 gap-4">';

	foreach ( $certificates as $cert ) {
		echo '<div class="certificate-card border border-bd-gray-200 rounded-lg p-4">';
		echo '<h3 class="font-semibold mb-2">' . esc_html( $cert['title'] ) . '</h3>';
		echo '<p class="text-sm text-bd-gray-600">' . esc_html( $cert['description'] ) . '</p>';
		echo '</div>';
	}

	echo '</div>';
	echo '</section>';
}

/**
 * Product FAQ section with schema
 *
 * @since 1.0.0
 */
function bdwp_product_faq() {
	global $product;

	$faq = get_field( 'bdwp_product_faq', $product->get_id() );

	if ( ! $faq ) {
		return;
	}

	echo '<section class="product-faq my-12">';
	echo '<h2 class="text-2xl font-display font-bold mb-6">' . esc_html__( 'Najcz??ciej zadawane pytania', 'bigdiamond-white-prestige' ) . '</h2>';
	echo '<div class="faq-accordion space-y-4">';

	$faq_schema = array();

	foreach ( $faq as $index => $item ) {
		echo '<div class="faq-item border border-bd-gray-200 rounded-lg">';
		echo '<button class="faq-question w-full text-left px-6 py-4 font-medium flex justify-between items-center" data-faq-toggle="' . esc_attr( $index ) . '">';
		echo esc_html( $item['question'] );
		echo '<svg class="w-5 h-5 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>';
		echo '</button>';
		echo '<div class="faq-answer px-6 py-4 hidden" id="faq-' . esc_attr( $index ) . '">';
		echo wp_kses_post( wpautop( $item['answer'] ) );
		echo '</div>';
		echo '</div>';

		$faq_schema[] = array(
			'@type'          => 'Question',
			'name'           => $item['question'],
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => wp_strip_all_tags( $item['answer'] ),
			),
		);
	}

	echo '</div>';
	echo '</section>';

	// Output FAQ Schema
	if ( ! empty( $faq_schema ) ) {
		$schema = array(
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => $faq_schema,
		);

		echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>';
	}
}

/**
 * Related products section
 *
 * @since 1.0.0
 */
function bdwp_related_products_section() {
	echo '<section class="related-products my-12">';
	woocommerce_output_related_products();
	echo '</section>';
}

/**
 * Ring Configurator CTA
 *
 * @since 1.0.0
 */
function bdwp_configurator_cta() {
	// Only show on ring products
	if ( ! has_term( 'obraczki', 'product_cat' ) ) {
		return;
	}

	echo '<section class="configurator-cta bg-bd-gold text-white py-12 my-12 rounded-lg">';
	echo '<div class="container mx-auto px-4 text-center">';
	echo '<h2 class="text-3xl font-display font-bold mb-4">' . esc_html__( 'Zaprojektuj idealne obr?czki', 'bigdiamond-white-prestige' ) . '</h2>';
	echo '<p class="text-xl mb-6">' . esc_html__( 'U?yj naszego konfiguratora, aby stworzy? obr?czki dok?adnie wed?ug Twoich wyobra?e?', 'bigdiamond-white-prestige' ) . '</p>';
	echo '<a href="' . esc_url( home_url( '/konfigurator-obraczek' ) ) . '" class="inline-block bg-white text-bd-gold px-8 py-3 rounded-md font-semibold hover:bg-bd-cream transition-colors">';
	echo esc_html__( 'Uruchom konfigurator', 'bigdiamond-white-prestige' );
	echo '</a>';
	echo '</div>';
	echo '</section>';
}
