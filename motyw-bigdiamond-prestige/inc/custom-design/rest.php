<?php
/**
 * Custom Design REST API
 *
 * Provides REST API endpoints for custom design project
 * submission and status tracking.
 *
 * @package BigDIAMOND_White_Prestige
 * @since   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register custom design REST routes
 *
 * @since 1.0.0
 */
function bdwp_register_custom_design_routes() {
	register_rest_route( 'bdwp/v1', '/custom-design/submit', array(
		'methods'             => 'POST',
		'callback'            => 'bdwp_submit_custom_design',
		'permission_callback' => 'bdwp_custom_design_permissions',
		'args'                => array(
			'name'              => array(
				'required'          => true,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'email'             => array(
				'required'          => true,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_email',
			),
			'phone'             => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'project_type'      => array(
				'required'          => true,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'brief'             => array(
				'required'          => true,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_textarea_field',
			),
			'budget'            => array(
				'type'              => 'number',
			),
			'deadline'          => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'materials'         => array(
				'type'              => 'array',
			),
			'stones'            => array(
				'type'              => 'array',
			),
			'inspiration_notes' => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_textarea_field',
			),
		),
	) );

	register_rest_route( 'bdwp/v1', '/custom-design/(?P<id>\d+)', array(
		'methods'             => 'GET',
		'callback'            => 'bdwp_get_custom_design_status',
		'permission_callback' => 'bdwp_custom_design_view_permissions',
	) );

	register_rest_route( 'bdwp/v1', '/custom-design/(?P<id>\d+)/comments', array(
		'methods'             => 'POST',
		'callback'            => 'bdwp_add_custom_design_comment',
		'permission_callback' => 'bdwp_custom_design_comment_permissions',
		'args'                => array(
			'comment'           => array(
				'required'          => true,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_textarea_field',
			),
		),
	) );
}
add_action( 'rest_api_init', 'bdwp_register_custom_design_routes' );

/**
 * Submit custom design project via REST API
 *
 * @param WP_REST_Request $request Request object.
 * @return WP_REST_Response|WP_Error Response object.
 * @since 1.0.0
 */
function bdwp_submit_custom_design( $request ) {
	$params = $request->get_params();

	// Create project post
	$project_id = wp_insert_post( array(
		'post_type'   => 'custom_project',
		'post_title'  => sprintf(
			/* translators: %s: project type */
			__( 'Projekt: %s - %s', 'bigdiamond-white-prestige' ),
			$params['project_type'],
			$params['name']
		),
		'post_content' => $params['brief'],
		'post_status' => 'publish',
		'post_author' => get_current_user_id() ?: 1,
	) );

	if ( is_wp_error( $project_id ) ) {
		return new WP_Error(
			'project_creation_failed',
			__( 'Nie uda?o si? utworzy? projektu', 'bigdiamond-white-prestige' ),
			array( 'status' => 500 )
		);
	}

	// Save metadata
	update_post_meta( $project_id, '_customer_name', $params['name'] );
	update_post_meta( $project_id, '_customer_email', $params['email'] );
	update_post_meta( $project_id, '_customer_phone', $params['phone'] ?? '' );
	update_post_meta( $project_id, '_project_type', $params['project_type'] );
	update_post_meta( $project_id, '_project_budget', $params['budget'] ?? 0 );
	update_post_meta( $project_id, '_project_status', 'brief_received' );

	// Save ACF fields if available
	if ( function_exists( 'update_field' ) ) {
		update_field( 'customer_name', $params['name'], $project_id );
		update_field( 'customer_email', $params['email'], $project_id );
		update_field( 'customer_phone', $params['phone'] ?? '', $project_id );
		update_field( 'project_type', $params['project_type'], $project_id );
		update_field( 'project_brief', $params['brief'], $project_id );
		update_field( 'project_budget', $params['budget'] ?? 0, $project_id );

		if ( ! empty( $params['materials'] ) ) {
			update_field( 'preferred_material', $params['materials'], $project_id );
		}

		if ( ! empty( $params['stones'] ) ) {
			update_field( 'preferred_stones', $params['stones'], $project_id );
		}

		if ( ! empty( $params['deadline'] ) ) {
			update_field( 'deadline', $params['deadline'], $project_id );
		}

		if ( ! empty( $params['inspiration_notes'] ) ) {
			update_field( 'inspiration_notes', $params['inspiration_notes'], $project_id );
		}
	}

	// Trigger intake email
	do_action( 'bdwp_custom_design_intake_notification', $project_id );

	return new WP_REST_Response( array(
		'success'    => true,
		'project_id' => $project_id,
		'message'    => __( 'Projekt zosta? pomy?lnie z?o?ony', 'bigdiamond-white-prestige' ),
	), 201 );
}

/**
 * Get custom design project status
 *
 * @param WP_REST_Request $request Request object.
 * @return WP_REST_Response|WP_Error Response object.
 * @since 1.0.0
 */
function bdwp_get_custom_design_status( $request ) {
	$project_id = (int) $request['id'];

	$project = get_post( $project_id );

	if ( ! $project || $project->post_type !== 'custom_project' ) {
		return new WP_Error(
			'project_not_found',
			__( 'Projekt nie zosta? znaleziony', 'bigdiamond-white-prestige' ),
			array( 'status' => 404 )
		);
	}

	$status = get_post_meta( $project_id, '_project_status', true );
	$status_history = get_post_meta( $project_id, '_status_history', true ) ?: array();

	return new WP_REST_Response( array(
		'project_id'     => $project_id,
		'title'          => get_the_title( $project_id ),
		'status'         => $status,
		'status_label'   => bdwp_get_project_status_label( $status ),
		'status_history' => $status_history,
		'created_date'   => get_the_date( 'c', $project_id ),
	), 200 );
}

/**
 * Add comment to custom design project
 *
 * @param WP_REST_Request $request Request object.
 * @return WP_REST_Response|WP_Error Response object.
 * @since 1.0.0
 */
function bdwp_add_custom_design_comment( $request ) {
	$project_id = (int) $request['id'];
	$comment_text = $request['comment'];

	$comment_id = wp_insert_comment( array(
		'comment_post_ID' => $project_id,
		'comment_content' => $comment_text,
		'comment_author'  => is_user_logged_in() ? wp_get_current_user()->display_name : 'Klient',
		'comment_author_email' => is_user_logged_in() ? wp_get_current_user()->user_email : get_post_meta( $project_id, '_customer_email', true ),
		'comment_approved' => 1,
	) );

	if ( ! $comment_id ) {
		return new WP_Error(
			'comment_failed',
			__( 'Nie uda?o si? doda? komentarza', 'bigdiamond-white-prestige' ),
			array( 'status' => 500 )
		);
	}

	return new WP_REST_Response( array(
		'success'    => true,
		'comment_id' => $comment_id,
		'message'    => __( 'Komentarz zosta? dodany', 'bigdiamond-white-prestige' ),
	), 201 );
}

/**
 * Permission callback for custom design submission
 *
 * @return bool
 * @since 1.0.0
 */
function bdwp_custom_design_permissions() {
	// Allow anyone to submit (could add nonce or recaptcha verification)
	return true;
}

/**
 * Permission callback for viewing custom design
 *
 * @param WP_REST_Request $request Request object.
 * @return bool
 * @since 1.0.0
 */
function bdwp_custom_design_view_permissions( $request ) {
	$project_id = (int) $request['id'];

	// Allow admin or project owner
	if ( current_user_can( 'edit_posts' ) ) {
		return true;
	}

	// Check if current user is the project owner (by email)
	if ( is_user_logged_in() ) {
		$customer_email = get_post_meta( $project_id, '_customer_email', true );
		$current_user_email = wp_get_current_user()->user_email;

		if ( $customer_email === $current_user_email ) {
			return true;
		}
	}

	return false;
}

/**
 * Permission callback for adding comments
 *
 * @param WP_REST_Request $request Request object.
 * @return bool
 * @since 1.0.0
 */
function bdwp_custom_design_comment_permissions( $request ) {
	return bdwp_custom_design_view_permissions( $request );
}
