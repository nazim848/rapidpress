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
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	private function load_dependencies() {
		require_once RAPIDPRESS_PATH . 'inc/class-loader.php';
		require_once RAPIDPRESS_PATH . 'inc/class-rapidpress-options.php';
		require_once RAPIDPRESS_PATH . 'inc/class-core-tweaks.php';
		require_once RAPIDPRESS_PATH . 'inc/class-html-minifier.php';
		require_once RAPIDPRESS_PATH . 'inc/class-css-minifier.php';
		require_once RAPIDPRESS_PATH . 'inc/class-css-combiner.php';
		require_once RAPIDPRESS_PATH . 'inc/class-js-minifier.php';
		require_once RAPIDPRESS_PATH . 'inc/class-js-defer.php';
		require_once RAPIDPRESS_PATH . 'inc/class-js-delay.php';
		require_once RAPIDPRESS_PATH . 'inc/class-optimization-scope.php';
		require_once RAPIDPRESS_PATH . 'inc/class-image-lazy-loading.php';
		require_once RAPIDPRESS_PATH . 'inc/class-page-cache.php';
		require_once RAPIDPRESS_PATH . 'admin/class-admin.php';
		require_once RAPIDPRESS_PATH . 'public/class-public.php';
		require_once RAPIDPRESS_PATH . 'inc/class-asset-manager.php';

		$this->loader = new \RapidPress\Loader();
	}

	private function define_public_hooks() {
		$plugin_public = new \RapidPress\Public_Core($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

		new \RapidPress\Core_Tweaks();
		new \RapidPress\Optimization_Scope();
		new \RapidPress\HTML_Minifier();
		new \RapidPress\CSS_Combiner();
		new \RapidPress\JS_Defer();
		new \RapidPress\Asset_Manager();
		new \RapidPress\Image_Lazy_Loading();
		new \RapidPress\Page_Cache();
	}

	private function define_admin_hooks() {
		$plugin_admin = new \RapidPress\Admin($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
		$this->loader->add_action('admin_init', $plugin_admin, 'register_settings');
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
