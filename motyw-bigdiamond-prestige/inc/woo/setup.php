<?php
/**
 * WooCommerce Setup and Configuration
 *
 * Registers WooCommerce support, customizes shop behavior,
 * and configures basic WooCommerce features.
 *
 * @package BigDIAMOND_White_Prestige
 * @since   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Declare WooCommerce support
 *
 * @since 1.0.0
 */
function bdwp_woo_support() {
	add_theme_support( 'woocommerce', array(
		'thumbnail_image_width' => 800,
		'gallery_thumbnail_image_width' => 150,
		'single_image_width'    => 1200,
		'product_grid'          => array(
			'default_rows'    => 3,
			'min_rows'        => 2,
			'max_rows'        => 8,
			'default_columns' => 3,
			'min_columns'     => 2,
			'max_columns'     => 4,
		),
	) );

	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'bdwp_woo_support' );

/**
 * Set products per page
 *
 * @return int Number of products.
 * @since 1.0.0
 */
function bdwp_products_per_page() {
	return 12;
}
add_filter( 'loop_shop_per_page', 'bdwp_products_per_page' );

/**
 * Set products per row
 *
 * @return int Number of columns.
 * @since 1.0.0
 */
function bdwp_products_per_row() {
	return 3;
}
add_filter( 'loop_shop_columns', 'bdwp_products_per_row' );

/**
 * Disable default WooCommerce styles
 * We use custom Tailwind-based styles instead
 *
 * @param array $styles Default WooCommerce styles.
 * @return array Modified styles array.
 * @since 1.0.0
 */
function bdwp_dequeue_woo_styles( $styles ) {
	unset( $styles['woocommerce-general'] );
	unset( $styles['woocommerce-layout'] );
	unset( $styles['woocommerce-smallscreen'] );
	return $styles;
}
add_filter( 'woocommerce_enqueue_styles', 'bdwp_dequeue_woo_styles' );

/**
 * Register product taxonomies
 *
 * @since 1.0.0
 */
function bdwp_register_product_taxonomies() {
	// Material attribute (pa_material)
	register_taxonomy( 'pa_material', 'product', array(
		'label'        => __( 'Materia?', 'bigdiamond-white-prestige' ),
		'hierarchical' => false,
		'public'       => true,
		'show_ui'      => true,
		'show_in_nav_menus' => true,
		'rewrite'      => array( 'slug' => 'material' ),
	) );

	// Stone attribute (pa_kamien)
	register_taxonomy( 'pa_kamien', 'product', array(
		'label'        => __( 'Kamie?', 'bigdiamond-white-prestige' ),
		'hierarchical' => false,
		'public'       => true,
		'show_ui'      => true,
		'show_in_nav_menus' => true,
		'rewrite'      => array( 'slug' => 'kamien' ),
	) );

	// Color attribute (pa_kolor)
	register_taxonomy( 'pa_kolor', 'product', array(
		'label'        => __( 'Kolor', 'bigdiamond-white-prestige' ),
		'hierarchical' => false,
		'public'       => true,
		'show_ui'      => true,
		'show_in_nav_menus' => true,
		'rewrite'      => array( 'slug' => 'kolor' ),
	) );

	// Theme/Motif attribute (pa_motyw)
	register_taxonomy( 'pa_motyw', 'product', array(
		'label'        => __( 'Motyw', 'bigdiamond-white-prestige' ),
		'hierarchical' => false,
		'public'       => true,
		'show_ui'      => true,
		'show_in_nav_menus' => true,
		'rewrite'      => array( 'slug' => 'motyw' ),
	) );
}
add_action( 'init', 'bdwp_register_product_taxonomies', 20 );

/**
 * Customize breadcrumbs
 *
 * @param array $args Breadcrumb arguments.
 * @return array Modified arguments.
 * @since 1.0.0
 */
function bdwp_woo_breadcrumbs( $args ) {
	$args['delimiter']   = '<span class="mx-2 text-bd-gray-400">/</span>';
	$args['wrap_before'] = '<nav class="woocommerce-breadcrumb mb-8 text-sm" aria-label="breadcrumb"><ol class="flex items-center">';
	$args['wrap_after']  = '</ol></nav>';
	$args['before']      = '<li>';
	$args['after']       = '</li>';
	$args['home']        = __( 'Start', 'bigdiamond-white-prestige' );

	return $args;
}
add_filter( 'woocommerce_breadcrumb_defaults', 'bdwp_woo_breadcrumbs' );

/**
 * Add mini-cart to header
 *
 * @since 1.0.0
 */
function bdwp_mini_cart_fragment( $fragments ) {
	ob_start();
	?>
	<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="relative inline-flex items-center">
		<?php echo bdwp_get_icon( 'cart', 'w-6 h-6' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php if ( WC()->cart->get_cart_contents_count() > 0 ) : ?>
			<span class="absolute -top-2 -right-2 bg-bd-gold text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
				<?php echo esc_html( WC()->cart->get_cart_contents_count() ); ?>
			</span>
		<?php endif; ?>
	</a>
	<?php
	$fragments['a.mini-cart-toggle'] = ob_get_clean();

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'bdwp_mini_cart_fragment' );

/**
 * Customize "Add to Cart" button text
 *
 * @param string     $text Button text.
 * @param WC_Product $product Product object.
 * @return string Modified button text.
 * @since 1.0.0
 */
function bdwp_add_to_cart_text( $text, $product ) {
	if ( $product->is_type( 'simple' ) ) {
		return __( 'Dodaj do koszyka', 'bigdiamond-white-prestige' );
	}

	if ( $product->is_type( 'variable' ) ) {
		return __( 'Wybierz opcje', 'bigdiamond-white-prestige' );
	}

	return $text;
}
add_filter( 'woocommerce_product_add_to_cart_text', 'bdwp_add_to_cart_text', 10, 2 );
add_filter( 'woocommerce_product_single_add_to_cart_text', 'bdwp_add_to_cart_text', 10, 2 );

/**
 * Remove unwanted WooCommerce actions
 *
 * @since 1.0.0
 */
function bdwp_remove_woo_hooks() {
	// Remove default sorting dropdown (we'll add custom one)
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );

	// Remove result count
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );

	// Customize product tabs
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
	add_action( 'woocommerce_after_single_product_summary', 'bdwp_custom_product_tabs', 10 );
}
add_action( 'init', 'bdwp_remove_woo_hooks' );

/**
 * Custom product tabs layout
 *
 * @since 1.0.0
 */
function bdwp_custom_product_tabs() {
	$tabs = apply_filters( 'woocommerce_product_tabs', array() );

	if ( ! empty( $tabs ) ) {
		echo '<div class="bdwp-product-tabs mt-12">';
		echo '<div class="tabs-navigation flex border-b border-bd-gray-200 mb-6">';

		foreach ( $tabs as $key => $tab ) {
			echo '<button class="tab-link px-6 py-4 text-sm font-medium" data-tab="' . esc_attr( $key ) . '">';
			echo esc_html( $tab['title'] );
			echo '</button>';
		}

		echo '</div>';

		foreach ( $tabs as $key => $tab ) {
			echo '<div class="tab-content hidden" id="tab-' . esc_attr( $key ) . '">';
			call_user_func( $tab['callback'], $key, $tab );
			echo '</div>';
		}

		echo '</div>';
	}
}
