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
		add_filter( 'template_include', array( $this, 'filter_serve_document_file' ), 10, 1 );
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
			'labels'            => $documents_labels,
			'public'            => true,
			'capability_type'   => 'post',
			'hierarchical'      => false,
			'show_in_menu'      => 'upload.php',
			'show_in_nav_menus' => false,
			'menu_icon'         => 'dashicons-media-document',
			'supports'          => array(
				'title',
				'editor',
				'author',
				'excerpt',
				'custom-fields',
				'thumbnail',
			),
			'rewrite'           => array( 'slug' => admin\get_plugin_info( 'slug' ) ),
			'show_in_rest'      => true,
			'template'          => array( array( 'hrswp/document-select' ) ),
			'template_lock'     => 'all',
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
	 * Serves the document file to the browser in place of the template.
	 *
	 * From Ben Balter's WP Document Revisions, @link https://github.com/benbalter/wp-document-revisions
	 *
	 * @since 1.0.0
	 *
	 * @param string $template The requested template.
	 * @return string The default template or document file.
	 */
	public function filter_serve_document_file( $template ) {
		global $post;
		global $wp_query;
		global $wp;

		if ( ! is_single() ) {
			return $template;
		}

		if ( admin\get_plugin_info( 'post_type' ) !== get_post_type( $post ) ) {
			return $template;
		}

		// Send password-protected pages to the default template.
		if ( post_password_required( $post ) ) {
			return $template;
		}

		$file_id = get_post_meta( $post->ID, '_hrswp_document_file_id', true );
		$file    = get_attached_file( $file_id );

		// @todo consider flipping slashes with filter, @see https://github.com/benbalter/wp-document-revisions/blob/master/includes/class-wp-document-revisions.php

		if ( ! is_file( $file ) ) {
			$wp_query->posts          = array();
			$wp_query->queried_object = null;
			$wp->handle_404();

			return get_404_template();
		}

		status_header( 200 );

		$filename = $post->post_name . $this->get_extension( wp_get_attachment_url( $file_id ) );

		$headers = array();

		$mime = wp_check_filetype( $file );
		if ( false === $mime['type'] && function_exists( 'mime_content_type' ) ) {
			$mime['type'] = mime_content_type( $file );
		}

		$mimetype = ( $mime['type'] )
			? $mime['type']
			: 'image/' . substr( $file, strpos( $file, '.' ) + 1 );

		$headers['Content-Disposition'] = sprintf( 'inline; filename="%s"', $filename );

		if ( is_string( $mimetype ) ) {
			$headers['Content-Type'] = $mimetype;
		}

		$headers['Content-Length'] = filesize( $file );

		$last_modified = gmdate( 'D, d M Y H:i:s', filemtime( $file ) );
		$etag          = sprintf( '"%s"', md5( $last_modified ) );

		$headers['Last-Modified'] = sprintf( '%s GMT', $last_modified );
		$headers['ETag']          = $etag;
		$headers['Expires']       = sprintf( '%s GMT', gmdate( 'D, d M Y H:i:s', time() + 100000000 ) );

		foreach ( $headers as $header => $value ) {
			header( $header . ': ' . $value );
		}

		// Support for Conditional GET.
		$client_etag = isset( $_SERVER['HTTP_IF_NONE_MATCH'] )
			? stripslashes( sanitize_text_field( wp_unslash( $_SERVER['HTTP_IF_NONE_MATCH'] ) ) )
			: false;

		if ( ! isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ) {
			$_SERVER['HTTP_IF_MODIFIED_SINCE'] = false;
		}

		$client_last_modified = trim( sanitize_text_field( wp_unslash( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ) );

		// If string is empty, return 0. If not, attempt to parse into a timestamp.
		$client_modified_timestamp = $client_last_modified ? strtotime( $client_last_modified ) : 0;

		// Make a timestamp for the most recent modification.
		$modified_timestamp = strtotime( $last_modified );

		if ( ( $client_last_modified && $client_etag )
			? ( ( $client_modified_timestamp >= $modified_timestamp ) && ( $client_etag === $etag ) )
			: ( ( $client_modified_timestamp >= $modified_timestamp ) || ( $client_etag === $etag ) )
		) {
			status_header( 304 );
			return;
		}

		// Clear output buffer to prevent other plugins from corrupting the file.
		if ( ob_get_level() ) {
			ob_clean();
			flush();
		}

		// Note: We use readfile, and not WP_Filesystem, for memory/performance reasons.
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_readfile
		readfile( $file );
	}

	/**
	 * Returns a file's extension.
	 *
	 * @since 1.0.0
	 *
	 * @param string $file The URL, path, or filename of the file.
	 * @return string The file extension.
	 */
	public function get_extension( $file ) {
		$extension = '.' . pathinfo( $file, PATHINFO_EXTENSION );

		if ( '.' === $extension ) {
			return '';
		}

		return $extension;
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
