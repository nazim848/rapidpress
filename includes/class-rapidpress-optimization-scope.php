<?php

class RapidPress_Optimization_Scope {

	public static function should_optimize() {
		$scope = get_option('rapidpress_optimization_scope', 'entire_site');
		$current_url = self::get_current_relative_url();

		if ($scope === 'entire_site') {
			$excluded_pages = get_option('rapidpress_excluded_pages', '');
			$excluded_pages = array_filter(array_map('trim', explode("\n", $excluded_pages)));

			// If there are no exclusions, optimize everything
			if (empty($excluded_pages)) {
				return true;
			}

			foreach ($excluded_pages as $page) {
				if (!empty($page) && self::url_match($current_url, $page)) {
					return false;
				}
			}

			return true;
		}

		$optimized_pages = get_option('rapidpress_optimized_pages', '');
		$pages = array_filter(array_map('trim', explode("\n", $optimized_pages)));

		foreach ($pages as $page) {
			if (!empty($page) && self::url_match($current_url, $page)) {
				return true;
			}
		}

		return false;
	}

	private static function get_current_relative_url() {
		$home_path = parse_url(home_url(), PHP_URL_PATH);
		$home_path = trim($home_path, '/');

		$current_url = trim($_SERVER['REQUEST_URI'], '/');

		if ($home_path && strpos($current_url, $home_path) === 0) {
			$current_url = substr($current_url, strlen($home_path));
		}

		return trim($current_url, '/');
	}

	private static function url_match($current_url, $page_url) {
		$current_url = trim($current_url, '/');
		$page_url = trim($page_url, '/');

		// Convert relative URL to absolute if it's not already
		if (strpos($page_url, 'http') !== 0) {
			$page_url = home_url($page_url);
		}

		$current_parts = parse_url(home_url($current_url));
		$page_parts = parse_url($page_url);

		// Compare paths
		$current_path = isset($current_parts['path']) ? trim($current_parts['path'], '/') : '';
		$page_path = isset($page_parts['path']) ? trim($page_parts['path'], '/') : '';

		return $current_path === $page_path;
	}
}
