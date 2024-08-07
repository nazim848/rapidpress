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

define('RAPIDPRESS_VERSION', '1.0.0');
define('RAPIDPRESS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RAPIDPRESS_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-rapidpress.php';

// The CSS minifier class
require plugin_dir_path(__FILE__) . 'includes/class-rapidpress-css-minifier.php';

// The JS minifier class
require plugin_dir_path(__FILE__) . 'includes/class-rapidpress-js-minifier.php';

// The JS defer class
require plugin_dir_path(__FILE__) . 'includes/class-rapidpress-js-defer.php';

// The JS delay class.
require plugin_dir_path(__FILE__) . 'includes/class-rapidpress-js-delay.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 */
function run_rapidpress() {
	$plugin = new RapidPress();
	$plugin->run();
}
run_rapidpress();

/**
 * Activation hook.
 */
register_activation_hook(__FILE__, 'rapidpress_activate');

/**
 * Deactivation hook.
 */
register_deactivation_hook(__FILE__, 'rapidpress_deactivate');

/**
 * The code that runs during plugin activation.
 */
function rapidpress_activate() {
	// Activation code here
}

/**
 * The code that runs during plugin deactivation.
 */
function rapidpress_deactivate() {
	// Deactivation code here
}
