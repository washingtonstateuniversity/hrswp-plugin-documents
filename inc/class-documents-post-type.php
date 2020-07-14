<?php
/**
 * Set up the HRSWP Documents post type
 *
 * @package HRSWP_Documents
 * @since 1.0.0
 */

namespace HrswpDocuments\inc\Documents_Post_Type;

use HrswpDocuments as admin;

/**
 * The HRSWP Documents post type class.
 */
class Documents_Post_Type {
	/**
	 * Sets up the Documents post type.
	 *
	 * @since 1.0.0
	 */
	public function setup() {
		add_action( 'init', array( $this, 'action_register_post_types' ) );
		add_action( 'init', array( $this, 'action_register_post_meta' ) );
	}

	/**
	 * Registers the post types for the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @uses register_post_type, _x, plugins_url, apply_filters
	 * @return void
	 */
	public function action_register_post_types() {
		$documents_labels = array(
			'name'                  => esc_html_x( 'Document Manager', 'post type general name', 'hrswp-documents' ),
			'singular_name'         => esc_html_x( 'Document', 'post type singular name', 'hrswp-documents' ),
			'add_new'               => _x( 'Create Document', 'document', 'hrswp-documents' ),
			'add_new_item'          => esc_html__( 'Document Manager', 'hrswp-documents' ),
			'edit_item'             => esc_html__( 'Edit Document', 'hrswp-documents' ),
			'new_item'              => esc_html__( 'New Document', 'hrswp-documents' ),
			'all_items'             => esc_html__( 'Document Manager', 'hrswp-documents' ),
			'view_item'             => esc_html__( 'View Document', 'hrswp-documents' ),
			'search_items'          => esc_html__( 'Search Documents', 'hrswp-documents' ),
			'not_found'             => esc_html__( 'No documents found.', 'hrswp-documents' ),
			'not_found_in_trash'    => esc_html__( 'No documents found in trash.', 'hrswp-documents' ),
			'parent_item_colon'     => '',
			'menu_name'             => esc_html__( 'Document Manager', 'hrswp-documents' ),
			'featured_image'        => esc_html__( 'Document Thumbnail', 'hrswp-documents' ),
			'set_featured_image'    => esc_html__( 'Set a custom document thumbnail image.', 'hrswp-documents' ),
			'remove_featured_image' => esc_html__( 'Remove custom document thumbnail.', 'hrswp-documents' ),
			'use_featured_image'    => esc_html__( 'Use as document thumbnail', 'hrswp-documents' ),
		);

		$documents_args = array(
			'labels'          => $documents_labels,
			'public'          => true,
			'capability_type' => 'post',
			'hierarchical'    => false,
			'show_in_menu'    => 'upload.php',
			'menu_icon'       => 'dashicons-media-document',
			'supports'        => array(
				'title',
				'editor',
				'author',
				'excerpt',
				'revisions',
				'custom-fields',
				'thumbnail',
			),
			'rewrite'         => array( 'slug' => admin\get_plugin_info( 'slug' ) ),
			'show_in_rest'    => true,
		);

		register_post_type( admin\get_plugin_info( 'post_type' ), $documents_args );
	}

	/**
	 * Registers the post meta.
	 *
	 * @since 1.0.0
	 *
	 * @uses register_post_meta
	 * @return void
	 */
	public function action_register_post_meta() {
		register_post_meta(
			'',
			'_hrswp_document_file_id',
			array(
				'object_subtype' => admin\get_plugin_info( 'post_type' ),
				'show_in_rest'   => true,
				'single'         => true,
				'type'           => 'number',
				'auth_callback'  => function() {
					return current_user_can( 'edit_posts' );
				},
			)
		);

		register_post_meta(
			'',
			'_hrswp_document_file_href',
			array(
				'object_subtype' => admin\get_plugin_info( 'post_type' ),
				'show_in_rest'   => true,
				'single'         => true,
				'type'           => 'string',
				'auth_callback'  => function() {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}

	/**
	 * Returns a singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return Documents_Post_Type Documents post type object.
	 */
	public static function factory() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
			$instance->setup();
		}

		return $instance;
	}

}
