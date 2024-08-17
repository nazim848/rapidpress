<?php

namespace RapidPress;

class Optimization_Scope {

	public static function should_optimize() {
		$scope = get_option('rapidpress_optimization_scope', 'entire_site');
		$current_url = self::get_current_relative_url();

		switch ($scope) {
			case 'entire_site':
				return self::should_optimize_entire_site($current_url);
			case 'front_page':
				return self::is_front_page();
			case 'specific_pages':
				return self::should_optimize_specific_pages($current_url);
			default:
				return false;
		}
	}

	private static function should_optimize_entire_site($current_url) {
		$enable_exclusions = get_option('rapidpress_enable_scope_exclusions', '0');

		if ($enable_exclusions !== '1') {
			return true;
		}

		$excluded_pages = get_option('rapidpress_optimization_excluded_pages', '');
		$excluded_pages = array_filter(array_map('trim', explode("\n", $excluded_pages)));

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

	private static function is_front_page() {
		return is_front_page() || is_home();
	}

	private static function should_optimize_specific_pages($current_url) {
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
		$home_path = $home_path ? trim($home_path, '/') : '';

		$current_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
		$current_url = trim($current_url, '/');

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
