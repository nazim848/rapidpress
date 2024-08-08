<?php

class RapidPress_Asset_Manager {

	public function __construct() {
		add_action('wp_enqueue_scripts', array($this, 'manage_js_assets'), 9999);
		add_action('wp_print_scripts', array($this, 'final_js_cleanup'), 9999);
		add_filter('script_loader_tag', array($this, 'remove_script_tag'), 10, 2);
	}

	// Add this new method
	public function remove_script_tag($tag, $handle) {
		$js_rules = get_option('rapidpress_js_disable_rules', array());
		$current_url = trailingslashit($this->get_current_url());

		foreach ($js_rules as $rule) {
			$pages = array_map('trailingslashit', array_map('trim', explode("\n", $rule['pages'])));
			if (in_array($current_url, $pages) && $handle === $rule['handle']) {
				return '';
			}
		}

		return $tag;
	}

	// Add this new method
	public function final_js_cleanup() {
		$js_rules = get_option('rapidpress_js_disable_rules', array());
		$current_url = trailingslashit($this->get_current_url());

		foreach ($js_rules as $rule) {
			$pages = array_map('trailingslashit', array_map('trim', explode("\n", $rule['pages'])));
			if (in_array($current_url, $pages)) {
				$this->disable_script($rule['handle']);
			}
		}
	}

	public function manage_js_assets() {
		$js_rules = get_option('rapidpress_js_disable_rules', array());
		$current_url = trailingslashit($this->get_current_url());

		foreach ($js_rules as $rule) {
			$pages = array_map('trailingslashit', array_map('trim', explode("\n", $rule['pages'])));
			if (in_array($current_url, $pages)) {
				$this->disable_script($rule['handle']);
			}
		}
	}

	private function disable_script($handle) {
		if (wp_script_is($handle, 'enqueued')) {
			wp_dequeue_script($handle);
		}
		if (wp_script_is($handle, 'registered')) {
			wp_deregister_script($handle);
		}
	}

	private function get_current_url() {
		global $wp;
		return home_url($wp->request);
	}
}
