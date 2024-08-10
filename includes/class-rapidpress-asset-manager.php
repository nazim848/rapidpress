<?php

class RapidPress_Asset_Manager {

	public function __construct() {
		add_action('wp_enqueue_scripts', array($this, 'manage_js_assets'), 9999);
		add_action('wp_enqueue_scripts', array($this, 'manage_css_assets'), 9999);
		add_action('wp_print_scripts', array($this, 'final_js_cleanup'), 9999);
		add_action('wp_print_styles', array($this, 'final_css_cleanup'), 9999);
		add_filter('script_loader_tag', array($this, 'remove_script_tag'), 10, 2);
		add_filter('style_loader_tag', array($this, 'remove_style_tag'), 10, 2);
	}

	public function manage_css_assets() {
		$css_rules = get_option('rapidpress_css_disable_rules', array());
		$current_url = trailingslashit($this->get_current_url());

		foreach ($css_rules as $rule) {
			$styles = $this->get_styles_from_rule($rule);
			$scope = isset($rule['scope']) ? $rule['scope'] : 'entire_site';
			$should_disable = $this->should_disable_for_scope($scope, $current_url, $rule);

			if ($should_disable) {
				foreach ($styles as $style) {
					$this->disable_style($style);
				}
			}
		}
	}

	private function disable_style($handle) {
		if (wp_style_is($handle, 'enqueued')) {
			wp_dequeue_style($handle);
		}
		if (wp_style_is($handle, 'registered')) {
			wp_deregister_style($handle);
		}
	}

	public function final_css_cleanup() {
		$css_rules = get_option('rapidpress_css_disable_rules', array());
		$current_url = trailingslashit($this->get_current_url());

		foreach ($css_rules as $rule) {
			$styles = $this->get_styles_from_rule($rule);
			$scope = isset($rule['scope']) ? $rule['scope'] : 'entire_site';
			$should_disable = $this->should_disable_for_scope($scope, $current_url, $rule);

			if ($should_disable) {
				foreach ($styles as $style) {
					$this->disable_style($style);
				}
			}
		}
	}

	private function should_disable_for_scope($scope, $current_url, $rule) {
		switch ($scope) {
			case 'entire_site':
				return true;
			case 'front_page':
				return $this->is_front_page();
			case 'specific_pages':
				$pages = $this->get_pages_from_rule($rule);
				return in_array($current_url, $pages);
			default:
				return false;
		}
	}

	public function remove_style_tag($tag, $handle) {
		$css_rules = get_option('rapidpress_css_disable_rules', array());

		if (empty($css_rules)) {
			return $tag;
		}

		$current_url = trailingslashit($this->get_current_url());

		foreach ($css_rules as $rule) {
			$styles = $this->get_styles_from_rule($rule);
			$scope = isset($rule['scope']) ? $rule['scope'] : 'entire_site';
			$should_disable = $this->should_disable_for_scope($scope, $current_url, $rule);

			if ($should_disable) {
				foreach ($styles as $style) {
					if ($handle === $style || strpos($tag, $style) !== false) {
						return '';
					}
				}
			}
		}

		return $tag;
	}

	private function get_styles_from_rule($rule) {
		return isset($rule['styles']) ? $rule['styles'] : array();
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
			$scripts = $this->get_scripts_from_rule($rule);
			$should_disable = false;

			switch ($rule['scope']) {
				case 'entire_site':
					$should_disable = true;
					break;
				case 'front_page':
					$should_disable = $this->is_front_page();
					break;
				case 'specific_pages':
					$pages = $this->get_pages_from_rule($rule);
					$should_disable = in_array($current_url, $pages);
					break;
			}

			if ($should_disable) {
				foreach ($scripts as $script) {
					$this->disable_script($script);
				}
			}
		}
	}

	private function is_front_page() {
		return is_front_page() || is_home();
	}

	private function disable_script($script) {
		if (wp_script_is($script, 'enqueued')) {
			wp_dequeue_script($script);
		}
		if (wp_script_is($script, 'registered')) {
			wp_deregister_script($script);
		}
		add_filter('script_loader_tag', function ($tag, $handle) use ($script) {
			if (strpos($tag, $script) !== false) {
				return '';
			}
			return $tag;
		}, 10, 2);
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
		if (is_array($rule['scripts'])) {
			return array_filter(array_map('trim', $rule['scripts']));
		} elseif (is_string($rule['scripts'])) {
			return array_filter(array_map('trim', explode("\n", $rule['scripts'])));
		}
		return array();
	}

	private function get_current_url() {
		global $wp;
		return home_url($wp->request);
	}
}
