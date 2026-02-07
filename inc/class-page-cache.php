<?php

namespace RapidPress;

class Page_Cache {
	private $cache_config;
	private $cache_key;
	private $cache_store;

	public function __construct($cache_config = null, $cache_key = null, $cache_store = null) {
		$this->cache_config = $cache_config instanceof Cache_Config ? $cache_config : new Cache_Config();
		$this->cache_key = $cache_key instanceof Cache_Key ? $cache_key : new Cache_Key();
		$this->cache_store = $cache_store instanceof Cache_Store ? $cache_store : new Cache_Store();

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

		$key = $this->get_cache_key();
		$cache_file = $this->cache_store->get_cache_file_path($key);
		if (!$cache_file) {
			return;
		}

		if (file_exists($cache_file)) {
			if ($this->cache_store->is_fresh($cache_file, $this->cache_config->get_ttl())) {
				$contents = $this->cache_store->read_file($cache_file);
				if ($contents !== false) {
					if (!headers_sent()) {
						header('X-RapidPress-Cache: HIT');
					}
					echo $contents; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					exit;
				}
			} else {
				$this->cache_store->delete_file($cache_file);
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
		$key = $this->get_cache_key();
		$cache_file = $this->cache_store->get_cache_file_path($key);
		if ($cache_file && $html !== '') {
			$this->cache_store->write_file($cache_file, $html);
			if (!headers_sent()) {
				header('X-RapidPress-Cache: MISS');
			}
		}

		return $html;
	}

	public function purge_cache() {
		$this->cache_store->purge_all_html();
	}

	private function should_cache_request() {
		if (!$this->cache_config->is_enabled()) {
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

		if (strpos($this->cache_key->get_request_uri(), '?') !== false || !empty($_GET)) {
			return false;
		}

		$skip = apply_filters('rapidpress_cache_skip', false);
		if ($skip) {
			return false;
		}

		return true;
	}

	private function get_cache_key() {
		return $this->cache_key->from_request();
	}
}
