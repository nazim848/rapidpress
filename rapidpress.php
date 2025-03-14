<?php
/*
Plugin Name: RapidPress - Turbocharge Website Performance
Description: Boost your WordPress site speed by 2x-5x with advanced optimization techniques including minification, asset management, and performance tweaks.
Version: 1.0.0
Author: Nazim Husain
Author URI: https://nazimansari.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: rapidpress
Domain Path: /languages
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.2

RapidPress is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

RapidPress is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with RapidPress. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

require __DIR__ . '/includes/class-rapidpress-options.php';

use RapidPress\RP_Options;

define('RAPIDPRESS_VERSION', '1.0');
define('RAPIDPRESS_PATH', plugin_dir_path(__FILE__));
define('RAPIDPRESS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('RAPIDPRESS_PLUGIN_FILE', __FILE__);
define('RAPIDPRESS_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 * @return void
 */
function rapidpress_load_textdomain() {
	load_plugin_textdomain(
		'rapidpress',
		false,
		dirname(RAPIDPRESS_PLUGIN_BASENAME) . '/languages/'
	);
}
add_action('plugins_loaded', 'rapidpress_load_textdomain');

// Load the main plugin class
require plugin_dir_path(__FILE__) . 'includes/class-rapidpress.php';

// Check if the plugin version is different from the current version
$rapidpress_version = get_option('rapidpress_version');
//update version
if ($rapidpress_version != RAPIDPRESS_VERSION) {
	update_option('rapidpress_version', RAPIDPRESS_VERSION, false);
}

// Run the plugin
function run_rapidpress() {
	$plugin = new RapidPress\RapidPress();
	$plugin->run();
}
run_rapidpress();

// Activation hook
register_activation_hook(__FILE__, 'rapidpress_activate');

// Deactivation hook
register_deactivation_hook(__FILE__, 'rapidpress_deactivate');

// Activation code
function rapidpress_activate() {
	// Activation code here
	set_transient('rapidpress_activation_notice', true, 5);
}

// Deactivation code
function rapidpress_deactivate() {
	// Check if clean deactivate is enabled
	$clean_deactivate = RP_Options::get_option('clean_deactivate');

	if ($clean_deactivate == '1') {
		// Delete all plugin options
		$options_to_delete = [
			'rapidpress_options',
			'rapidpress_version',
		];

		foreach ($options_to_delete as $option) {
			delete_option($option);
		}
	}
}

// Uninstall code
function rapidpress_uninstall() {
	// Uninstall code here
}
