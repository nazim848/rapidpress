<?php

namespace RapidPress;

class Cache_Key {
	public function from_request() {
		$host = isset($_SERVER['HTTP_HOST']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST'])) : '';
		$uri = $this->get_request_uri();
		$path = strtok($uri, '?');
		$path = untrailingslashit($path);
		$scheme = is_ssl() ? 'https' : 'http';

		$key = $scheme . '://' . $host . $path;
		return apply_filters('rapidpress_cache_key', $key);
	}

	public function from_url($url) {
		if (!is_string($url) || $url === '') {
			return '';
		}

		$parts = wp_parse_url($url);
		if (!is_array($parts) || empty($parts['host'])) {
			return '';
		}

		$scheme = isset($parts['scheme']) ? sanitize_text_field($parts['scheme']) : 'http';
		$host = sanitize_text_field($parts['host']);
		$path = isset($parts['path']) ? untrailingslashit((string) $parts['path']) : '';

		$key = $scheme . '://' . $host . $path;
		return apply_filters('rapidpress_cache_key', $key);
	}

	public function get_request_uri() {
		$uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '';
		return is_string($uri) ? $uri : '';
	}
}
