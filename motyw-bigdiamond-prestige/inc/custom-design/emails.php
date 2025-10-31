<?php
/**
 * Custom Design Project Emails
 *
 * Triggers email notifications for custom design project events.
 *
 * @package BigDIAMOND_White_Prestige
 * @since   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Send intake email when project is submitted
 *
 * @param int $project_id Project ID.
 * @since 1.0.0
 */
function bdwp_send_custom_design_intake_email( $project_id ) {
	do_action( 'bdwp_custom_design_intake_notification', $project_id );
}
add_action( 'publish_custom_project', 'bdwp_send_custom_design_intake_email' );

/**
 * Send update email when status changes
 *
 * @param int    $project_id Project ID.
 * @param string $new_status New status.
 * @since 1.0.0
 */
function bdwp_send_status_update_email( $project_id, $new_status ) {
	// Only send for certain status changes
	$notify_statuses = array(
		'concept_ready',
		'cad_approved',
		'ready_for_pickup',
	);

	if ( ! in_array( $new_status, $notify_statuses, true ) ) {
		return;
	}

	do_action( 'bdwp_custom_design_status_notification', $project_id, $new_status );
}
add_action( 'bdwp_custom_design_status_changed', 'bdwp_send_status_update_email', 10, 2 );
