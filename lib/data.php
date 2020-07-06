<?php
/**
 * Manages plugin data.
 *
 * @package HRSWP_Documents
 * @since 1.0.0
 */

namespace HrswpDocuments\lib\data;

use HrswpDocuments as admin;

/**
 * Updates plugin info for on lifecycle or version changes.
 *
 * @since 1.0.0
 */
function update_plugin_info() {
	if ( ! is_admin() || ! function_exists( 'get_plugin_data' ) ) {
		return;
	}
	$plugin_data = get_plugin_data( admin\get_plugin_info( 'plugin_file_uri' ) );

	$stored_version  = admin\get_plugin_info( 'version' );
	$current_version = $plugin_data['Version'];

	// Exit early if either version number is missing.
	if ( ! isset( $stored_version ) || ! isset( $current_version ) ) {
		return;
	}

	// Update the version if just activated or the versions don't match.
	if ( 'activated' === admin\get_plugin_info( 'status' ) || $stored_version !== $current_version ) {
		$meta = array(
			'status'  => 'active',
			'version' => $current_version,
		);

		update_option( admin\get_plugin_info( 'option_name' ), $meta );
	}
}
add_action( 'admin_init', __NAMESPACE__ . '\update_plugin_info' );

/**
 * Activates the plugin.
 *
 * @since 1.0.0
 */
function activate() {
	/**
	 * Track activation with an option because the activation hook fires
	 * before the plugin is actually set up, which prevents taking certain
	 * actions in this method.
	 *
	 * @link https://stackoverflow.com/questions/7738953/is-there-a-way-to-determine-if-a-wordpress-plugin-is-just-installed/13927297#13927297
	 */
	$option = admin\get_plugin_info( 'option_name' );
	$meta   = get_option( $option );
	if ( ! $meta ) {
		add_option(
			$option,
			array(
				'status'  => 'activated',
				'version' => '0.0.0',
			)
		);
	} else {
		$meta['status'] = 'activated';
		update_option( $option, $meta );
	}
}

/**
 * Deactivates the plugin.
 *
 * @since 1.0.0
 */
function deactivate() {
	$slug = admin\get_plugin_info( 'option_name' );
	$meta = get_option( $slug );

	$meta['status'] = 'deactivated';

	update_option( $slug, $meta );
}

/**
 * Uninstalls the plugin.
 *
 * @since 1.0.0
 */
function uninstall() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	// Delete plugin options.
	delete_option( admin\get_plugin_info( 'option_name' ) );
}
