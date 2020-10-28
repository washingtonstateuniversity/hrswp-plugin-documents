<?php
/**
 * Plugin Name: HRSWP Documents
 * Version: 1.1.0-alpha.3d9f8e5
 * Description: A WSU HRS WordPress plugin to provide document management.
 * Author: Adam Turner, washingtonstateuniversity
 * Author URI: https://hrs.wsu.edu/
 * Plugin URI: https://github.com/washingtonstateuniversity/hrswp-plugin-documents
 * Text Domain: hrswp-documents
 * Requires at least: 5.5
 * Tested up to: 5.5
 * Requires PHP: 7.0
 *
 * @package HRSWP_Documents
 * @since 1.0.0
 */

namespace HrswpDocuments;

use HrswpDocuments\lib\data;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Starts things up.
pre_init();

if ( false !== verify_dependencies() ) {
	register_activation_hook( __FILE__, __NAMESPACE__ . '\lib\data\activate' );
	register_deactivation_hook( __FILE__, __NAMESPACE__ . '\lib\data\deactivate' );
	register_uninstall_hook( __FILE__, __NAMESPACE__ . '\lib\data\uninstall' );
}

/**
 * Displays a version notice.
 *
 * @since 1.0.0
 */
function wordpress_version_notice() {
	printf(
		'<div class="error"><p>%s</p></div>',
		esc_html__( 'The HRSWP Documents plugin requires WordPress 5.5.0 or later to function properly. Please upgrade WordPress before activating.', 'hrswp-documents' )
	);
}

/**
 * Verifies plugin dependencies.
 *
 * @since 1.0.0
 *
 * @return bool True if all dependencies are met, false if not.
 */
function verify_dependencies() {
	global $wp_version;

	// Get unmodified $wp_version.
	include ABSPATH . WPINC . '/version.php';

	// Remove '-src' from the version string for `version_compare()`.
	$version = ( strpos( '-src', $wp_version ) )
		? str_replace( '-src', '', $wp_version )
		: preg_replace( '/-[A-Za-z-0-9]*$/', '.0', $wp_version );

	if ( version_compare( $version, '5.5.0', '<' ) ) {
		return false;
	}

	return true;
}

/**
 * Verifies plugin dependencies and then loads.
 *
 * @since 1.0.0
 */
function pre_init() {
	if ( false === verify_dependencies() ) {
		add_action( 'admin_notices', __NAMESPACE__ . '\wordpress_version_notice' );
		deactivate_plugins( array( 'hrswp-plugin-documents/hrswp-documents.php' ) );
		return;
	}

	require dirname( __FILE__ ) . '/lib/load.php';
}

/**
 * Retrieves information about the current plugin.
 *
 * @since 1.0.0
 *
 * @param string $type The plugin info to retrieve. Default empty (returns site name).
 * @return string Primarily string values, might be boolean or empty.
 */
function get_plugin_info( $type = '' ) {
	switch ( $type ) {
		case 'option_name':
			$output = 'hrswp_documents_meta';
			break;
		case 'plugin_file_uri':
			$output = __FILE__;
			break;
		case 'post_type':
			$output = 'hrswp_documents';
			break;
		case 'slug':
			$output = 'documents';
			break;
		case 'status':
			$options = get_option( get_plugin_info( 'option_name' ) );
			$output  = $options['status'];
			break;
		case 'version':
			$options = get_option( get_plugin_info( 'option_name' ) );
			$output  = $options['version'];
			break;
		case 'name':
		default:
			$output = 'HRSWP Documents';
			break;
	}

	return $output;
}
