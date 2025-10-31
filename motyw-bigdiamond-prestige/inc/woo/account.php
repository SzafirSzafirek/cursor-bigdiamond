<?php
/**
 * WooCommerce Customer Account
 *
 * Customizes My Account page, order management, returns,
 * and customer dashboard.
 *
 * @package BigDIAMOND_White_Prestige
 * @since   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add custom My Account menu items
 *
 * @param array $items Menu items.
 * @return array Modified menu items.
 * @since 1.0.0
 */
function bdwp_custom_account_menu_items( $items ) {
	// Remove downloads if not used
	unset( $items['downloads'] );

	// Add custom pages
	$custom_items = array(
		'custom-projects' => __( 'Moje projekty', 'bigdiamond-white-prestige' ),
		'warranties'      => __( 'Gwarancje', 'bigdiamond-white-prestige' ),
		'wishlist'        => __( 'Lista ?ycze?', 'bigdiamond-white-prestige' ),
	);

	// Insert custom items before logout
	$logout = $items['customer-logout'];
	unset( $items['customer-logout'] );

	$items = array_merge( $items, $custom_items );
	$items['customer-logout'] = $logout;

	return $items;
}
add_filter( 'woocommerce_account_menu_items', 'bdwp_custom_account_menu_items' );

/**
 * Register custom account endpoints
 *
 * @since 1.0.0
 */
function bdwp_register_account_endpoints() {
	add_rewrite_endpoint( 'custom-projects', EP_ROOT | EP_PAGES );
	add_rewrite_endpoint( 'warranties', EP_ROOT | EP_PAGES );
	add_rewrite_endpoint( 'wishlist', EP_ROOT | EP_PAGES );
}
add_action( 'init', 'bdwp_register_account_endpoints' );

/**
 * Custom Projects endpoint content
 *
 * @since 1.0.0
 */
function bdwp_custom_projects_endpoint_content() {
	$customer_id = get_current_user_id();

	$args = array(
		'post_type'      => 'custom_project',
		'posts_per_page' => -1,
		'author'         => $customer_id,
		'orderby'        => 'date',
		'order'          => 'DESC',
	);

	$projects = new WP_Query( $args );

	echo '<h2 class="text-2xl font-display font-bold mb-6">' . esc_html__( 'Moje projekty na zam?wienie', 'bigdiamond-white-prestige' ) . '</h2>';

	if ( $projects->have_posts() ) {
		echo '<div class="custom-projects-grid space-y-6">';

		while ( $projects->have_posts() ) {
			$projects->the_post();

			$status = get_post_meta( get_the_ID(), '_project_status', true );
			$status_label = bdwp_get_project_status_label( $status );

			?>
			<div class="project-card bg-white border border-bd-gray-200 rounded-lg p-6">
				<div class="flex justify-between items-start mb-4">
					<div>
						<h3 class="text-lg font-semibold mb-1">
							<a href="<?php echo esc_url( get_permalink() ); ?>" class="text-bd-charcoal hover:text-bd-gold">
								<?php the_title(); ?>
							</a>
						</h3>
						<p class="text-sm text-bd-gray-600"><?php echo esc_html( get_the_date() ); ?></p>
					</div>
					<span class="status-badge px-3 py-1 rounded-full text-sm font-medium <?php echo esc_attr( bdwp_get_status_class( $status ) ); ?>">
						<?php echo esc_html( $status_label ); ?>
					</span>
				</div>

				<div class="project-excerpt text-bd-gray-700 mb-4">
					<?php echo wp_kses_post( get_the_excerpt() ); ?>
				</div>

				<a href="<?php echo esc_url( get_permalink() ); ?>" class="inline-flex items-center text-bd-gold hover:underline">
					<?php esc_html_e( 'Zobacz szczeg??y', 'bigdiamond-white-prestige' ); ?>
					<?php echo bdwp_get_icon( 'arrow-right', 'w-4 h-4 ml-1' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</a>
			</div>
			<?php
		}

		echo '</div>';

		wp_reset_postdata();
	} else {
		echo '<div class="empty-state text-center py-12">';
		echo '<p class="text-bd-gray-600 mb-4">' . esc_html__( 'Nie masz jeszcze ?adnych projekt?w na zam?wienie.', 'bigdiamond-white-prestige' ) . '</p>';
		echo '<a href="' . esc_url( home_url( '/projektowanie-na-zamowienie' ) ) . '" class="inline-block bg-bd-gold text-white px-6 py-3 rounded-md hover:bg-opacity-90">';
		echo esc_html__( 'Rozpocznij projekt', 'bigdiamond-white-prestige' );
		echo '</a>';
		echo '</div>';
	}
}
add_action( 'woocommerce_account_custom-projects_endpoint', 'bdwp_custom_projects_endpoint_content' );

/**
 * Warranties endpoint content
 *
 * @since 1.0.0
 */
function bdwp_warranties_endpoint_content() {
	$customer_id = get_current_user_id();
	$customer_orders = wc_get_orders( array(
		'customer_id' => $customer_id,
		'limit'       => -1,
		'status'      => array( 'wc-completed' ),
	) );

	echo '<h2 class="text-2xl font-display font-bold mb-6">' . esc_html__( 'Gwarancje i certyfikaty', 'bigdiamond-white-prestige' ) . '</h2>';

	if ( ! empty( $customer_orders ) ) {
		echo '<div class="warranties-list space-y-4">';

		foreach ( $customer_orders as $order ) {
			foreach ( $order->get_items() as $item ) {
				$product = $item->get_product();
				$warranty_file = get_post_meta( $product->get_id(), '_warranty_certificate', true );

				if ( ! $warranty_file ) {
					continue;
				}

				?>
				<div class="warranty-card bg-white border border-bd-gray-200 rounded-lg p-6 flex items-center justify-between">
					<div>
						<h3 class="font-semibold text-lg mb-1"><?php echo esc_html( $product->get_name() ); ?></h3>
						<p class="text-sm text-bd-gray-600">
							<?php
							printf(
								/* translators: 1: order number, 2: order date */
								esc_html__( 'Zam?wienie #%1$s ? %2$s', 'bigdiamond-white-prestige' ),
								$order->get_order_number(),
								$order->get_date_created()->date_i18n( 'd.m.Y' )
							);
							?>
						</p>
					</div>
					<a href="<?php echo esc_url( $warranty_file ); ?>" target="_blank" class="inline-flex items-center px-4 py-2 bg-bd-gold text-white rounded-md hover:bg-opacity-90">
						<?php esc_html_e( 'Pobierz certyfikat', 'bigdiamond-white-prestige' ); ?>
					</a>
				</div>
				<?php
			}
		}

		echo '</div>';
	} else {
		echo '<p class="text-bd-gray-600">' . esc_html__( 'Brak dost?pnych certyfikat?w gwarancyjnych.', 'bigdiamond-white-prestige' ) . '</p>';
	}
}
add_action( 'woocommerce_account_warranties_endpoint', 'bdwp_warranties_endpoint_content' );

/**
 * Enhanced order details
 *
 * @param WC_Order $order Order object.
 * @since 1.0.0
 */
function bdwp_enhanced_order_details( $order ) {
	echo '<div class="order-timeline mt-8 mb-8">';
	echo '<h3 class="text-xl font-semibold mb-4">' . esc_html__( 'Status zam?wienia', 'bigdiamond-white-prestige' ) . '</h3>';

	$statuses = array(
		'processing' => __( 'Przetwarzanie', 'bigdiamond-white-prestige' ),
		'on-hold'    => __( 'Oczekiwanie na p?atno??', 'bigdiamond-white-prestige' ),
		'completed'  => __( 'Zrealizowane', 'bigdiamond-white-prestige' ),
		'refunded'   => __( 'Zwr?cone', 'bigdiamond-white-prestige' ),
	);

	$current_status = $order->get_status();

	echo '<div class="timeline flex flex-col md:flex-row gap-4">';

	foreach ( $statuses as $status => $label ) {
		$is_active = ( $status === $current_status );
		$is_completed = ( array_search( $current_status, array_keys( $statuses ), true ) > array_search( $status, array_keys( $statuses ), true ) );

		$class = 'timeline-item flex-1 text-center';
		if ( $is_active || $is_completed ) {
			$class .= ' active';
		}

		echo '<div class="' . esc_attr( $class ) . '">';
		echo '<div class="timeline-icon w-10 h-10 mx-auto mb-2 rounded-full flex items-center justify-center ' . ( $is_active || $is_completed ? 'bg-bd-gold text-white' : 'bg-bd-gray-200 text-bd-gray-600' ) . '">';
		echo bdwp_get_icon( 'check', 'w-5 h-5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>';
		echo '<p class="text-sm font-medium">' . esc_html( $label ) . '</p>';
		echo '</div>';
	}

	echo '</div>';
	echo '</div>';

	// Tracking number (if available)
	$tracking_number = $order->get_meta( '_tracking_number' );
	if ( $tracking_number ) {
		echo '<div class="tracking-info bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">';
		echo '<h4 class="font-semibold mb-2">' . esc_html__( 'Numer ?ledzenia przesy?ki', 'bigdiamond-white-prestige' ) . '</h4>';
		echo '<p class="font-mono text-lg">' . esc_html( $tracking_number ) . '</p>';
		echo '<a href="' . esc_url( 'https://example-courier.com/track/' . $tracking_number ) . '" target="_blank" class="text-bd-gold underline mt-2 inline-block">';
		echo esc_html__( '?led? przesy?k?', 'bigdiamond-white-prestige' );
		echo '</a>';
		echo '</div>';
	}
}
add_action( 'woocommerce_order_details_before_order_table', 'bdwp_enhanced_order_details' );

/**
 * Add return request button
 *
 * @param array    $actions Order actions.
 * @param WC_Order $order   Order object.
 * @return array Modified actions.
 * @since 1.0.0
 */
function bdwp_add_return_action( $actions, $order ) {
	// Only show for completed orders within 30 days
	$order_date = $order->get_date_created();
	$thirty_days_ago = new DateTime( '-30 days' );

	if ( 'completed' === $order->get_status() && $order_date > $thirty_days_ago ) {
		$actions['request-return'] = array(
			'url'  => wp_nonce_url( add_query_arg( array(
				'order_id' => $order->get_id(),
				'action'   => 'request_return',
			), wc_get_account_endpoint_url( 'orders' ) ), 'request-return' ),
			'name' => __( 'Z??? wniosek o zwrot', 'bigdiamond-white-prestige' ),
		);
	}

	return $actions;
}
add_filter( 'woocommerce_my_account_my_orders_actions', 'bdwp_add_return_action', 10, 2 );

/**
 * Get project status label
 *
 * @param string $status Status slug.
 * @return string Status label.
 * @since 1.0.0
 */
function bdwp_get_project_status_label( $status ) {
	$labels = array(
		'brief_received' => __( 'Brief otrzymany', 'bigdiamond-white-prestige' ),
		'concept_ready'  => __( 'Koncepcja gotowa', 'bigdiamond-white-prestige' ),
		'cad_approved'   => __( 'CAD zatwierdzony', 'bigdiamond-white-prestige' ),
		'in_production'  => __( 'W produkcji', 'bigdiamond-white-prestige' ),
		'ready_for_pickup' => __( 'Gotowy do odbioru', 'bigdiamond-white-prestige' ),
	);

	return $labels[ $status ] ?? $status;
}

/**
 * Get status CSS class
 *
 * @param string $status Status slug.
 * @return string CSS class.
 * @since 1.0.0
 */
function bdwp_get_status_class( $status ) {
	$classes = array(
		'brief_received'   => 'bg-yellow-100 text-yellow-800',
		'concept_ready'    => 'bg-blue-100 text-blue-800',
		'cad_approved'     => 'bg-purple-100 text-purple-800',
		'in_production'    => 'bg-orange-100 text-orange-800',
		'ready_for_pickup' => 'bg-green-100 text-green-800',
	);

	return $classes[ $status ] ?? 'bg-gray-100 text-gray-800';
}
