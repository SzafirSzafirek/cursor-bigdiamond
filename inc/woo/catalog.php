<?php
/**
 * WooCommerce Catalog & Product Listing
 *
 * Handles product archive pages, filters, sorting, and grid layout.
 *
 * @package BigDIAMOND_White_Prestige
 * @since   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add custom sorting options
 *
 * @param array $options Sorting options.
 * @return array Modified options.
 * @since 1.0.0
 */
function bdwp_custom_sorting_options( $options ) {
	$options = array(
		'menu_order' => __( 'Domy?lne sortowanie', 'bigdiamond-white-prestige' ),
		'popularity' => __( 'Popularno??', 'bigdiamond-white-prestige' ),
		'rating'     => __( '?rednia ocena', 'bigdiamond-white-prestige' ),
		'date'       => __( 'Najnowsze', 'bigdiamond-white-prestige' ),
		'price'      => __( 'Cena: rosn?co', 'bigdiamond-white-prestige' ),
		'price-desc' => __( 'Cena: malej?co', 'bigdiamond-white-prestige' ),
	);

	return $options;
}
add_filter( 'woocommerce_catalog_orderby', 'bdwp_custom_sorting_options' );

/**
 * Custom product archive header
 *
 * @since 1.0.0
 */
function bdwp_shop_header() {
	if ( ! is_shop() && ! is_product_category() && ! is_product_tag() ) {
		return;
	}

	$title = '';
	$description = '';

	if ( is_shop() ) {
		$title = get_the_title( wc_get_page_id( 'shop' ) );
		$description = get_the_excerpt( wc_get_page_id( 'shop' ) );
	} elseif ( is_product_category() ) {
		$term = get_queried_object();
		$title = $term->name;
		$description = $term->description;
	}

	?>
	<div class="shop-header bg-bd-cream py-12 mb-8">
		<div class="container mx-auto px-4">
			<h1 class="text-4xl font-display font-bold text-bd-charcoal mb-4">
				<?php echo esc_html( $title ); ?>
			</h1>
			<?php if ( $description ) : ?>
				<div class="text-lg text-bd-gray-600 max-w-3xl">
					<?php echo wp_kses_post( $description ); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<?php
}
add_action( 'woocommerce_before_main_content', 'bdwp_shop_header', 5 );

/**
 * Add filter sidebar toggle
 *
 * @since 1.0.0
 */
function bdwp_filter_toggle() {
	if ( ! is_active_sidebar( 'shop-sidebar' ) ) {
		return;
	}

	?>
	<button 
		class="filter-toggle inline-flex items-center px-4 py-2 bg-white border border-bd-gray-300 rounded-md mb-6 lg:hidden"
		aria-label="<?php esc_attr_e( 'Poka? filtry', 'bigdiamond-white-prestige' ); ?>"
	>
		<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
			<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
		</svg>
		<?php esc_html_e( 'Filtry', 'bigdiamond-white-prestige' ); ?>
	</button>
	<?php
}
add_action( 'woocommerce_before_shop_loop', 'bdwp_filter_toggle', 15 );

/**
 * Custom product loop item structure
 *
 * @since 1.0.0
 */
function bdwp_product_loop_structure() {
	// Remove default hooks
	remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
	remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
	remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
	remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
	remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
	remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );

	// Add custom hooks
	add_action( 'woocommerce_before_shop_loop_item', 'bdwp_product_card_open', 10 );
	add_action( 'woocommerce_before_shop_loop_item_title', 'bdwp_product_badges', 5 );
	add_action( 'woocommerce_before_shop_loop_item_title', 'bdwp_product_quick_view', 15 );
	add_action( 'woocommerce_shop_loop_item_title', 'bdwp_product_card_title', 10 );
	add_action( 'woocommerce_after_shop_loop_item_title', 'bdwp_product_card_attributes', 5 );
	add_action( 'woocommerce_after_shop_loop_item_title', 'bdwp_product_card_price', 10 );
	add_action( 'woocommerce_after_shop_loop_item', 'bdwp_product_card_actions', 10 );
	add_action( 'woocommerce_after_shop_loop_item', 'bdwp_product_card_close', 20 );
}
add_action( 'init', 'bdwp_product_loop_structure' );

/**
 * Product card opening wrapper
 *
 * @since 1.0.0
 */
function bdwp_product_card_open() {
	echo '<div class="product-card bg-white rounded-lg overflow-hidden shadow-card hover:shadow-elevated transition-shadow duration-300">';
	echo '<a href="' . esc_url( get_permalink() ) . '" class="product-image-wrapper block relative">';
}

/**
 * Product badges (New, Sale, Featured)
 *
 * @since 1.0.0
 */
function bdwp_product_badges() {
	global $product;

	echo '<div class="product-badges absolute top-3 left-3 z-10 flex flex-col gap-2">';

	// Sale badge
	if ( $product->is_on_sale() ) {
		echo '<span class="badge bg-bd-gold text-white text-xs font-medium px-3 py-1 rounded-full">';
		echo esc_html__( 'Promocja', 'bigdiamond-white-prestige' );
		echo '</span>';
	}

	// Featured badge
	if ( $product->is_featured() ) {
		echo '<span class="badge bg-bd-charcoal text-white text-xs font-medium px-3 py-1 rounded-full">';
		echo esc_html__( 'Polecane', 'bigdiamond-white-prestige' );
		echo '</span>';
	}

	echo '</div>';
}

/**
 * Quick view button
 *
 * @since 1.0.0
 */
function bdwp_product_quick_view() {
	global $product;

	?>
	<button 
		class="quick-view-btn absolute bottom-3 right-3 z-10 bg-white p-2 rounded-full shadow-md opacity-0 group-hover:opacity-100 transition-opacity"
		data-product-id="<?php echo esc_attr( $product->get_id() ); ?>"
		aria-label="<?php esc_attr_e( 'Szybki podgl?d', 'bigdiamond-white-prestige' ); ?>"
	>
		<?php echo bdwp_get_icon( 'search', 'w-5 h-5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</button>
	<?php

	echo '</a>'; // Close image wrapper link
}

/**
 * Product card title
 *
 * @since 1.0.0
 */
function bdwp_product_card_title() {
	echo '<div class="product-info p-4">';
	echo '<h3 class="product-title text-lg font-medium text-bd-charcoal mb-2">';
	echo '<a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a>';
	echo '</h3>';
}

/**
 * Product card attributes
 *
 * @since 1.0.0
 */
function bdwp_product_card_attributes() {
	global $product;

	$attributes = bdwp_get_product_attributes( $product->get_id(), array( 'pa_gramatura', 'pa_rozmiar' ) );

	if ( ! empty( $attributes ) ) {
		echo '<div class="product-attributes text-sm text-bd-gray-600 mb-2">';
		foreach ( array_slice( $attributes, 0, 2 ) as $attribute ) {
			echo '<span class="attribute">' . esc_html( $attribute['value'] ) . '</span>';
			if ( $attribute !== end( $attributes ) ) {
				echo ' ? ';
			}
		}
		echo '</div>';
	}
}

/**
 * Product card price
 *
 * @since 1.0.0
 */
function bdwp_product_card_price() {
	global $product;

	if ( bdwp_can_see_prices() ) {
		echo '<div class="product-price text-xl font-semibold text-bd-gold mb-4">';
		echo wp_kses_post( $product->get_price_html() );
		echo '</div>';
	}
}

/**
 * Product card actions
 *
 * @since 1.0.0
 */
function bdwp_product_card_actions() {
	global $product;

	echo '<div class="product-actions flex gap-2">';
	
	// Add to cart button
	woocommerce_template_loop_add_to_cart();

	// Wishlist button (if plugin is active)
	if ( shortcode_exists( 'yith_wcwl_add_to_wishlist' ) ) {
		echo do_shortcode( '[yith_wcwl_add_to_wishlist]' );
	}

	echo '</div>';
}

/**
 * Product card closing wrapper
 *
 * @since 1.0.0
 */
function bdwp_product_card_close() {
	echo '</div>'; // Close product-info
	echo '</div>'; // Close product-card
}

/**
 * Add AJAX filter functionality
 *
 * @since 1.0.0
 */
function bdwp_ajax_filter_products() {
	check_ajax_referer( 'bdwp-woo-nonce', 'nonce' );

	$args = array(
		'post_type'      => 'product',
		'posts_per_page' => 12,
		'paged'          => isset( $_POST['page'] ) ? intval( $_POST['page'] ) : 1,
	);

	// Price filter
	if ( isset( $_POST['min_price'] ) && isset( $_POST['max_price'] ) ) {
		$args['meta_query'][] = array(
			'key'     => '_price',
			'value'   => array( floatval( $_POST['min_price'] ), floatval( $_POST['max_price'] ) ),
			'type'    => 'NUMERIC',
			'compare' => 'BETWEEN',
		);
	}

	// Attribute filters
	$tax_query = array();
	if ( ! empty( $_POST['materials'] ) ) {
		$tax_query[] = array(
			'taxonomy' => 'pa_material',
			'field'    => 'slug',
			'terms'    => array_map( 'sanitize_text_field', $_POST['materials'] ),
		);
	}

	if ( ! empty( $tax_query ) ) {
		$args['tax_query'] = $tax_query;
	}

	$query = new WP_Query( $args );

	ob_start();

	if ( $query->have_posts() ) {
		woocommerce_product_loop_start();

		while ( $query->have_posts() ) {
			$query->the_post();
			wc_get_template_part( 'content', 'product' );
		}

		woocommerce_product_loop_end();
	} else {
		echo '<p>' . esc_html__( 'Nie znaleziono produkt?w.', 'bigdiamond-white-prestige' ) . '</p>';
	}

	$products_html = ob_get_clean();

	wp_send_json_success( array(
		'products' => $products_html,
		'found'    => $query->found_posts,
	) );
}
add_action( 'wp_ajax_bdwp_filter_products', 'bdwp_ajax_filter_products' );
add_action( 'wp_ajax_nopriv_bdwp_filter_products', 'bdwp_ajax_filter_products' );
