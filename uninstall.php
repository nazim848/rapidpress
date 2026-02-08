<?php

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
	exit;
}

require __DIR__ . '/inc/class-rapidpress-options.php';
require __DIR__ . '/inc/class-cache-dropin-manager.php';
require __DIR__ . '/inc/class-cache-preloader.php';
require __DIR__ . '/inc/class-cache-store.php';

use RapidPress\RP_Options;
use RapidPress\Cache_Dropin_Manager;
use RapidPress\Cache_Preloader;
use RapidPress\Cache_Store;

// Check if clean uninstall is enabled.
$rapidpress_clean_uninstall = RP_Options::get_option('clean_uninstall');

if ($rapidpress_clean_uninstall == '1') {
	// Delete all plugin options
	$rapidpress_options_to_delete = [
		'rapidpress_options',
		'rapidpress_version',
		'rapidpress_cache_preload_last_run',
		'rapidpress_cache_preload_last_count',
	];

	foreach ($rapidpress_options_to_delete as $rapidpress_option) {
		delete_option($rapidpress_option);
	}

	// Delete any custom tables if plugin creates any
	// global $wpdb;
	// $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}your_custom_table");

	// Delete transients
	delete_transient('rapidpress_activation_notice');

	// Delete any files or directories created by the plugin
	$rapidpress_cache_store = new Cache_Store();
	$rapidpress_base_dir = $rapidpress_cache_store->get_cache_base_dir();

	if ($rapidpress_base_dir !== '' && is_dir($rapidpress_base_dir)) {
		rapidpress_remove_directory($rapidpress_base_dir);
	}
}

Cache_Dropin_Manager::remove_dropin();
Cache_Preloader::clear_schedule();

/**
 * Recursively remove a directory and its contents
 *
 * @param string $dir Path to the directory
 * @return bool True on success, false on failure
 */
function rapidpress_remove_directory($dir) {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	WP_Filesystem();
	global $wp_filesystem;

	if (!$wp_filesystem->is_dir($dir)) {
		return false;
	}

	// Delete directory contents recursively
	if (!$wp_filesystem->delete($dir, true)) {
		return false;
	}

	return true;
}
