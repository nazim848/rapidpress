<?php

namespace RapidPress;

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
		require_once RAPIDPRESS_PATH . 'includes/class-loader.php';
		require_once RAPIDPRESS_PATH . 'includes/class-html-minifier.php';
		require_once RAPIDPRESS_PATH . 'includes/class-css-minifier.php';
		require_once RAPIDPRESS_PATH . 'includes/class-css-combiner.php';
		require_once RAPIDPRESS_PATH . 'includes/class-js-minifier.php';
		require_once RAPIDPRESS_PATH . 'includes/class-js-defer.php';
		require_once RAPIDPRESS_PATH . 'includes/class-js-delay.php';
		require_once RAPIDPRESS_PATH . 'includes/class-optimization-scope.php';
		require_once RAPIDPRESS_PATH . 'admin/class-admin.php';
		require_once RAPIDPRESS_PATH . 'public/class-public.php';
		require_once RAPIDPRESS_PATH . 'includes/class-asset-manager.php';

		$this->loader = new \RapidPress\Loader();
	}

	private function define_public_hooks() {
		$plugin_public = new \RapidPress\Public_Core($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

		new \RapidPress\Optimization_Scope();
		new \RapidPress\HTML_Minifier();
		new \RapidPress\CSS_Combiner();
		new \RapidPress\JS_Defer();
		new \RapidPress\Asset_Manager();
	}

	private function define_admin_hooks() {
		$plugin_admin = new \RapidPress\Admin($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
		$this->loader->add_action('admin_init', $plugin_admin, 'register_settings');
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
