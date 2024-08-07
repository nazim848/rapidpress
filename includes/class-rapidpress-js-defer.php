<?php

class RapidPress_JS_Defer {

	public function __construct() {
		add_filter('script_loader_tag', array($this, 'defer_js'), 10, 3);
	}

	public function defer_js($tag, $handle, $src) {
		if (!get_option('rapidpress_js_defer')) {
			return $tag;
		}

		$exclusions = $this->get_exclusions();

		if ($this->is_excluded($src, $exclusions)) {
			return $tag;
		}

		if (strpos($tag, 'defer') !== false) {
			return $tag;
		}

		return str_replace(' src', ' defer src', $tag);
	}

	private function get_exclusions() {
		$exclusions_string = get_option('rapidpress_js_defer_exclusions', '');
		return array_filter(array_map('trim', explode("\n", $exclusions_string)));
	}

	private function is_excluded($src, $exclusions) {
		foreach ($exclusions as $exclusion) {
			if (strpos($src, $exclusion) !== false) {
				return true;
			}
		}
		return false;
	}
}
