<?php

class RapidPress {

	protected $loader;
	protected $plugin_name;
	protected $version;

	public function __construct() {
		$this->version = RAPIDPRESS_VERSION;
		$this->plugin_name = 'rapidpress';
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	private function load_dependencies() {
		require_once RAPIDPRESS_PLUGIN_DIR . 'includes/class-rapidpress-loader.php';
		require_once RAPIDPRESS_PLUGIN_DIR . 'includes/class-rapidpress-html-minifier.php';
		require_once RAPIDPRESS_PLUGIN_DIR . 'includes/class-rapidpress-css-minifier.php';
		require_once RAPIDPRESS_PLUGIN_DIR . 'includes/class-rapidpress-css-combiner.php';
		require_once RAPIDPRESS_PLUGIN_DIR . 'admin/class-rapidpress-admin.php';
		require_once RAPIDPRESS_PLUGIN_DIR . 'public/class-rapidpress-public.php';

		$this->loader = new RapidPress_Loader();
	}

	private function set_locale() {
		add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));
	}

	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'rapidpress',
			false,
			dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
		);
	}

	private function define_admin_hooks() {
		$plugin_admin = new RapidPress_Admin($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
		$this->loader->add_action('admin_init', $plugin_admin, 'register_settings');
	}

	private function define_public_hooks() {
		$plugin_public = new RapidPress_Public($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

		// Initialize HTML Minifier
		new RapidPress_HTML_Minifier();

		// Initialize CSS Combiner
		new RapidPress_CSS_Combiner();

		// Initialize JS Defer
		new RapidPress_JS_Defer();
	}

	public function run() {
		$this->loader->run();
	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	public function get_version() {
		return $this->version;
	}
}
