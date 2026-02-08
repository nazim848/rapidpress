<?php

namespace RapidPress;

class Cache_Store {
	public function get_cache_base_dir() {
		$upload_dir = wp_upload_dir();
		$base_dir = isset($upload_dir['basedir']) ? $upload_dir['basedir'] : '';
		if (!is_string($base_dir) || $base_dir === '') {
			return '';
		}

		$dir = trailingslashit($base_dir) . 'rapidpress';
		return apply_filters('rapidpress_cache_base_dir', $dir);
	}

	public function get_html_cache_dir() {
		$base_dir = $this->get_cache_base_dir();
		if ($base_dir === '') {
			return '';
		}

		$dir = trailingslashit($base_dir) . 'cache';
		$dir = apply_filters('rapidpress_cache_path', $dir);
		$dir = apply_filters('rapidpress_html_cache_path', $dir);

		if (!is_dir($dir)) {
			wp_mkdir_p($dir);
		}

		if (!is_dir($dir)) {
			return '';
		}

		return $dir;
	}

	public function get_css_cache_dir() {
		$base_dir = $this->get_cache_base_dir();
		if ($base_dir === '') {
			return '';
		}

		$dir = trailingslashit($base_dir) . 'css';
		$dir = apply_filters('rapidpress_css_cache_path', $dir);

		if (!is_dir($dir)) {
			wp_mkdir_p($dir);
		}

		if (!is_dir($dir)) {
			return '';
		}

		return $dir;
	}

	public function get_css_cache_url() {
		$upload_dir = wp_upload_dir();
		$base_url = isset($upload_dir['baseurl']) ? $upload_dir['baseurl'] : '';
		if (!is_string($base_url) || $base_url === '') {
			return '';
		}

		$url = trailingslashit($base_url) . 'rapidpress/css';
		return apply_filters('rapidpress_css_cache_url', $url);
	}

	public function get_cache_dir() {
		return $this->get_html_cache_dir();
	}

	public function get_cache_file_path($key) {
		if (!is_string($key) || $key === '') {
			return '';
		}

		$cache_dir = $this->get_cache_dir();
		if ($cache_dir === '') {
			return '';
		}

		$filename = md5($key) . '.html';
		return trailingslashit($cache_dir) . $filename;
	}

	public function is_fresh($path, $ttl) {
		if (!is_string($path) || $path === '' || !file_exists($path)) {
			return false;
		}

		$age = time() - filemtime($path);
		return $age <= max(0, intval($ttl));
	}

	public function purge_all_html() {
		$cache_dir = $this->get_cache_dir();
		if ($cache_dir === '' || !is_dir($cache_dir)) {
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

	public function delete_by_key($key) {
		$path = $this->get_cache_file_path($key);
		if ($path === '') {
			return;
		}

		$this->delete_file($path);
	}

	public function read_file($path) {
		global $wp_filesystem;
		if (empty($wp_filesystem)) {
			require_once ABSPATH . 'wp-admin/inc/file.php';
			WP_Filesystem();
		}

		return $wp_filesystem->get_contents($path);
	}

	public function write_file($path, $contents) {
		global $wp_filesystem;
		if (empty($wp_filesystem)) {
			require_once ABSPATH . 'wp-admin/inc/file.php';
			WP_Filesystem();
		}

		$wp_filesystem->put_contents($path, $contents, FS_CHMOD_FILE);
	}

	public function delete_file($path) {
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
