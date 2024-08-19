<?php

/**
 * Plugin Name: RapidPress
 * Plugin URI: https://github.com/yourusername/rapidpress
 * Description: A lightweight and robust WordPress optimization plugin
 * Version: 1.0.0
 * Author: Nazim Husain
 * Author URI: https://yourwebsite.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: rapidpress
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

define('RAPIDPRESS_VERSION', '1.0');
define('RAPIDPRESS_PATH', plugin_dir_path(__FILE__));
define('RAPIDPRESS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('RAPIDPRESS_PLUGIN_FILE', __FILE__);

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
	// Deactivation code here
}

// Uninstall code
function rapidpress_uninstall() {
	// Uninstall code here
}
