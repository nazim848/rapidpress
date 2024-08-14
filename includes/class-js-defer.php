<?php

namespace RapidPress;

class JS_Defer {

	public function __construct() {
		add_filter('script_loader_tag', array($this, 'defer_js'), 10, 3);
	}

	public function defer_js($tag, $handle, $src) {
		if (is_admin() || !get_option('rapidpress_js_defer') || !\RapidPress\Optimization_Scope::should_optimize()) {
			return $tag;
		}

		$enable_exclusions = get_option('rapidpress_enable_js_defer_exclusions', '0');
		if ($enable_exclusions === '1') {
			$exclusions = $this->get_exclusions();
			if ($this->is_excluded($src, $exclusions)) {
				return $tag;
			}
		}

		if (strpos($tag, 'defer') !== false) {
			return $tag;
		}

		return str_replace(' src', ' defer src', $tag);
	}

	private function get_exclusions() {

		// $enable_exclusions = get_option('rapidpress_enable_js_defer_exclusions', '0');
		// if ($enable_exclusions !== '1') {
		// 	return true;
		// }

		$exclusions_string = get_option('rapidpress_js_defer_exclusions', '');
		return array_filter(array_map('trim', explode("\n", $exclusions_string)));
	}

	private function is_excluded($src, $exclusions) {


		foreach ($exclusions as $exclusion) {

			$enable_exclusions = get_option('rapidpress_enable_js_defer_exclusions', '0');
			if ($enable_exclusions !== '1') {
				return true;
			}

			if (strpos($src, $exclusion) !== false) {
				return true;
			}
		}
		return false;
	}
}
