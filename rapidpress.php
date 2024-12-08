<?php

/**
 * Plugin Name: RapidPress
 * Description: A lightweight and robust WordPress speed optimization plugin with granual control over your website.
 * Version: 1.0
 * Author: Nazim Husain
 * Author URI: https://nazimansari.com
 * License: GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: rapidpress
 * Domain Path: /languages
 */

/**
 * @copyright 2024  Nazim Husain  https://nazimansari.com
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
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
