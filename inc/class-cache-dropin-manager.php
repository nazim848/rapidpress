<?php

namespace RapidPress;

class Cache_Dropin_Manager {
	const SIGNATURE = 'RapidPress advanced-cache drop-in';

	public static function sync_from_options() {
		$cache_enabled = rest_sanitize_boolean(RP_Options::get_option('enable_cache'));
		$early_enabled = rest_sanitize_boolean(RP_Options::get_option('early_cache_serving', false));

		if ($cache_enabled && $early_enabled) {
			return self::install_dropin();
		}

		return self::remove_dropin();
	}

	public static function install_dropin() {
		$dropin_path = self::get_dropin_path();
		$dropin_dir = dirname($dropin_path);
		if (!is_dir($dropin_dir) || !is_writable($dropin_dir)) {
			return false;
		}

		$uploads_dir = wp_upload_dir();
		$cache_dir = isset($uploads_dir['basedir']) ? trailingslashit($uploads_dir['basedir']) . 'rapidpress-cache' : '';
		if ($cache_dir === '') {
			return false;
		}

		$ttl = apply_filters('rapidpress_cache_ttl', 3600);
		$ttl = max(0, intval($ttl));

		$content = self::get_dropin_content($cache_dir, $ttl);
		return file_put_contents($dropin_path, $content) !== false;
	}

	public static function remove_dropin() {
		$dropin_path = self::get_dropin_path();
		if (!file_exists($dropin_path)) {
			return true;
		}

		if (!self::is_managed_dropin($dropin_path)) {
			return false;
		}

		return unlink($dropin_path);
	}

	private static function get_dropin_path() {
		$content_dir = defined('WP_CONTENT_DIR') ? WP_CONTENT_DIR : ABSPATH . 'wp-content';
		return trailingslashit($content_dir) . 'advanced-cache.php';
	}

	private static function is_managed_dropin($dropin_path) {
		$contents = file_get_contents($dropin_path);
		if (!is_string($contents)) {
			return false;
		}

		return strpos($contents, self::SIGNATURE) !== false;
	}

	private static function get_dropin_content($cache_dir, $ttl) {
		$cache_dir_export = var_export($cache_dir, true);
		$ttl_export = var_export($ttl, true);

		return <<<PHP
<?php
/**
 * RapidPress advanced-cache drop-in
 * This file is generated automatically by RapidPress.
 * Signature: RapidPress advanced-cache drop-in
 */

if (!defined('ABSPATH')) {
	return;
}

if (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') {
	return;
}

if (!isset(\$_SERVER['REQUEST_METHOD'])) {
	return;
}

\$method = strtoupper((string) \$_SERVER['REQUEST_METHOD']);
if (!in_array(\$method, array('GET', 'HEAD'), true)) {
	return;
}

if (!empty(\$_GET)) {
	return;
}

\$uri = isset(\$_SERVER['REQUEST_URI']) ? (string) \$_SERVER['REQUEST_URI'] : '';
if (\$uri === '' || strpos(\$uri, '?') !== false) {
	return;
}

\$path = parse_url(\$uri, PHP_URL_PATH);
\$path = is_string(\$path) ? rtrim(\$path, '/') : '';

if (strpos(\$path, '/wp-admin') === 0 || strpos(\$path, '/wp-login.php') === 0) {
	return;
}

if (!empty(\$_COOKIE)) {
	foreach (\$_COOKIE as \$cookie_name => \$cookie_value) {
		if (
			strpos(\$cookie_name, 'wordpress_logged_in_') === 0 ||
			strpos(\$cookie_name, 'comment_author_') === 0 ||
			strpos(\$cookie_name, 'wp-postpass_') === 0
		) {
			return;
		}
	}
}

\$host = isset(\$_SERVER['HTTP_HOST']) ? (string) \$_SERVER['HTTP_HOST'] : '';
if (\$host === '') {
	return;
}

\$scheme = (
	(isset(\$_SERVER['HTTPS']) && strtolower((string) \$_SERVER['HTTPS']) !== 'off') ||
	(isset(\$_SERVER['SERVER_PORT']) && (string) \$_SERVER['SERVER_PORT'] === '443')
) ? 'https' : 'http';

\$cache_key = \$scheme . '://' . \$host . \$path;
\$cache_file = {$cache_dir_export} . '/' . md5(\$cache_key) . '.html';
\$ttl = {$ttl_export};

if (!is_file(\$cache_file)) {
	return;
}

\$mtime = filemtime(\$cache_file);
if (!is_int(\$mtime) || (time() - \$mtime) > \$ttl) {
	return;
}

if (!headers_sent()) {
	header('X-RapidPress-Cache: HIT-EARLY');
}

readfile(\$cache_file);
exit;

PHP;
	}
}
