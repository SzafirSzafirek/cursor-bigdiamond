<?php
/**
 * Custom Design Projects Post Type
 *
 * Registers custom_project post type for bespoke jewelry
 * design projects and commissions.
 *
 * @package BigDIAMOND_White_Prestige
 * @since   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Custom Project post type
 *
 * @since 1.0.0
 */
function bdwp_register_custom_project_post_type() {
	$labels = array(
		'name'                  => __( 'Projekty na zam?wienie', 'bigdiamond-white-prestige' ),
		'singular_name'         => __( 'Projekt', 'bigdiamond-white-prestige' ),
		'menu_name'             => __( 'Projekty na zam?wienie', 'bigdiamond-white-prestige' ),
		'add_new'               => __( 'Dodaj nowy', 'bigdiamond-white-prestige' ),
		'add_new_item'          => __( 'Dodaj nowy projekt', 'bigdiamond-white-prestige' ),
		'edit_item'             => __( 'Edytuj projekt', 'bigdiamond-white-prestige' ),
		'new_item'              => __( 'Nowy projekt', 'bigdiamond-white-prestige' ),
		'view_item'             => __( 'Zobacz projekt', 'bigdiamond-white-prestige' ),
		'search_items'          => __( 'Szukaj projekt?w', 'bigdiamond-white-prestige' ),
		'not_found'             => __( 'Nie znaleziono projekt?w', 'bigdiamond-white-prestige' ),
		'not_found_in_trash'    => __( 'Nie znaleziono projekt?w w koszu', 'bigdiamond-white-prestige' ),
		'all_items'             => __( 'Wszystkie projekty', 'bigdiamond-white-prestige' ),
	);

	$args = array(
		'labels'              => $labels,
		'public'              => false,
		'publicly_queryable'  => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_rest'        => true,
		'query_var'           => true,
		'rewrite'             => array( 'slug' => 'projekt' ),
		'capability_type'     => 'post',
		'has_archive'         => false,
		'hierarchical'        => false,
		'menu_position'       => 26,
		'menu_icon'           => 'dashicons-hammer',
		'supports'            => array( 'title', 'editor', 'thumbnail', 'author', 'comments' ),
		'show_in_nav_menus'   => false,
	);

	register_post_type( 'custom_project', $args );
}
add_action( 'init', 'bdwp_register_custom_project_post_type' );

/**
 * Add custom columns to projects list
 *
 * @param array $columns Existing columns.
 * @return array Modified columns.
 * @since 1.0.0
 */
function bdwp_custom_project_columns( $columns ) {
	$new_columns = array();

	$new_columns['cb'] = $columns['cb'];
	$new_columns['title'] = $columns['title'];
	$new_columns['customer'] = __( 'Klient', 'bigdiamond-white-prestige' );
	$new_columns['status'] = __( 'Status', 'bigdiamond-white-prestige' );
	$new_columns['budget'] = __( 'Bud?et', 'bigdiamond-white-prestige' );
	$new_columns['date'] = $columns['date'];

	return $new_columns;
}
add_filter( 'manage_custom_project_posts_columns', 'bdwp_custom_project_columns' );

/**
 * Populate custom columns
 *
 * @param string $column  Column name.
 * @param int    $post_id Post ID.
 * @since 1.0.0
 */
function bdwp_custom_project_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'customer':
			$customer_name = get_post_meta( $post_id, '_customer_name', true );
			$customer_email = get_post_meta( $post_id, '_customer_email', true );

			if ( $customer_name ) {
				echo esc_html( $customer_name );
				if ( $customer_email ) {
					echo '<br><small><a href="mailto:' . esc_attr( $customer_email ) . '">' . esc_html( $customer_email ) . '</a></small>';
				}
			} else {
				echo '?';
			}
			break;

		case 'status':
			$status = get_post_meta( $post_id, '_project_status', true );
			$status_label = bdwp_get_project_status_label( $status );
			$status_class = bdwp_get_status_class( $status );

			echo '<span class="bdwp-status-badge ' . esc_attr( $status_class ) . '" style="padding: 4px 8px; border-radius: 4px; font-size: 12px;">';
			echo esc_html( $status_label );
			echo '</span>';
			break;

		case 'budget':
			$budget = get_post_meta( $post_id, '_project_budget', true );

			if ( $budget ) {
				echo wc_price( $budget ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			} else {
				echo '?';
			}
			break;
	}
}
add_action( 'manage_custom_project_posts_custom_column', 'bdwp_custom_project_column_content', 10, 2 );

/**
 * Make custom columns sortable
 *
 * @param array $columns Sortable columns.
 * @return array Modified columns.
 * @since 1.0.0
 */
function bdwp_custom_project_sortable_columns( $columns ) {
	$columns['status'] = 'project_status';
	$columns['budget'] = 'project_budget';

	return $columns;
}
add_filter( 'manage_edit-custom_project_sortable_columns', 'bdwp_custom_project_sortable_columns' );

/**
 * Handle custom column sorting
 *
 * @param WP_Query $query Query object.
 * @since 1.0.0
 */
function bdwp_custom_project_orderby( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}

	$orderby = $query->get( 'orderby' );

	switch ( $orderby ) {
		case 'project_status':
			$query->set( 'meta_key', '_project_status' );
			$query->set( 'orderby', 'meta_value' );
			break;

		case 'project_budget':
			$query->set( 'meta_key', '_project_budget' );
			$query->set( 'orderby', 'meta_value_num' );
			break;
	}
}
add_action( 'pre_get_posts', 'bdwp_custom_project_orderby' );

/**
 * Add status filter dropdown
 *
 * @since 1.0.0
 */
function bdwp_custom_project_filters() {
	global $typenow;

	if ( 'custom_project' !== $typenow ) {
		return;
	}

	$statuses = array(
		'brief_received'   => __( 'Brief otrzymany', 'bigdiamond-white-prestige' ),
		'concept_ready'    => __( 'Koncepcja gotowa', 'bigdiamond-white-prestige' ),
		'cad_approved'     => __( 'CAD zatwierdzony', 'bigdiamond-white-prestige' ),
		'in_production'    => __( 'W produkcji', 'bigdiamond-white-prestige' ),
		'ready_for_pickup' => __( 'Gotowy do odbioru', 'bigdiamond-white-prestige' ),
	);

	$current_status = isset( $_GET['project_status'] ) ? sanitize_text_field( wp_unslash( $_GET['project_status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	echo '<select name="project_status">';
	echo '<option value="">' . esc_html__( 'Wszystkie statusy', 'bigdiamond-white-prestige' ) . '</option>';

	foreach ( $statuses as $value => $label ) {
		printf(
			'<option value="%s"%s>%s</option>',
			esc_attr( $value ),
			selected( $current_status, $value, false ),
			esc_html( $label )
		);
	}

	echo '</select>';
}
add_action( 'restrict_manage_posts', 'bdwp_custom_project_filters' );

/**
 * Apply status filter
 *
 * @param WP_Query $query Query object.
 * @since 1.0.0
 */
function bdwp_custom_project_filter_query( $query ) {
	global $pagenow, $typenow;

	if ( 'edit.php' !== $pagenow || 'custom_project' !== $typenow || ! is_admin() ) {
		return;
	}

	if ( isset( $_GET['project_status'] ) && ! empty( $_GET['project_status'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$query->set( 'meta_key', '_project_status' );
		$query->set( 'meta_value', sanitize_text_field( wp_unslash( $_GET['project_status'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}
}
add_filter( 'parse_query', 'bdwp_custom_project_filter_query' );

/**
 * Add admin notices for project actions
 *
 * @since 1.0.0
 */
function bdwp_custom_project_admin_notices() {
	if ( isset( $_GET['project_updated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Status projektu zosta? zaktualizowany.', 'bigdiamond-white-prestige' ); ?></p>
		</div>
		<?php
	}
}
add_action( 'admin_notices', 'bdwp_custom_project_admin_notices' );
