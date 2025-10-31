<?php
/**
 * Ring Configurator Product Mapping
 *
 * Maps ring configuration data to WooCommerce products,
 * creates custom cart items, and handles pricing.
 *
 * @package BigDIAMOND_White_Prestige
 * @since   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Map configuration to WooCommerce products
 *
 * @param array $config_data Configuration data.
 * @return array Products to add to cart.
 * @since 1.0.0
 */
function bdwp_map_config_to_products( $config_data ) {
	$products = array();

	// Option 1: Map to existing custom ring product
	$custom_ring_product_id = get_option( 'bdwp_custom_ring_product_id' );

	if ( $custom_ring_product_id ) {
		// Add Ring 1
		$products[] = array(
			'product_id'     => $custom_ring_product_id,
			'quantity'       => 1,
			'cart_item_data' => array(
				'custom_ring_data' => $config_data['ring1'],
				'ring_number'      => 1,
				'config_id'        => $config_data['config_id'],
			),
		);

		// Add Ring 2
		$products[] = array(
			'product_id'     => $custom_ring_product_id,
			'quantity'       => 1,
			'cart_item_data' => array(
				'custom_ring_data' => $config_data['ring2'],
				'ring_number'      => 2,
				'config_id'        => $config_data['config_id'],
			),
		);

		return $products;
	}

	// Option 2: Create virtual product on-the-fly
	// This requires creating temporary product or using fees
	$products[] = array(
		'product_id'     => 0, // Special handling needed
		'quantity'       => 2,
		'cart_item_data' => array(
			'custom_rings'  => array( $config_data['ring1'], $config_data['ring2'] ),
			'config_id'     => $config_data['config_id'],
			'custom_price'  => $config_data['ring1']['price'] + $config_data['ring2']['price'],
		),
	);

	return $products;
}

/**
 * Set custom price for configured rings
 *
 * @param array $cart_item Cart item data.
 * @return array Modified cart item.
 * @since 1.0.0
 */
function bdwp_set_custom_ring_price( $cart_item ) {
	if ( isset( $cart_item['custom_ring_data'] ) ) {
		$ring_data = $cart_item['custom_ring_data'];

		if ( isset( $ring_data['price'] ) && $ring_data['price'] > 0 ) {
			$cart_item['data']->set_price( $ring_data['price'] );
		}
	}

	return $cart_item;
}
add_filter( 'woocommerce_add_cart_item', 'bdwp_set_custom_ring_price' );

/**
 * Display custom ring details in cart
 *
 * @param array  $item_data Cart item data.
 * @param array  $cart_item Cart item.
 * @return array Modified item data.
 * @since 1.0.0
 */
function bdwp_display_custom_ring_details( $item_data, $cart_item ) {
	if ( ! isset( $cart_item['custom_ring_data'] ) ) {
		return $item_data;
	}

	$ring_data = $cart_item['custom_ring_data'];

	$item_data[] = array(
		'key'   => __( 'Obr?czka', 'bigdiamond-white-prestige' ),
		'value' => sprintf( __( 'Nr %d', 'bigdiamond-white-prestige' ), $cart_item['ring_number'] ),
	);

	if ( ! empty( $ring_data['material'] ) ) {
		$item_data[] = array(
			'key'   => __( 'Materia?', 'bigdiamond-white-prestige' ),
			'value' => $ring_data['material'],
		);
	}

	if ( ! empty( $ring_data['finish'] ) ) {
		$item_data[] = array(
			'key'   => __( 'Wyko?czenie', 'bigdiamond-white-prestige' ),
			'value' => $ring_data['finish'],
		);
	}

	if ( ! empty( $ring_data['width'] ) ) {
		$item_data[] = array(
			'key'   => __( 'Szeroko??', 'bigdiamond-white-prestige' ),
			'value' => $ring_data['width'] . ' mm',
		);
	}

	if ( ! empty( $ring_data['size'] ) ) {
		$item_data[] = array(
			'key'   => __( 'Rozmiar', 'bigdiamond-white-prestige' ),
			'value' => $ring_data['size'],
		);
	}

	if ( ! empty( $ring_data['engraving'] ) ) {
		$item_data[] = array(
			'key'   => __( 'Grawer', 'bigdiamond-white-prestige' ),
			'value' => $ring_data['engraving'],
		);
	}

	if ( ! empty( $ring_data['stones'] ) ) {
		$item_data[] = array(
			'key'   => __( 'Kamienie', 'bigdiamond-white-prestige' ),
			'value' => implode( ', ', $ring_data['stones'] ),
		);
	}

	return $item_data;
}
add_filter( 'woocommerce_get_item_data', 'bdwp_display_custom_ring_details', 10, 2 );

/**
 * Save custom ring data to order item meta
 *
 * @param WC_Order_Item $item Order item.
 * @param string        $cart_item_key Cart item key.
 * @param array         $values Cart item values.
 * @param WC_Order      $order Order object.
 * @since 1.0.0
 */
function bdwp_save_custom_ring_order_meta( $item, $cart_item_key, $values, $order ) {
	if ( isset( $values['custom_ring_data'] ) ) {
		$item->add_meta_data( '_custom_ring_data', $values['custom_ring_data'], true );
		$item->add_meta_data( '_ring_number', $values['ring_number'], true );
		$item->add_meta_data( '_ring_config_id', $values['config_id'], true );
	}
}
add_action( 'woocommerce_checkout_create_order_line_item', 'bdwp_save_custom_ring_order_meta', 10, 4 );

/**
 * Display custom ring details in order
 *
 * @param string       $html       Item meta HTML.
 * @param WC_Order_Item $item       Order item.
 * @param array        $args       Display args.
 * @return string Modified HTML.
 * @since 1.0.0
 */
function bdwp_display_custom_ring_order_meta( $html, $item, $args ) {
	$ring_data = $item->get_meta( '_custom_ring_data' );

	if ( empty( $ring_data ) ) {
		return $html;
	}

	$meta_html = '<ul class="wc-item-meta">';

	$meta_html .= '<li><strong>' . esc_html__( 'Obr?czka:', 'bigdiamond-white-prestige' ) . '</strong> ' . esc_html( sprintf( __( 'Nr %d', 'bigdiamond-white-prestige' ), $item->get_meta( '_ring_number' ) ) ) . '</li>';

	if ( ! empty( $ring_data['material'] ) ) {
		$meta_html .= '<li><strong>' . esc_html__( 'Materia?:', 'bigdiamond-white-prestige' ) . '</strong> ' . esc_html( $ring_data['material'] ) . '</li>';
	}

	if ( ! empty( $ring_data['size'] ) ) {
		$meta_html .= '<li><strong>' . esc_html__( 'Rozmiar:', 'bigdiamond-white-prestige' ) . '</strong> ' . esc_html( $ring_data['size'] ) . '</li>';
	}

	if ( ! empty( $ring_data['engraving'] ) ) {
		$meta_html .= '<li><strong>' . esc_html__( 'Grawer:', 'bigdiamond-white-prestige' ) . '</strong> ' . esc_html( $ring_data['engraving'] ) . '</li>';
	}

	$meta_html .= '</ul>';

	return $html . $meta_html;
}
add_filter( 'woocommerce_order_item_display_meta_value', 'bdwp_display_custom_ring_order_meta', 10, 3 );

/**
 * Add custom ring image to cart item
 *
 * @param string $product_image Product image HTML.
 * @param array  $cart_item     Cart item.
 * @param string $cart_item_key Cart item key.
 * @return string Modified image HTML.
 * @since 1.0.0
 */
function bdwp_custom_ring_cart_image( $product_image, $cart_item, $cart_item_key ) {
	if ( isset( $cart_item['custom_ring_data']['image'] ) && ! empty( $cart_item['custom_ring_data']['image'] ) ) {
		$image_url = $cart_item['custom_ring_data']['image'];
		$product_image = '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr__( 'Skonfigurowana obr?czka', 'bigdiamond-white-prestige' ) . '" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail" />';
	}

	return $product_image;
}
add_filter( 'woocommerce_cart_item_thumbnail', 'bdwp_custom_ring_cart_image', 10, 3 );
