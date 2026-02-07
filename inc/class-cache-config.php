<?php

namespace RapidPress;

class Cache_Config {
	public function is_enabled() {
		return rest_sanitize_boolean(RP_Options::get_option('enable_cache'));
	}

	public function get_ttl() {
		$ttl = 3600;
		$ttl = apply_filters('rapidpress_cache_ttl', $ttl);
		return max(0, intval($ttl));
	}

	public function get_query_policy() {
		$policy = RP_Options::get_option('cache_query_policy', 'bypass');
		return is_string($policy) ? $policy : 'bypass';
	}

	public function use_mobile_variant() {
		return rest_sanitize_boolean(RP_Options::get_option('cache_mobile_variant', false));
	}

	public function cache_logged_in_users() {
		return rest_sanitize_boolean(RP_Options::get_option('cache_logged_in_users', false));
	}
}
