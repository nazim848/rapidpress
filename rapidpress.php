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

require plugin_dir_path(__FILE__) . 'includes/class-rapidpress.php';
require plugin_dir_path(__FILE__) . 'includes/class-rapidpress-css-minifier.php';

function run_rapidpress() {
	$plugin = new RapidPress();
	$plugin->run();
}
run_rapidpress();
