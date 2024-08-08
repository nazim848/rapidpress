<?php

class RapidPress_Asset_Manager {

	public function __construct() {
		add_action('wp_enqueue_scripts', array($this, 'manage_js_assets'), 9999);
		add_action('wp_print_scripts', array($this, 'final_js_cleanup'), 9999);
		add_filter('script_loader_tag', array($this, 'remove_script_tag'), 10, 2);
	}


	public function final_js_cleanup() {
		$js_rules = get_option('rapidpress_js_disable_rules', array());
		$current_url = trailingslashit($this->get_current_url());

		foreach ($js_rules as $rule) {
			$pages = $this->get_pages_from_rule($rule);
			$scripts = $this->get_scripts_from_rule($rule);

			if (in_array($current_url, $pages)) {
				foreach ($scripts as $script) {
					$this->disable_script($script);
				}
			}
		}
	}

	public function manage_js_assets() {
		$js_rules = get_option('rapidpress_js_disable_rules', array());
		$current_url = trailingslashit($this->get_current_url());

		foreach ($js_rules as $rule) {
			$pages = $this->get_pages_from_rule($rule);
			$scripts = $this->get_scripts_from_rule($rule);

			if (in_array($current_url, $pages)) {
				foreach ($scripts as $script) {
					$this->disable_script($script);
				}
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

	public function remove_script_tag($tag, $handle) {
		$js_rules = get_option('rapidpress_js_disable_rules', array());

		if (empty($js_rules)) {
			return $tag;
		}

		$current_url = trailingslashit($this->get_current_url());

		foreach ($js_rules as $rule) {
			$pages = $this->get_pages_from_rule($rule);
			$scripts = $this->get_scripts_from_rule($rule);

			if (in_array($current_url, $pages)) {
				foreach ($scripts as $script) {
					if ($handle === $script || strpos($tag, $script) !== false) {
						return '';
					}
				}
			}
		}

		return $tag;
	}

	private function get_pages_from_rule($rule) {
		return isset($rule['pages']) ? $rule['pages'] : array();
	}

	private function get_scripts_from_rule($rule) {
		return isset($rule['scripts']) ? $rule['scripts'] : array();
	}

	private function get_current_url() {
		global $wp;
		return home_url($wp->request);
	}
}
