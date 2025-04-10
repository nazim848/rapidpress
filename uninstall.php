<?php

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
	exit;
}

require __DIR__ . '/includes/class-rapidpress-options.php';

use RapidPress\RP_Options;

// Check if clean uninstall is enabled
$clean_uninstall = RP_Options::get_option('clean_uninstall');

if ($clean_uninstall == '1') {
	// Delete all plugin options
	$options_to_delete = [
		'rapidpress_options',
		'rapidpress_version',
	];

	foreach ($options_to_delete as $option) {
		delete_option($option);
	}

	// Delete any custom tables if plugin creates any
	// global $wpdb;
	// $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}your_custom_table");

	// Delete transients
	delete_transient('rapidpress_activation_notice');

	// Delete any files or directories created by the plugin
	$upload_dir = wp_upload_dir();
	$combined_dir = trailingslashit($upload_dir['basedir']) . 'rapidpress';

	if (is_dir($combined_dir)) {
		rapidpress_remove_directory($combined_dir);
	}
}

/**
 * Recursively remove a directory and its contents
 *
 * @param string $dir Path to the directory
 * @return bool True on success, false on failure
 */
function rapidpress_remove_directory($dir) {
	require_once(ABSPATH . 'wp-admin/includes/file.php');
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
