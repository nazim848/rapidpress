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
		if (!is_string($policy)) {
			return 'bypass';
		}

		$policy = sanitize_text_field($policy);
		return in_array($policy, array('bypass', 'ignore'), true) ? $policy : 'bypass';
	}

	public function use_mobile_variant() {
		return rest_sanitize_boolean(RP_Options::get_option('cache_mobile_variant', false));
	}

	public function cache_logged_in_users() {
		return rest_sanitize_boolean(RP_Options::get_option('cache_logged_in_users', false));
	}

	public function get_never_cache_urls() {
		$raw = RP_Options::get_option('cache_never_cache_urls', '');
		if (!is_string($raw) || $raw === '') {
			return array();
		}

		$lines = preg_split('/\r\n|\r|\n/', $raw);
		if (!is_array($lines)) {
			return array();
		}

		$lines = array_map('trim', $lines);
		$lines = array_filter($lines, function ($line) {
			return $line !== '';
		});

		return array_values(array_unique($lines));
	}

	public function get_never_cache_user_agents() {
		$raw = RP_Options::get_option('cache_never_cache_user_agents', '');
		if (!is_string($raw) || $raw === '') {
			return array();
		}

		$lines = preg_split('/\r\n|\r|\n/', $raw);
		if (!is_array($lines)) {
			return array();
		}

		$lines = array_map('trim', $lines);
		$lines = array_filter($lines, function ($line) {
			return $line !== '';
		});

		return array_values(array_unique($lines));
	}
}
