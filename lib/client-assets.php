<?php
/**
 * Functions to register and manage client assets (scripts and styles).
 *
 * @package HrswpTheme
 * @since 1.0.0
 */

namespace HrswpDocuments\lib\client_assets;

use HrswpDocuments as admin;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Silence is golden.' );
}

/**
 * Enqueues the plugin editor scripts.
 *
 * @since 1.0.0
 */
function action_enqueue_block_editor_assets() {
	$slug    = admin\get_plugin_info( 'slug' );
	$path    = admin\get_plugin_info( 'plugin_file_uri' );
	$version = admin\get_plugin_info( 'version' );

	wp_enqueue_script(
		$slug . '-script',
		plugins_url( 'build/index.js', $path ),
		array(
			'wp-blob',
			'wp-block-editor',
			'wp-components',
			'wp-compose',
			'wp-data',
			'wp-element',
			'wp-i18n',
		),
		$version,
		true
	);

	wp_enqueue_style(
		$slug . 'editor-style',
		plugins_url( 'build/editor.css', $path ),
		array(),
		$version
	);
}
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\action_enqueue_block_editor_assets' );
