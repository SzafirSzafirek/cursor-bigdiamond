<?php
/**
 * WooCommerce Pricing Logic
 *
 * Handles price display, price ranges for variations,
 * and custom pricing logic for jewelry products.
 *
 * @package BigDIAMOND_White_Prestige
 * @since   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Format variable product price range
 *
 * @param string     $price   Default price HTML.
 * @param WC_Product $product Product object.
 * @return string Modified price HTML.
 * @since 1.0.0
 */
function bdwp_variable_price_format( $price, $product ) {
	if ( ! $product->is_type( 'variable' ) ) {
		return $price;
	}

	$prices = $product->get_variation_prices( true );

	if ( empty( $prices['price'] ) ) {
		return $price;
	}

	$min_price = current( $prices['price'] );
	$max_price = end( $prices['price'] );

	if ( $min_price === $max_price ) {
		return wc_price( $min_price );
	}

	return sprintf(
		/* translators: 1: minimum price, 2: maximum price */
		__( 'Od %1$s', 'bigdiamond-white-prestige' ),
		wc_price( $min_price )
	);
}
add_filter( 'woocommerce_variable_price_html', 'bdwp_variable_price_format', 10, 2 );

/**
 * Add "od" (from) prefix to price ranges
 *
 * @param string $price_html Price HTML.
 * @param string $product Product object.
 * @return string Modified price HTML.
 * @since 1.0.0
 */
function bdwp_add_price_prefix( $price_html, $product ) {
	if ( $product->is_type( 'variable' ) ) {
		$price_html = '<span class="from-price">' . __( 'Od', 'bigdiamond-white-prestige' ) . ' ' . $price_html . '</span>';
	}

	return $price_html;
}
// Commented out - uncomment if "od" prefix is needed
// add_filter( 'woocommerce_get_price_html', 'bdwp_add_price_prefix', 10, 2 );

/**
 * Custom sale price formatting
 *
 * @param string     $price   Price HTML.
 * @param WC_Product $product Product object.
 * @return string Modified price HTML.
 * @since 1.0.0
 */
function bdwp_sale_price_format( $price, $product ) {
	if ( ! $product->is_on_sale() ) {
		return $price;
	}

	$regular_price = $product->get_regular_price();
	$sale_price    = $product->get_sale_price();

	if ( empty( $sale_price ) ) {
		return $price;
	}

	$price_html  = '<del class="original-price text-bd-gray-400 mr-2">' . wc_price( $regular_price ) . '</del>';
	$price_html .= '<ins class="sale-price text-bd-gold font-bold no-underline">' . wc_price( $sale_price ) . '</ins>';

	// Calculate discount percentage
	$discount_percent = round( ( ( $regular_price - $sale_price ) / $regular_price ) * 100 );
	$price_html .= ' <span class="discount-badge ml-2 text-sm bg-red-100 text-red-700 px-2 py-1 rounded">-' . $discount_percent . '%</span>';

	return $price_html;
}
add_filter( 'woocommerce_get_price_html', 'bdwp_sale_price_format', 10, 2 );

/**
 * Hide prices for specific user roles or conditions
 *
 * @param string     $price   Price HTML.
 * @param WC_Product $product Product object.
 * @return string Modified price HTML.
 * @since 1.0.0
 */
function bdwp_conditional_price_display( $price, $product ) {
	if ( ! bdwp_can_see_prices() ) {
		return '<span class="price-login-required">' . __( 'Zaloguj si?, aby zobaczy? ceny', 'bigdiamond-white-prestige' ) . '</span>';
	}

	return $price;
}
// Commented out - uncomment if conditional pricing is needed
// add_filter( 'woocommerce_get_price_html', 'bdwp_conditional_price_display', 20, 2 );

/**
 * Add price suffix (e.g., "brutto", "za sztuk?")
 *
 * @param string     $price   Price HTML.
 * @param WC_Product $product Product object.
 * @return string Modified price HTML.
 * @since 1.0.0
 */
function bdwp_price_suffix( $price, $product ) {
	if ( ! $price ) {
		return $price;
	}

	$suffix = ' <span class="price-suffix text-sm text-bd-gray-600">' . __( 'brutto', 'bigdiamond-white-prestige' ) . '</span>';

	return $price . $suffix;
}
// Commented out - uncomment if price suffix is needed
// add_filter( 'woocommerce_get_price_html', 'bdwp_price_suffix', 30, 2 );

/**
 * Custom pricing for product bundles or sets
 *
 * @param float      $price   Price.
 * @param WC_Product $product Product object.
 * @return float Modified price.
 * @since 1.0.0
 */
function bdwp_bundle_pricing( $price, $product ) {
	// Check if product is marked as bundle via ACF
	$is_bundle = get_field( 'bdwp_is_bundle', $product->get_id() );

	if ( ! $is_bundle ) {
		return $price;
	}

	// Apply bundle discount (example: 10% off)
	$discount_percentage = get_field( 'bdwp_bundle_discount', $product->get_id() ) ?: 10;
	$discounted_price = $price * ( 1 - ( $discount_percentage / 100 ) );

	return $discounted_price;
}
// Commented out - uncomment and configure if bundle pricing is needed
// add_filter( 'woocommerce_product_get_price', 'bdwp_bundle_pricing', 10, 2 );

/**
 * Display installment information
 *
 * @since 1.0.0
 */
function bdwp_display_installments() {
	global $product;

	$price = $product->get_price();

	// Only show for products above certain price threshold
	if ( $price < 1000 ) {
		return;
	}

	$installments = 12; // Number of installments
	$monthly_payment = $price / $installments;

	echo '<div class="installment-info bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">';
	echo '<div class="flex items-center gap-2 mb-2">';
	echo '<svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>';
	echo '<span class="font-semibold">' . esc_html__( 'Raty 0%', 'bigdiamond-white-prestige' ) . '</span>';
	echo '</div>';
	echo '<p class="text-sm text-bd-gray-700">';
	printf(
		/* translators: 1: number of installments, 2: monthly payment amount */
		esc_html__( 'Kup teraz, zap?a? p??niej. %1$d rat po %2$s', 'bigdiamond-white-prestige' ),
		$installments,
		'<strong>' . wc_price( $monthly_payment ) . '</strong>'
	);
	echo '</p>';
	echo '</div>';
}
add_action( 'woocommerce_single_product_summary', 'bdwp_display_installments', 26 );

/**
 * Add tax information below price
 *
 * @since 1.0.0
 */
function bdwp_tax_information() {
	if ( ! wc_tax_enabled() ) {
		return;
	}

	echo '<div class="tax-info text-xs text-bd-gray-600 mt-2">';

	if ( 'incl' === get_option( 'woocommerce_tax_display_shop' ) ) {
		echo esc_html__( 'Cena zawiera podatek VAT', 'bigdiamond-white-prestige' );
	} else {
		echo esc_html__( 'Cena netto, podatek VAT zostanie doliczony przy finalizacji zam?wienia', 'bigdiamond-white-prestige' );
	}

	echo '</div>';
}
add_action( 'woocommerce_single_product_summary', 'bdwp_tax_information', 11 );

/**
 * Show price history (requires price tracking)
 *
 * @since 1.0.0
 */
function bdwp_price_history() {
	global $product;

	// Get price history from custom meta (requires separate tracking implementation)
	$price_history = get_post_meta( $product->get_id(), '_bdwp_price_history', true );

	if ( empty( $price_history ) ) {
		return;
	}

	// Only show if price changed in last 30 days
	$thirty_days_ago = strtotime( '-30 days' );
	$recent_changes = array_filter( $price_history, function( $entry ) use ( $thirty_days_ago ) {
		return $entry['date'] >= $thirty_days_ago;
	} );

	if ( empty( $recent_changes ) ) {
		return;
	}

	echo '<div class="price-history text-xs text-bd-gray-600 mt-2">';
	echo '<button class="underline" data-toggle="price-history-modal">';
	echo esc_html__( 'Zobacz histori? cen', 'bigdiamond-white-prestige' );
	echo '</button>';
	echo '</div>';
}
// Commented out - uncomment if price history tracking is implemented
// add_action( 'woocommerce_single_product_summary', 'bdwp_price_history', 12 );
