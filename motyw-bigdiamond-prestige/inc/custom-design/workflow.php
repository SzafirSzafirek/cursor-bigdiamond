<?php
/**
 * Custom Design Project Workflow
 *
 * Manages project status transitions, notifications,
 * and workflow automation for custom design projects.
 *
 * @package BigDIAMOND_White_Prestige
 * @since   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Initialize default project status
 *
 * @param int $post_id Post ID.
 * @since 1.0.0
 */
function bdwp_init_project_status( $post_id ) {
	if ( get_post_type( $post_id ) !== 'custom_project' ) {
		return;
	}

	// Set default status if not set
	$status = get_post_meta( $post_id, '_project_status', true );

	if ( empty( $status ) ) {
		update_post_meta( $post_id, '_project_status', 'brief_received' );
	}
}
add_action( 'wp_insert_post', 'bdwp_init_project_status' );

/**
 * Add project status meta box
 *
 * @since 1.0.0
 */
function bdwp_project_status_meta_box() {
	add_meta_box(
		'bdwp_project_status',
		__( 'Status projektu', 'bigdiamond-white-prestige' ),
		'bdwp_project_status_meta_box_callback',
		'custom_project',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'bdwp_project_status_meta_box' );

/**
 * Project status meta box callback
 *
 * @param WP_Post $post Post object.
 * @since 1.0.0
 */
function bdwp_project_status_meta_box_callback( $post ) {
	wp_nonce_field( 'bdwp_project_status_nonce', 'bdwp_project_status_nonce_field' );

	$current_status = get_post_meta( $post->ID, '_project_status', true ) ?: 'brief_received';

	$statuses = array(
		'brief_received'   => __( 'Brief otrzymany', 'bigdiamond-white-prestige' ),
		'concept_ready'    => __( 'Koncepcja gotowa', 'bigdiamond-white-prestige' ),
		'cad_approved'     => __( 'CAD zatwierdzony', 'bigdiamond-white-prestige' ),
		'in_production'    => __( 'W produkcji', 'bigdiamond-white-prestige' ),
		'ready_for_pickup' => __( 'Gotowy do odbioru', 'bigdiamond-white-prestige' ),
	);

	?>
	<div class="bdwp-project-status-selector">
		<select name="bdwp_project_status" id="bdwp_project_status" class="widefat">
			<?php foreach ( $statuses as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $current_status, $value ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>

		<div class="bdwp-status-timeline" style="margin-top: 20px;">
			<p style="font-size: 12px; color: #666; margin-bottom: 10px;">
				<strong><?php esc_html_e( 'Historia status?w:', 'bigdiamond-white-prestige' ); ?></strong>
			</p>
			<?php
			$status_history = get_post_meta( $post->ID, '_status_history', true ) ?: array();

			if ( ! empty( $status_history ) ) {
				echo '<ul style="font-size: 11px; color: #666;">';
				foreach ( array_reverse( $status_history ) as $entry ) {
					printf(
						'<li>%s: <strong>%s</strong> (%s)</li>',
						esc_html( date_i18n( 'd.m.Y H:i', $entry['timestamp'] ) ),
						esc_html( bdwp_get_project_status_label( $entry['status'] ) ),
						esc_html( $entry['user'] )
					);
				}
				echo '</ul>';
			} else {
				echo '<p style="font-size: 11px; color: #999;">' . esc_html__( 'Brak historii', 'bigdiamond-white-prestige' ) . '</p>';
			}
			?>
		</div>

		<p style="margin-top: 15px;">
			<label>
				<input type="checkbox" name="bdwp_notify_customer" value="1" checked>
				<?php esc_html_e( 'Powiadom klienta o zmianie statusu', 'bigdiamond-white-prestige' ); ?>
			</label>
		</p>
	</div>
	<?php
}

/**
 * Save project status
 *
 * @param int $post_id Post ID.
 * @since 1.0.0
 */
function bdwp_save_project_status( $post_id ) {
	// Security checks
	if ( ! isset( $_POST['bdwp_project_status_nonce_field'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bdwp_project_status_nonce_field'] ) ), 'bdwp_project_status_nonce' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( get_post_type( $post_id ) !== 'custom_project' ) {
		return;
	}

	// Get old and new status
	$old_status = get_post_meta( $post_id, '_project_status', true );
	$new_status = isset( $_POST['bdwp_project_status'] ) ? sanitize_text_field( wp_unslash( $_POST['bdwp_project_status'] ) ) : '';

	if ( empty( $new_status ) ) {
		return;
	}

	// Update status
	update_post_meta( $post_id, '_project_status', $new_status );

	// Add to status history
	$status_history = get_post_meta( $post_id, '_status_history', true ) ?: array();
	$current_user = wp_get_current_user();

	$status_history[] = array(
		'status'    => $new_status,
		'timestamp' => time(),
		'user'      => $current_user->display_name,
	);

	update_post_meta( $post_id, '_status_history', $status_history );

	// Trigger status change action if status actually changed
	if ( $old_status !== $new_status ) {
		do_action( 'bdwp_custom_design_status_changed', $post_id, $new_status );

		// Send notification if checkbox is checked
		if ( isset( $_POST['bdwp_notify_customer'] ) ) {
			do_action( 'bdwp_custom_design_status_notification', $post_id, $new_status );
		}
	}
}
add_action( 'save_post', 'bdwp_save_project_status' );

/**
 * Get available status transitions
 *
 * @param string $current_status Current status.
 * @return array Available next statuses.
 * @since 1.0.0
 */
function bdwp_get_available_status_transitions( $current_status ) {
	$transitions = array(
		'brief_received'   => array( 'concept_ready' ),
		'concept_ready'    => array( 'cad_approved', 'brief_received' ),
		'cad_approved'     => array( 'in_production', 'concept_ready' ),
		'in_production'    => array( 'ready_for_pickup', 'cad_approved' ),
		'ready_for_pickup' => array( 'in_production' ),
	);

	return isset( $transitions[ $current_status ] ) ? $transitions[ $current_status ] : array();
}

/**
 * Add quick status change links
 *
 * @param array   $actions Row actions.
 * @param WP_Post $post    Post object.
 * @return array Modified actions.
 * @since 1.0.0
 */
function bdwp_project_row_actions( $actions, $post ) {
	if ( $post->post_type !== 'custom_project' ) {
		return $actions;
	}

	$current_status = get_post_meta( $post->ID, '_project_status', true );
	$next_statuses = bdwp_get_available_status_transitions( $current_status );

	if ( ! empty( $next_statuses ) ) {
		foreach ( $next_statuses as $next_status ) {
			$actions[ 'status_' . $next_status ] = sprintf(
				'<a href="%s">%s</a>',
				wp_nonce_url(
					add_query_arg( array(
						'action'        => 'change_project_status',
						'post'          => $post->ID,
						'new_status'    => $next_status,
					), admin_url( 'edit.php?post_type=custom_project' ) ),
					'change_status_' . $post->ID
				),
				sprintf(
					/* translators: %s: status label */
					__( '? %s', 'bigdiamond-white-prestige' ),
					bdwp_get_project_status_label( $next_status )
				)
			);
		}
	}

	return $actions;
}
add_filter( 'post_row_actions', 'bdwp_project_row_actions', 10, 2 );

/**
 * Handle quick status change
 *
 * @since 1.0.0
 */
function bdwp_handle_quick_status_change() {
	if ( ! isset( $_GET['action'] ) || $_GET['action'] !== 'change_project_status' ) {
		return;
	}

	if ( ! isset( $_GET['post'] ) || ! isset( $_GET['new_status'] ) ) {
		return;
	}

	$post_id = intval( $_GET['post'] );

	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ?? '' ) ), 'change_status_' . $post_id ) ) {
		wp_die( esc_html__( 'Nieprawid?owy token bezpiecze?stwa.', 'bigdiamond-white-prestige' ) );
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		wp_die( esc_html__( 'Brak uprawnie?.', 'bigdiamond-white-prestige' ) );
	}

	$new_status = sanitize_text_field( wp_unslash( $_GET['new_status'] ) );

	update_post_meta( $post_id, '_project_status', $new_status );

	do_action( 'bdwp_custom_design_status_changed', $post_id, $new_status );

	wp_safe_redirect( add_query_arg( 'project_updated', '1', admin_url( 'edit.php?post_type=custom_project' ) ) );
	exit;
}
add_action( 'admin_init', 'bdwp_handle_quick_status_change' );

/**
 * Add status badge to post title
 *
 * @param string $title Post title.
 * @param int    $post_id Post ID.
 * @return string Modified title.
 * @since 1.0.0
 */
function bdwp_add_status_to_title( $title, $post_id = null ) {
	if ( ! is_admin() || ! $post_id || get_post_type( $post_id ) !== 'custom_project' ) {
		return $title;
	}

	$status = get_post_meta( $post_id, '_project_status', true );

	if ( $status ) {
		$status_label = bdwp_get_project_status_label( $status );
		$title .= ' <span style="font-size: 11px; color: #666;">(' . $status_label . ')</span>';
	}

	return $title;
}
// Uncomment if title badge is desired
// add_filter( 'the_title', 'bdwp_add_status_to_title', 10, 2 );
