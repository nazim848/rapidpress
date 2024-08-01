<?php

class RapidPress_Admin {
	private $plugin_name;
	private $version;

	public function __construct($plugin_name, $version) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;

		add_action('admin_menu', array($this, 'add_plugin_admin_menu'));
		add_action('admin_init', array($this, 'register_settings'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
	}

	public function enqueue_styles($hook) {
		if ('toplevel_page_rapidpress' !== $hook) {
			return;
		}
		wp_enqueue_style('rapidpress-admin', plugin_dir_url(__FILE__) . 'css/rapidpress-admin.css', array(), $this->version, 'all');
	}

	public function enqueue_scripts($hook) {
		if ('toplevel_page_rapidpress' !== $hook) {
			return;
		}
		wp_enqueue_script('rapidpress-admin', plugin_dir_url(__FILE__) . 'js/rapidpress-admin.js', array('jquery'), $this->version, false);
	}

	public function add_plugin_admin_menu() {
		add_menu_page(
			'RapidPress Settings',
			'RapidPress',
			'manage_options',
			'rapidpress',
			array($this, 'display_plugin_setup_page'),
			'dashicons-performance',
			80
		);
	}


	public function display_plugin_setup_page() {
		$active_tab = '#dashboard'; // Default to dashboard

		if (isset($_GET['tab'])) {
			$active_tab = '#' . sanitize_text_field($_GET['tab']);
		}

		include_once 'partials/rapidpress-admin-display.php';

		echo "<script>jQuery(document).ready(function($) { setActiveTab('$active_tab'); });</script>";
	}

	public function register_settings() {
		register_setting('rapidpress_options', 'rapidpress_html_minify');
		register_setting('rapidpress_options', 'rapidpress_css_minify');
		// Add more settings here as we add features

		// Add a custom sanitization callback
		add_filter('pre_update_option_rapidpress_html_minify', array($this, 'save_settings_with_tab'), 10, 3);
		add_filter('pre_update_option_rapidpress_css_minify', array($this, 'save_settings_with_tab'), 10, 3);
	}

	public function save_settings_with_tab($value, $old_value, $option) {
		if (isset($_POST['rapidpress_active_tab'])) {
			$tab = ltrim($_POST['rapidpress_active_tab'], '#');
			add_filter('wp_redirect', function ($location) use ($tab) {
				return add_query_arg('tab', $tab, $location);
			});
		}
		return $value;
	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	public function get_version() {
		return $this->version;
	}
}
