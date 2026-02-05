<?php

namespace RapidPress;

class Page_Cache {
	private $cache_dir;

	public function __construct() {
		$this->cache_dir = $this->get_cache_dir();

		add_action('template_redirect', array($this, 'maybe_serve_cache'), 0);
		add_action('template_redirect', array($this, 'start_buffering'), 1);

		add_action('save_post', array($this, 'purge_cache'));
		add_action('deleted_post', array($this, 'purge_cache'));
		add_action('transition_post_status', array($this, 'purge_cache'));
		add_action('comment_post', array($this, 'purge_cache'));
		add_action('wp_update_nav_menu', array($this, 'purge_cache'));
		add_action('customize_save_after', array($this, 'purge_cache'));
	}

	public function maybe_serve_cache() {
		if (!$this->should_cache_request()) {
			return;
		}

		$cache_file = $this->get_cache_file_path();
		if (!$cache_file) {
			return;
		}

		$ttl = $this->get_ttl();
		if (file_exists($cache_file)) {
			$age = time() - filemtime($cache_file);
			if ($age <= $ttl) {
				$contents = $this->read_file($cache_file);
				if ($contents !== false) {
					if (!headers_sent()) {
						header('X-RapidPress-Cache: HIT');
					}
					echo $contents; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					exit;
				}
			} else {
				$this->delete_file($cache_file);
			}
		}
	}

	public function start_buffering() {
		if (!$this->should_cache_request()) {
			return;
		}

		ob_start(array($this, 'cache_output'));
	}

	public function cache_output($html) {
		$cache_file = $this->get_cache_file_path();
		if ($cache_file && $html !== '') {
			$this->write_file($cache_file, $html);
			if (!headers_sent()) {
				header('X-RapidPress-Cache: MISS');
			}
		}

		return $html;
	}

	public function purge_cache() {
		$cache_dir = $this->get_cache_dir();
		if (!$cache_dir || !is_dir($cache_dir)) {
			return;
		}

		$files = glob(trailingslashit($cache_dir) . '*.html');
		if (empty($files)) {
			return;
		}

		foreach ($files as $file) {
			$this->delete_file($file);
		}
	}

	private function should_cache_request() {
		if (!RP_Options::get_option('enable_cache')) {
			return false;
		}

		if (defined('DONOTCACHEPAGE') && DONOTCACHEPAGE) {
			return false;
		}

		if (is_admin() || is_user_logged_in() || is_feed() || is_preview() || is_404()) {
			return false;
		}

		if (!isset($_SERVER['REQUEST_METHOD'])) {
			return false;
		}

		$method = strtoupper(sanitize_text_field(wp_unslash($_SERVER['REQUEST_METHOD'])));
		if (!in_array($method, array('GET', 'HEAD'), true)) {
			return false;
		}

		if (strpos($this->get_request_uri(), '?') !== false || !empty($_GET)) {
			return false;
		}

		$skip = apply_filters('rapidpress_cache_skip', false);
		if ($skip) {
			return false;
		}

		return true;
	}

	private function get_request_uri() {
		$uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '';
		return is_string($uri) ? $uri : '';
	}

	private function get_cache_key() {
		$host = isset($_SERVER['HTTP_HOST']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST'])) : '';
		$uri = $this->get_request_uri();
		$path = strtok($uri, '?');
		$path = untrailingslashit($path);
		$scheme = is_ssl() ? 'https' : 'http';

		$key = $scheme . '://' . $host . $path;
		return apply_filters('rapidpress_cache_key', $key);
	}

	private function get_cache_file_path() {
		$cache_dir = $this->get_cache_dir();
		if (!$cache_dir) {
			return '';
		}

		$key = $this->get_cache_key();
		if ($key === '') {
			return '';
		}

		$filename = md5($key) . '.html';
		return trailingslashit($cache_dir) . $filename;
	}

	private function get_cache_dir() {
		$upload_dir = wp_upload_dir();
		$base_dir = isset($upload_dir['basedir']) ? $upload_dir['basedir'] : '';
		if ($base_dir === '') {
			return '';
		}

		$dir = trailingslashit($base_dir) . 'rapidpress-cache';
		$dir = apply_filters('rapidpress_cache_path', $dir);

		if (!is_dir($dir)) {
			wp_mkdir_p($dir);
		}

		return $dir;
	}

	private function get_ttl() {
		$ttl = 3600;
		$ttl = apply_filters('rapidpress_cache_ttl', $ttl);
		return max(0, intval($ttl));
	}

	private function read_file($path) {
		global $wp_filesystem;
		if (empty($wp_filesystem)) {
			require_once ABSPATH . 'wp-admin/inc/file.php';
			WP_Filesystem();
		}

		return $wp_filesystem->get_contents($path);
	}

	private function write_file($path, $contents) {
		global $wp_filesystem;
		if (empty($wp_filesystem)) {
			require_once ABSPATH . 'wp-admin/inc/file.php';
			WP_Filesystem();
		}

		$wp_filesystem->put_contents($path, $contents, FS_CHMOD_FILE);
	}

	private function delete_file($path) {
		global $wp_filesystem;
		if (empty($wp_filesystem)) {
			require_once ABSPATH . 'wp-admin/inc/file.php';
			WP_Filesystem();
		}

		if ($wp_filesystem->exists($path)) {
			$wp_filesystem->delete($path);
		}
	}
}
