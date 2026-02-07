<?php

namespace RapidPress;

class Page_Cache {
	private $cache_config;
	private $cache_key;
	private $cache_store;
	private $cache_invalidation;
	private $skip_reason = '';

	public function __construct($cache_config = null, $cache_key = null, $cache_store = null, $cache_invalidation = null) {
		$this->cache_config = $cache_config instanceof Cache_Config ? $cache_config : new Cache_Config();
		$this->cache_key = $cache_key instanceof Cache_Key ? $cache_key : new Cache_Key();
		$this->cache_store = $cache_store instanceof Cache_Store ? $cache_store : new Cache_Store();
		$this->cache_invalidation = $cache_invalidation instanceof Cache_Invalidation ? $cache_invalidation : new Cache_Invalidation();

		add_action('template_redirect', array($this, 'maybe_serve_cache'), 0);
		add_action('template_redirect', array($this, 'start_buffering'), 1);

		add_action('save_post', array($this, 'purge_post_related_cache'));
		add_action('deleted_post', array($this, 'purge_cache'));
		add_action('transition_post_status', array($this, 'purge_transition_related_cache'), 10, 3);
		add_action('comment_post', array($this, 'purge_comment_related_cache'));
		add_action('wp_update_nav_menu', array($this, 'purge_cache'));
		add_action('customize_save_after', array($this, 'purge_cache'));
	}

	public function maybe_serve_cache() {
		if (!$this->should_cache_request()) {
			$this->send_bypass_headers();
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

	public function purge_post_related_cache($post_id) {
		$post = get_post($post_id);
		if ($post instanceof \WP_Post && wp_is_post_revision($post_id)) {
			return;
		}

		$this->purge_related_urls($this->cache_invalidation->get_urls_for_post($post_id));
	}

	public function purge_transition_related_cache($new_status, $old_status, $post) {
		if (!$post instanceof \WP_Post) {
			$this->purge_cache();
			return;
		}

		if ($new_status === $old_status) {
			return;
		}

		$this->purge_related_urls($this->cache_invalidation->get_urls_for_post($post->ID));
	}

	public function purge_comment_related_cache($comment_id) {
		$this->purge_related_urls($this->cache_invalidation->get_urls_for_comment($comment_id));
	}

	private function should_cache_request() {
		if (!$this->cache_config->is_enabled()) {
			$this->skip_reason = 'cache_disabled';
			return false;
		}

		if (defined('DONOTCACHEPAGE') && DONOTCACHEPAGE) {
			$this->skip_reason = 'donotcachepage';
			return false;
		}

		if (is_admin() || is_feed() || is_preview() || is_404()) {
			$this->skip_reason = 'request_type';
			return false;
		}

		if (is_user_logged_in() && !$this->cache_config->cache_logged_in_users()) {
			$this->skip_reason = 'logged_in';
			return false;
		}

		if (!isset($_SERVER['REQUEST_METHOD'])) {
			$this->skip_reason = 'missing_method';
			return false;
		}

		$method = strtoupper(sanitize_text_field(wp_unslash($_SERVER['REQUEST_METHOD'])));
		if (!in_array($method, array('GET', 'HEAD'), true)) {
			$this->skip_reason = 'method_not_cacheable';
			return false;
		}

		$has_query = (strpos($this->cache_key->get_request_uri(), '?') !== false || !empty($_GET));
		if ($has_query && $this->cache_config->get_query_policy() !== 'ignore') {
			$this->skip_reason = 'query_string';
			return false;
		}

		if ($this->matches_never_cache_url()) {
			$this->skip_reason = 'url_rule';
			return false;
		}

		if ($this->matches_never_cache_user_agent()) {
			$this->skip_reason = 'user_agent_rule';
			return false;
		}

		$skip = apply_filters('rapidpress_cache_skip', false);
		if ($skip) {
			$this->skip_reason = 'custom_filter';
			return false;
		}

		$this->skip_reason = '';
		return true;
	}

	private function get_cache_key() {
		$key = $this->cache_key->from_request();
		if ($key === '') {
			return '';
		}

		if ($this->cache_config->use_mobile_variant()) {
			$key .= wp_is_mobile() ? '|mobile' : '|desktop';
		}

		return $key;
	}

	private function purge_related_urls($urls) {
		if (!is_array($urls) || empty($urls)) {
			$this->purge_cache();
			return;
		}

		foreach ($urls as $url) {
			$base_key = $this->cache_key->from_url($url);
			$this->delete_url_cache_by_key($base_key);
		}
	}

	private function delete_url_cache_by_key($base_key) {
		if (!is_string($base_key) || $base_key === '') {
			return;
		}

		if ($this->cache_config->use_mobile_variant()) {
			$this->cache_store->delete_by_key($base_key . '|mobile');
			$this->cache_store->delete_by_key($base_key . '|desktop');
			return;
		}

		$this->cache_store->delete_by_key($base_key);
	}

	private function matches_never_cache_url() {
		$patterns = $this->cache_config->get_never_cache_urls();
		if (empty($patterns)) {
			return false;
		}

		$request_uri = $this->cache_key->get_request_uri();
		$request_url = home_url($request_uri);
		foreach ($patterns as $pattern) {
			if (strpos($request_uri, $pattern) !== false || strpos($request_url, $pattern) !== false) {
				return true;
			}
		}

		return false;
	}

	private function matches_never_cache_user_agent() {
		$patterns = $this->cache_config->get_never_cache_user_agents();
		if (empty($patterns) || !isset($_SERVER['HTTP_USER_AGENT'])) {
			return false;
		}

		$user_agent = sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT']));
		foreach ($patterns as $pattern) {
			if (stripos($user_agent, $pattern) !== false) {
				return true;
			}
		}

		return false;
	}

	private function send_bypass_headers() {
		if (headers_sent()) {
			return;
		}

		header('X-RapidPress-Cache: BYPASS');
		if ($this->skip_reason !== '') {
			header('X-RapidPress-Cache-Reason: ' . $this->skip_reason);
		}
	}
}
