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
 * Moves all HRSWP Documents custom post types to the trash.
 *
 * Uses a direct MySQL command with the $wpdb object in order to prevent
 * memory-based timeouts when trying to trash many posts.
 *
 * @since 1.1.0
 *
 * @return int|false The number of rows affected by the query or false if a MySQL error is encountered.
 */
function trash_documents() {
	global $wpdb;

	$result = wp_cache_get( 'trash_posts', admin\get_plugin_info( 'post_type' ) );
	if ( false === $result ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->query(
			$wpdb->prepare(
				"
				UPDATE `$wpdb->posts`
				SET `post_status` = %s
				WHERE `post_type` = %s
				",
				'trash',
				admin\get_plugin_info( 'post_type' )
			)
		);
		wp_cache_set( 'trash_posts', $result, admin\get_plugin_info( 'post_type' ) );
	}

	return $result;
}

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
	unregister_post_type( admin\get_plugin_info( 'post_type' ) );

	flush_rewrite_rules();
}

/**
 * Uninstalls the plugin.
 *
 * Uninstall will remove all options and delete all posts created by the HRS
 * Courses custom post type plugin. Do not need to flush cache/temp or
 * permalinks here, as that will have already been done on deactivation.
 * Uses `get_posts()` and `wp_trash_post()` to do the heavy lifting.
 *
 * Note: `get_posts()` does not return posts with of auto_draft type, so
 * currently these methods will not delete any from the database.
 *
 * @since 1.0.0
 * @since 1.1.0 Add post removal
 */
function uninstall() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	// Delete plugin options.
	delete_option( admin\get_plugin_info( 'option_name' ) );

	// Move all Document posts to the trash.
	trash_documents();
}
