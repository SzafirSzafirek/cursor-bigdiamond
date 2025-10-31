<?php
/**
 * Custom Design Project Fields
 *
 * Manages meta fields and ACF field groups for custom design projects.
 * Stores project brief, materials, budget, inspirations, and timeline.
 *
 * @package BigDIAMOND_White_Prestige
 * @since   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register ACF field group for custom projects
 *
 * @since 1.0.0
 */
function bdwp_custom_project_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group( array(
		'key'      => 'group_custom_project',
		'title'    => __( 'Szczeg??y projektu', 'bigdiamond-white-prestige' ),
		'fields'   => array(
			// Customer Information
			array(
				'key'          => 'field_customer_name',
				'label'        => __( 'Imi? i nazwisko klienta', 'bigdiamond-white-prestige' ),
				'name'         => 'customer_name',
				'type'         => 'text',
				'required'     => 1,
			),
			array(
				'key'          => 'field_customer_email',
				'label'        => __( 'Email klienta', 'bigdiamond-white-prestige' ),
				'name'         => 'customer_email',
				'type'         => 'email',
				'required'     => 1,
			),
			array(
				'key'          => 'field_customer_phone',
				'label'        => __( 'Telefon klienta', 'bigdiamond-white-prestige' ),
				'name'         => 'customer_phone',
				'type'         => 'text',
			),

			// Project Details
			array(
				'key'          => 'field_project_type',
				'label'        => __( 'Rodzaj projektu', 'bigdiamond-white-prestige' ),
				'name'         => 'project_type',
				'type'         => 'select',
				'choices'      => array(
					'ring'      => __( 'Pier?cionek', 'bigdiamond-white-prestige' ),
					'necklace'  => __( 'Naszyjnik', 'bigdiamond-white-prestige' ),
					'bracelet'  => __( 'Bransoletka', 'bigdiamond-white-prestige' ),
					'earrings'  => __( 'Kolczyki', 'bigdiamond-white-prestige' ),
					'other'     => __( 'Inne', 'bigdiamond-white-prestige' ),
				),
				'required'     => 1,
			),
			array(
				'key'          => 'field_project_brief',
				'label'        => __( 'Brief projektu', 'bigdiamond-white-prestige' ),
				'name'         => 'project_brief',
				'type'         => 'textarea',
				'rows'         => 6,
				'instructions' => __( 'Opis wizji klienta, wymagania, preferencje', 'bigdiamond-white-prestige' ),
			),
			array(
				'key'          => 'field_project_budget',
				'label'        => __( 'Bud?et (PLN)', 'bigdiamond-white-prestige' ),
				'name'         => 'project_budget',
				'type'         => 'number',
				'min'          => 0,
			),
			array(
				'key'          => 'field_preferred_material',
				'label'        => __( 'Preferowany materia?', 'bigdiamond-white-prestige' ),
				'name'         => 'preferred_material',
				'type'         => 'checkbox',
				'choices'      => array(
					'white_gold'   => __( 'Bia?e z?oto', 'bigdiamond-white-prestige' ),
					'yellow_gold'  => __( '???te z?oto', 'bigdiamond-white-prestige' ),
					'rose_gold'    => __( 'R??owe z?oto', 'bigdiamond-white-prestige' ),
					'platinum'     => __( 'Platyna', 'bigdiamond-white-prestige' ),
					'silver'       => __( 'Srebro', 'bigdiamond-white-prestige' ),
				),
			),
			array(
				'key'          => 'field_preferred_stones',
				'label'        => __( 'Preferowane kamienie', 'bigdiamond-white-prestige' ),
				'name'         => 'preferred_stones',
				'type'         => 'checkbox',
				'choices'      => array(
					'diamond'   => __( 'Diament', 'bigdiamond-white-prestige' ),
					'sapphire'  => __( 'Szafir', 'bigdiamond-white-prestige' ),
					'ruby'      => __( 'Rubin', 'bigdiamond-white-prestige' ),
					'emerald'   => __( 'Szmaragd', 'bigdiamond-white-prestige' ),
					'other'     => __( 'Inne', 'bigdiamond-white-prestige' ),
				),
			),

			// Inspirations
			array(
				'key'          => 'field_inspiration_images',
				'label'        => __( 'Zdj?cia inspiracji', 'bigdiamond-white-prestige' ),
				'name'         => 'inspiration_images',
				'type'         => 'gallery',
				'return_format' => 'array',
			),
			array(
				'key'          => 'field_inspiration_notes',
				'label'        => __( 'Notatki dotycz?ce inspiracji', 'bigdiamond-white-prestige' ),
				'name'         => 'inspiration_notes',
				'type'         => 'textarea',
				'rows'         => 4,
			),

			// Timeline
			array(
				'key'          => 'field_deadline',
				'label'        => __( 'Preferowany termin realizacji', 'bigdiamond-white-prestige' ),
				'name'         => 'deadline',
				'type'         => 'date_picker',
				'display_format' => 'd/m/Y',
				'return_format' => 'Y-m-d',
			),

			// Design Files
			array(
				'key'          => 'field_concept_files',
				'label'        => __( 'Pliki koncepcyjne', 'bigdiamond-white-prestige' ),
				'name'         => 'concept_files',
				'type'         => 'gallery',
				'instructions' => __( 'Renderingi, szkice, projekty', 'bigdiamond-white-prestige' ),
			),
			array(
				'key'          => 'field_cad_files',
				'label'        => __( 'Pliki CAD', 'bigdiamond-white-prestige' ),
				'name'         => 'cad_files',
				'type'         => 'file',
				'return_format' => 'array',
				'multiple'     => 1,
			),

			// Production Notes
			array(
				'key'          => 'field_production_notes',
				'label'        => __( 'Notatki produkcyjne', 'bigdiamond-white-prestige' ),
				'name'         => 'production_notes',
				'type'         => 'wysiwyg',
				'tabs'         => 'visual',
				'toolbar'      => 'basic',
			),
			array(
				'key'          => 'field_completion_photos',
				'label'        => __( 'Zdj?cia gotowego produktu', 'bigdiamond-white-prestige' ),
				'name'         => 'completion_photos',
				'type'         => 'gallery',
			),
		),
		'location' => array(
			array(
				array(
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'custom_project',
				),
			),
		),
	) );
}
add_action( 'acf/init', 'bdwp_custom_project_fields' );

/**
 * Save custom project metadata
 *
 * @param int $post_id Post ID.
 * @since 1.0.0
 */
function bdwp_save_custom_project_meta( $post_id ) {
	if ( wp_is_post_revision( $post_id ) || get_post_type( $post_id ) !== 'custom_project' ) {
		return;
	}

	// Sync ACF fields to post meta for faster queries
	if ( function_exists( 'get_field' ) ) {
		$customer_name = get_field( 'customer_name', $post_id );
		$customer_email = get_field( 'customer_email', $post_id );
		$budget = get_field( 'project_budget', $post_id );

		if ( $customer_name ) {
			update_post_meta( $post_id, '_customer_name', sanitize_text_field( $customer_name ) );
		}

		if ( $customer_email ) {
			update_post_meta( $post_id, '_customer_email', sanitize_email( $customer_email ) );
		}

		if ( $budget ) {
			update_post_meta( $post_id, '_project_budget', floatval( $budget ) );
		}
	}
}
add_action( 'acf/save_post', 'bdwp_save_custom_project_meta', 20 );
