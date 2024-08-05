<?php

class RapidPress_CSS_Combiner {

	private $combined_css = '';
	private $combined_filename = '';
	private $debug_log = array();
	private $cache_expiration = 86400; // 24 hours in seconds
	private $last_modified = 0;

	public function __construct() {
		add_action('wp_print_styles', array($this, 'combine_css'), 100);
		add_action('wp_head', array($this, 'print_combined_css'), 999);
	}

	public function combine_css() {
		if (!get_option('rapidpress_combine_css') || is_admin()) {
			$this->debug_log[] = "CSS combination is disabled or is admin page.";
			return;
		}

		global $wp_styles;

		if (!is_object($wp_styles)) {
			$this->debug_log[] = "wp_styles is not an object.";
			return;
		}

		$styles_to_combine = array();
		$styles_hash = '';

		foreach ($wp_styles->queue as $handle) {
			$style = $wp_styles->registered[$handle];

			if (!isset($style->src) || empty($style->src)) {
				$this->debug_log[] = "Skipped {$handle}: No src attribute.";
				continue;
			}

			if ($this->is_same_domain($style->src)) {
				$styles_to_combine[] = $style;
				$styles_hash .= $style->src . (isset($style->ver) ? $style->ver : '');
				$this->update_last_modified($style->src);
				$wp_styles->dequeue($handle);
				$this->debug_log[] = "Added to combine: {$style->src}";
			} else {
				$this->debug_log[] = "Skipped {$handle}: Not same domain.";
			}
		}

		$styles_hash = md5($styles_hash . $this->last_modified);
		$this->combined_filename = 'combined-' . $styles_hash . '.css';

		if ($this->is_cached_file_valid($styles_hash)) {
			$this->debug_log[] = "Using cached combined CSS file.";
			return;
		}

		if (!empty($styles_to_combine)) {
			$this->combined_css = '';
			foreach ($styles_to_combine as $style) {
				$content = $this->get_css_content($style->src);
				if (!empty($content)) {
					$this->combined_css .= $content . "\n";
					$this->debug_log[] = "Combined: {$style->src}";

					if (!empty($style->extra['after'])) {
						$this->combined_css .= implode("\n", $style->extra['after']) . "\n";
						$this->debug_log[] = "Added inline CSS for: {$style->src}";
					}
				} else {
					$this->debug_log[] = "Failed to get content: {$style->src}";
				}
			}

			$this->combined_css = $this->minify_css($this->combined_css);
			$this->save_combined_css($styles_hash);
		} else {
			$this->debug_log[] = "No styles to combine.";
		}
	}

	private function update_last_modified($src) {
		$file_path = $this->url_to_path($src);
		if (file_exists($file_path)) {
			$file_modified = filemtime($file_path);
			$this->last_modified = max($this->last_modified, $file_modified);
		}
	}

	private function is_cached_file_valid($styles_hash) {
		$upload_dir = wp_upload_dir();
		$combined_dir = $upload_dir['basedir'] . '/rapidpress-combined';
		$combined_file = $combined_dir . '/' . $this->combined_filename;

		if (file_exists($combined_file)) {
			$cache_meta = get_option('rapidpress_css_cache_meta', array());
			if (isset($cache_meta['hash']) && $cache_meta['hash'] === $styles_hash) {
				return true;
			}
		}

		return false;
	}

	private function get_css_content($src) {
		$full_url = $this->get_full_url($src);

		// For local environments, use file_get_contents instead of wp_remote_get
		if ($this->is_local_environment()) {
			$file_path = $this->url_to_path($full_url);
			if (file_exists($file_path)) {
				return file_get_contents($file_path);
			} else {
				$this->debug_log[] = "File not found: {$file_path}";
				return '';
			}
		}

		$args = array(
			'sslverify' => !$this->is_local_environment()
		);
		$response = wp_remote_get($full_url, $args);

		if (is_wp_error($response)) {
			$this->debug_log[] = "Error fetching {$full_url}: " . $response->get_error_message();
			return '';
		}
		return wp_remote_retrieve_body($response);
	}

	private function is_local_environment() {
		return in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1')) || strpos($_SERVER['HTTP_HOST'], '.local') !== false;
	}

	private function url_to_path($url) {
		$site_url = site_url();
		$site_path = ABSPATH;
		return str_replace($site_url, $site_path, $url);
	}

	private function get_full_url($src) {
		if (strpos($src, 'http') !== 0) {
			if (strpos($src, '//') === 0) {
				return 'http:' . $src;
			}
			return home_url($src);
		}
		return $src;
	}

	private function is_same_domain($src) {
		$full_url = $this->get_full_url($src);
		$parsed_url = parse_url($full_url);
		$is_same = (!isset($parsed_url['host']) || $parsed_url['host'] === $_SERVER['HTTP_HOST']);
		$this->debug_log[] = "Domain check for {$full_url}: " . ($is_same ? "Same domain" : "Different domain");
		return $is_same;
	}

	private function minify_css($css) {
		// Basic CSS minification
		$css = preg_replace('/\s+/', ' ', $css);
		$css = preg_replace('/\/\*[^*]*\*+([^\/][^*]*\*+)*\//', '', $css);
		$css = str_replace(array(': ', ' {', '} '), array(':', '{', '}'), $css);
		return trim($css);
	}

	private function save_combined_css($styles_hash) {
		$upload_dir = wp_upload_dir();
		$combined_dir = $upload_dir['basedir'] . '/rapidpress-combined';

		if (!file_exists($combined_dir)) {
			mkdir($combined_dir, 0755, true);
		}

		$combined_file = $combined_dir . '/' . $this->combined_filename;

		file_put_contents($combined_file, $this->combined_css);

		// Save cache metadata
		$cache_meta = array(
			'hash' => $styles_hash,
			'last_modified' => $this->last_modified
		);
		update_option('rapidpress_css_cache_meta', $cache_meta);
	}

	public function print_combined_css() {
		if (!empty($this->combined_filename)) {
			$upload_dir = wp_upload_dir();
			$combined_url = $upload_dir['baseurl'] . '/rapidpress-combined/' . $this->combined_filename;
			echo "<link rel='stylesheet' href='" . esc_url($combined_url) . "' type='text/css' media='all' />\n";
		}
	}
}
