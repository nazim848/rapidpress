<?php

class RapidPress_Optimization_Scope {
	public static function should_optimize() {
		$scope = get_option('rapidpress_optimization_scope', 'entire_site');

		if ($scope === 'entire_site') {
			return true;
		}

		$optimized_pages = get_option('rapidpress_optimized_pages', '');
		$pages = array_map('trim', explode("\n", $optimized_pages));
		$current_url = trailingslashit(home_url($GLOBALS['wp']->request));

		return in_array($current_url, $pages);
	}
}
