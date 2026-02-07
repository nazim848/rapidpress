<?php

namespace RapidPress;

if (!defined('ABSPATH')) {
	exit;
}

class CSS_Combiner {

	private $combined_css = '';
	private $combined_filename = '';
	private $debug_log = array();
	private $cache_expiration = 86400; // 24 hours in seconds
	private $last_modified = 0;
	private $excluded_files = array();
	private $combined_handles = array();
	private $file_prefix = 'combined-';
	private $file_extension = '.css';
	private $max_files_to_keep = 100;
	private $combined_base_url = '';

	public function __construct() {
		add_action('wp_print_styles', array($this, 'combine_css'), 100);
		add_action('wp_head', array($this, 'print_combined_css'), 999);
		add_action('wp_print_styles', array($this, 'final_css_cleanup'), 9999);
		add_filter('rapidpress_final_output', array($this, 'filter_final_output'), 10, 1);
		$this->set_excluded_files();
		$this->excluded_files[] = 'admin-bar.min.css';
	}

	private function set_excluded_files() {

		$enable_exclusions = RP_Options::get_option('enable_combine_css_exclusions', '0');
		if ($enable_exclusions != '1') {
			return true;
		}
		$exclusions = RP_Options::get_option('combine_css_exclusions', '');
		$this->excluded_files = array_filter(array_map('trim', explode("\n", $exclusions)));

		if (!empty($this->excluded_files)) {
			$this->debug_log[] = "CSS exclusions enabled with " . count($this->excluded_files) . " exclusion rules.";
		}
	}

	public function combine_css() {
		// Exclude admin, POST requests, and pages not in the optimization scope
		if (!RP_Options::get_option('combine_css') || is_admin() || !\RapidPress\Optimization_Scope::should_optimize()) {
			$this->debug_log[] = "CSS combination is disabled, is admin page, or not in optimization scope.";
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

			// Store original src with query string for versioning
			$original_src = $style->src;

			// Remove query string for processing
			$src = preg_replace('/\?.*/', '', $this->get_full_url($style->src));

			$this->debug_log[] = "Processing style: handle={$handle}, src={$src}";

			if ($this->is_external_file($src) || $this->is_excluded_file($src, $handle)) {
				$this->debug_log[] = "Skipped {$handle}: External or excluded file.";
				continue;
			}

				$styles_to_combine[] = array(
					'style' => $style,
					'src' => $src,
				);
				$file_path = $this->url_to_path($src);
				$last_modified = $this->get_file_last_modified($file_path);

			// Include original src with query string in hash calculation
			$styles_hash .= $original_src . $last_modified;
			$this->combined_handles[] = $handle;
			$this->last_modified = max($this->last_modified, $last_modified);
		}

			if (!empty($styles_to_combine)) {
				$styles_hash = md5($styles_hash);
				$this->combined_filename = "{$this->file_prefix}{$styles_hash}{$this->file_extension}";
				$combined_file_ready = $this->is_cached_file_valid($styles_hash);

					if (!$combined_file_ready) {
						foreach ($styles_to_combine as $style_item) {
							$this->add_style_to_combined($style_item['style'], $style_item['src']);
						}

						$this->combined_css = $this->minify_css($this->combined_css);
						if ($this->combined_css === '') {
							$this->debug_log[] = "Combined CSS content is empty. Keeping original styles.";
							$this->combined_handles = array();
							return;
						}
						$combined_file_ready = $this->save_combined_css($styles_hash);
						if (!$combined_file_ready) {
							$this->debug_log[] = "Combined CSS write failed. Keeping original styles.";
							$this->combined_css = '';
							$this->combined_handles = array();
							return;
						}
						$this->cleanup_old_files();
					}

			// Dequeue and deregister original styles
			foreach ($this->combined_handles as $handle) {
				wp_dequeue_style($handle);
				wp_deregister_style($handle);
			}

				// Add filter to remove any style tags that might be directly output
				add_filter('style_loader_tag', array($this, 'remove_combined_style_tags'), 10, 2);

				// Enqueue combined style with versioning
				$combined_url = $this->get_combined_css_url();
				if ($combined_url !== '') {
					wp_enqueue_style('rapidpress-combined-css', $combined_url, array(), $this->last_modified);
				}
			} else {
				$this->debug_log[] = "No styles to combine.";
			}
	}

	private function get_file_last_modified($file_path) {
		return file_exists($file_path) ? filemtime($file_path) : 0;
	}

	private function cleanup_old_files() {
		$locations = $this->get_combined_locations();
		foreach ($locations as $location) {
			$combined_dir = $location['dir'];
			if (!is_dir($combined_dir)) {
				continue;
			}

			$files = glob($combined_dir . '/' . $this->file_prefix . '*' . $this->file_extension);
			if (!is_array($files) || empty($files)) {
				continue;
			}

			$now = time();
			$expiry_cutoff = $now - intval($this->cache_expiration);
			$existing = array();

			// First pass: remove expired combined files.
			foreach ($files as $file) {
				$mtime = @filemtime($file);
				if ($mtime !== false && $mtime < $expiry_cutoff) {
					if (is_file($file)) {
						wp_delete_file($file);
					}
					continue;
				}
				$existing[] = $file;
			}

			// Second pass: high-water cap to avoid unlimited accumulation.
			if (count($existing) <= $this->max_files_to_keep) {
				continue;
			}

			usort($existing, function ($a, $b) {
				return filemtime($b) - filemtime($a);
			});

			$files_to_delete = array_slice($existing, $this->max_files_to_keep);
			foreach ($files_to_delete as $file) {
				if (is_file($file)) {
					wp_delete_file($file);
				}
			}
		}
	}

	private function is_external_file($src) {
		if (!preg_match('/^(https?:)?\/\//i', $src)) {
			return false;
		}

		$src_host = wp_parse_url($src, PHP_URL_HOST);
		$home_host = wp_parse_url(home_url('/'), PHP_URL_HOST);
		$site_host = wp_parse_url(site_url('/'), PHP_URL_HOST);

		$src_host = is_string($src_host) ? strtolower($src_host) : '';
		$home_host = is_string($home_host) ? strtolower($home_host) : '';
		$site_host = is_string($site_host) ? strtolower($site_host) : '';

		if ($src_host === '' || $src_host === $home_host || $src_host === $site_host) {
			return false;
		}

		return true;
	}

	private function is_excluded_file($src, $handle = '') {
		// Remove query strings for comparison
		$src = preg_replace('/\?.*/', '', $src);

		foreach ($this->excluded_files as $excluded_file) {
			// Remove query strings from excluded file patterns
			$excluded_file = preg_replace('/\?.*/', '', $excluded_file);

			// First check if the exclusion matches the handle (exact match)
			if (!empty($handle) && $excluded_file === $handle) {
				$this->debug_log[] = "Excluded {$src} by handle: {$handle}";
				return true;
			}

			// Then check if the exclusion matches the URL (partial match)
			if (strpos($src, $excluded_file) !== false) {
				$this->debug_log[] = "Excluded {$src} by URL pattern: {$excluded_file}";
				return true;
			}
		}
		return false;
	}

	private function get_full_url($src) {

		// Remove query strings for consistent URL handling
		$src = preg_replace('/\?.*/', '', $src);

		if (strpos($src, '//') === 0) {
			return 'https:' . $src;
		}
		if (strpos($src, '/') === 0) {
			return home_url($src);
		}
		return $src;
	}

	private function add_style_to_combined($style, $normalized_src = '') {
		$src = $normalized_src !== '' ? $normalized_src : $this->get_full_url($style->src);
		$content = $this->get_file_content($src);
		if ($content !== false) {
			$this->combined_css .= "/* {$style->handle} */\n";
			$this->combined_css .= $content . "\n";

			if (!empty($style->extra['after'])) {
				$this->combined_css .= implode("\n", $style->extra['after']) . "\n";
			}
		}
	}

	// private function get_file_content($src) {
	// 	$file_path = $this->url_to_path($src);
	// 	if (file_exists($file_path)) {
	// 		return file_get_contents($file_path);
	// 	}
	// 	return false;
	// }

	private function get_file_content($src) {
		$normalized_src = $this->get_full_url((string) $src);
		$normalized_src = preg_replace('/\?.*/', '', $normalized_src);
		$file_path = $this->url_to_path($normalized_src);
		if (file_exists($file_path)) {
			$contents = @file_get_contents($file_path); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			if ($contents !== false) {
				return $contents;
			}

			// Fallback to WP_Filesystem for local files.
			global $wp_filesystem;
			if (empty($wp_filesystem)) {
				require_once ABSPATH . '/wp-admin/inc/file.php';
				WP_Filesystem();
			}
			if (!empty($wp_filesystem)) {
				return $wp_filesystem->get_contents($file_path);
			}
		}

		if (strpos($normalized_src, '://') !== false) {
			$response = wp_remote_get($normalized_src);
			if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
				return wp_remote_retrieve_body($response);
			}
		}

		return false;
	}

	/**
	 * Convert a URL to a filesystem path 
	 *
	 * @param string $url The URL to convert to a path
	 * @return string The filesystem path
	 */
	private function url_to_path($url) {
		$upload_dir = wp_upload_dir();

		// First check if it's in the uploads directory
		if (strpos($url, $upload_dir['baseurl']) !== false) {
			return str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $url);
		}

		// Check if it's a plugin URL
		$plugins_url = plugins_url();
		if (strpos($url, $plugins_url) !== false) {
			return str_replace($plugins_url, WP_PLUGIN_DIR, $url);
		}

		// Check if it's a theme URL
		$theme_url = get_template_directory_uri();
		if (strpos($url, $theme_url) !== false) {
			return str_replace($theme_url, get_template_directory(), $url);
		}

		// For content URLs, use site_url() and ABSPATH to ensure compatibility
		$site_url = site_url('/');
		$content_path = 'wp-content/';

		if (strpos($url, $site_url . $content_path) !== false) {
			return str_replace($site_url . $content_path, ABSPATH . $content_path, $url);
		}

		$path = wp_parse_url($url, PHP_URL_PATH);
		if (is_string($path) && $path !== '') {
			$candidate = ABSPATH . ltrim($path, '/');
			if (file_exists($candidate)) {
				return $candidate;
			}

			if (strpos($path, '/wp-content/') !== false) {
				$wp_content_pos = strpos($path, '/wp-content/');
				$relative = substr($path, $wp_content_pos + 1);
				$candidate = ABSPATH . $relative;
				if (file_exists($candidate)) {
					return $candidate;
				}
			}
		}

		// Fallback for other URLs
		return $url;
	}

	private function minify_css($css) {
		// Basic CSS minification
		$css = preg_replace('/\s+/', ' ', $css);
		$css = preg_replace('/\/\*[^*]*\*+([^\/][^*]*\*+)*\//', '', $css);
		$css = str_replace(array(': ', ' {', '} '), array(':', '{', '}'), $css);
		return trim($css);
	}

	// private function save_combined_css($styles_hash) {
	// 	$upload_dir = wp_upload_dir();
	// 	$combined_dir = $upload_dir['basedir'] . '/rapidpress';
	// 	wp_mkdir_p($combined_dir);

	// 	$combined_file = $combined_dir . '/' . $this->combined_filename;
	// 	file_put_contents($combined_file, $this->combined_css);

	// 	$cache_meta = array(
	// 		'hash' => $styles_hash,
	// 		'expires' => time() + $this->cache_expiration,
	// 		'last_modified' => $this->last_modified,
	// 	);

	// 	RP_Options::update_option('css_cache_meta', $cache_meta);
	// }

	private function save_combined_css($styles_hash) {
		$location = $this->get_writable_combined_location(true);
		if (empty($location) || empty($location['dir']) || empty($location['url'])) {
			RP_Options::delete_option('css_cache_meta');
			return false;
		}

		$combined_dir = $location['dir'];
		$this->combined_base_url = untrailingslashit($location['url']);

		$combined_file = $combined_dir . '/' . $this->combined_filename;
		$write_ok = false;

		// Prefer native file writing for frontend reliability.
		$bytes = @file_put_contents($combined_file, $this->combined_css); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		if ($bytes !== false) {
			$write_ok = true;
			@chmod($combined_file, FS_CHMOD_FILE); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_chmod
		}

		// Fallback to WP_Filesystem when native write fails.
		if (!$write_ok) {
			global $wp_filesystem;
			if (!function_exists('WP_Filesystem')) {
				require_once ABSPATH . 'wp-admin/inc/file.php';
			}

			if (WP_Filesystem() && !empty($wp_filesystem)) {
				$write_ok = $wp_filesystem->put_contents(
					$combined_file,
					$this->combined_css,
					FS_CHMOD_FILE
				);
			}
		}

		if (!$write_ok || !file_exists($combined_file)) {
			RP_Options::delete_option('css_cache_meta');
			return false;
		}

		$cache_meta = array(
			'hash' => $styles_hash,
			'expires' => time() + $this->cache_expiration,
			'last_modified' => $this->last_modified,
			'base_url' => $this->combined_base_url,
		);

		RP_Options::update_option('css_cache_meta', $cache_meta);
		return true;
	}

	private function is_cached_file_valid($styles_hash) {
		$cache_meta = RP_Options::get_option('css_cache_meta', array());
		if (!isset($cache_meta['hash']) || $cache_meta['hash'] !== $styles_hash) {
			return false;
		}

		if (!isset($cache_meta['expires']) || intval($cache_meta['expires']) < time()) {
			return false;
		}

		if (!isset($cache_meta['last_modified']) || intval($cache_meta['last_modified']) < $this->last_modified) {
			return false;
		}

		$combined_file = $this->find_existing_combined_file($this->combined_filename);
		if ($combined_file === '') {
			return false;
		}

		$stored_base_url = isset($cache_meta['base_url']) ? sanitize_text_field($cache_meta['base_url']) : '';
		if ($stored_base_url !== '') {
			$this->combined_base_url = untrailingslashit($stored_base_url);
		}

		return true;
	}

	private function get_combined_css_url() {
		if ($this->combined_base_url === '') {
			$location = $this->get_writable_combined_location(false);
			if (!empty($location['url'])) {
				$this->combined_base_url = untrailingslashit($location['url']);
			}
		}

		if ($this->combined_base_url === '') {
			return '';
		}

		return trailingslashit($this->combined_base_url) . $this->combined_filename;
	}

	public function print_combined_css() {
		if (!empty($this->combined_css)) {
			wp_enqueue_style('rapidpress-combined-css', $this->get_combined_css_url(), array(), $this->last_modified, 'all');
		}
	}

	// public function print_combined_css() {
	// 	if (!empty($this->combined_css)) {
	// 		echo "<link rel='stylesheet' id='rapidpress-combined-css' href='" . esc_url($this->get_combined_css_url()) . "' type='text/css' media='all' />\n";
	// 	}
	// }

	/**
	 * Remove style tags for styles that have been combined
	 */
	public function remove_combined_style_tags($tag, $handle) {
		// If this handle was combined, remove the tag
		if (in_array($handle, $this->combined_handles)) {
			return '';
		}

		// Also check if the URL of the style is in our combined handles
		foreach ($this->combined_handles as $combined_handle) {
			// Get the source URL of the combined handle
			global $wp_styles;
			if (
				isset($wp_styles->registered[$combined_handle]) &&
				isset($wp_styles->registered[$combined_handle]->src) &&
				strpos($tag, $wp_styles->registered[$combined_handle]->src) !== false
			) {
				return '';
			}
		}

		return $tag;
	}

	/**
	 * Final cleanup to catch any styles that might be registered after our combine_css method runs
	 */
	public function final_css_cleanup() {
		// Only run if CSS combination is enabled and we have combined handles
		if (!RP_Options::get_option('combine_css') || empty($this->combined_handles) || is_admin() || !\RapidPress\Optimization_Scope::should_optimize()) {
			return;
		}

		global $wp_styles;

		if (!is_object($wp_styles)) {
			return;
		}

		// Check for any styles that should have been combined but were registered late
		foreach ($wp_styles->queue as $handle) {
			if (in_array($handle, $this->combined_handles)) {
				wp_dequeue_style($handle);
				wp_deregister_style($handle);
			}

			// Also check by URL for styles that might have been registered with a different handle
			if (isset($wp_styles->registered[$handle]) && isset($wp_styles->registered[$handle]->src)) {
				$src = $wp_styles->registered[$handle]->src;

				foreach ($this->combined_handles as $combined_handle) {
					global $wp_styles;
					if (
						isset($wp_styles->registered[$combined_handle]) &&
						isset($wp_styles->registered[$combined_handle]->src) &&
						$this->get_base_url($src) === $this->get_base_url($wp_styles->registered[$combined_handle]->src)
					) {
						wp_dequeue_style($handle);
						wp_deregister_style($handle);
						break;
					}
				}
			}
		}
	}

	/**
	 * Get the base URL without query string or protocol
	 */
	private function get_base_url($url) {
		// Remove protocol
		$url = preg_replace('/^https?:/', '', $url);
		// Remove query string
		$url = preg_replace('/\?.*/', '', $url);
		return $url;
	}

	private function get_combined_locations() {
		$upload_dir = wp_upload_dir();
		$locations = array();

		if (!empty($upload_dir['basedir']) && !empty($upload_dir['baseurl'])) {
			$locations[] = array(
				'dir' => trailingslashit($upload_dir['basedir']) . 'rapidpress',
				'url' => trailingslashit($upload_dir['baseurl']) . 'rapidpress',
			);
		}

		if (!empty($upload_dir['path']) && !empty($upload_dir['url'])) {
			$path_dir = trailingslashit($upload_dir['path']) . 'rapidpress';
			$path_url = trailingslashit($upload_dir['url']) . 'rapidpress';
			$exists = false;
			foreach ($locations as $location) {
				if ($location['dir'] === $path_dir) {
					$exists = true;
					break;
				}
			}
			if (!$exists) {
				$locations[] = array(
					'dir' => $path_dir,
					'url' => $path_url,
				);
			}
		}

		$locations[] = array(
			'dir' => trailingslashit(WP_CONTENT_DIR) . 'cache/rapidpress',
			'url' => trailingslashit(content_url('cache')) . 'rapidpress',
		);

		return $locations;
	}

	private function get_writable_combined_location($create = false) {
		$locations = $this->get_combined_locations();
		foreach ($locations as $location) {
			$dir = $location['dir'];
			if (is_dir($dir) && wp_is_writable($dir)) {
				return $location;
			}

			if ($create && wp_mkdir_p($dir) && is_dir($dir) && wp_is_writable($dir)) {
				return $location;
			}
		}

		return array();
	}

	private function find_existing_combined_file($filename) {
		$locations = $this->get_combined_locations();
		foreach ($locations as $location) {
			$file = trailingslashit($location['dir']) . $filename;
			if (file_exists($file)) {
				$this->combined_base_url = untrailingslashit($location['url']);
				return $file;
			}
		}

		return '';
	}

	private function persist_debug_state() {
		$this->debug_state['log_tail'] = array_slice($this->debug_log, -20);
		RP_Options::update_option('css_combine_debug', $this->debug_state);
	}

	/**
	 * Filter the final HTML output to remove any style tags that correspond to styles we've already combined
	 */
	public function filter_final_output($html) {
		// Only run if CSS combination is enabled and we have combined handles
		if (!RP_Options::get_option('combine_css') || empty($this->combined_handles) || is_admin() || !\RapidPress\Optimization_Scope::should_optimize()) {
			return $html;
		}

		// Get all the source URLs of the combined styles
		$combined_srcs = array();
		global $wp_styles;

		foreach ($this->combined_handles as $handle) {
			if (isset($wp_styles->registered[$handle]) && isset($wp_styles->registered[$handle]->src)) {
				$src = $wp_styles->registered[$handle]->src;
				// Store both the full URL and the base URL without query string
				$combined_srcs[] = $src;
				$combined_srcs[] = $this->get_base_url($src);

				// Also store the relative path
				$site_url = site_url();
				if (strpos($src, $site_url) === 0) {
					$combined_srcs[] = substr($src, strlen($site_url));
				}

				// Also store the path without the protocol and domain
				if (preg_match('/^https?:\/\/[^\/]+(.+)$/', $src, $matches)) {
					$combined_srcs[] = $matches[1];
				}
			}
		}

		// If we have no source URLs, return the original HTML
		if (empty($combined_srcs)) {
			return $html;
		}

		// Use a callback function with preg_replace_callback to remove style tags
		$html = preg_replace_callback('/<link[^>]*(?:href=[\'"]([^\'"]+)[\'"][^>]*rel=[\'"]stylesheet[\'"]|rel=[\'"]stylesheet[\'"][^>]*href=[\'"]([^\'"]+)[\'"])[^>]*>/i', function ($matches) use ($combined_srcs) {
			$tag = $matches[0];
			$href = !empty($matches[1]) ? $matches[1] : $matches[2]; // Get the href from whichever group matched

			// Check if this style tag corresponds to a style we've already combined
			foreach ($combined_srcs as $src) {
				// Check if the href contains the source URL
				if (strpos($href, $src) !== false || strpos($src, $href) !== false) {
					return ''; // Remove the tag
				}

				// Check for filename match (for cases where paths might be different but filename is the same)
				$href_filename = basename(wp_parse_url($href, PHP_URL_PATH));
				$src_filename = basename(wp_parse_url($src, PHP_URL_PATH));

				if (!empty($href_filename) && !empty($src_filename) && $href_filename === $src_filename) {
					// Additional check to avoid false positives - compare parent directory
					$href_dir = dirname(wp_parse_url($href, PHP_URL_PATH));
					$src_dir = dirname(wp_parse_url($src, PHP_URL_PATH));

					// If the parent directories match or one is a subdirectory of the other
					if ($href_dir === $src_dir || strpos($href_dir, $src_dir) === 0 || strpos($src_dir, $href_dir) === 0) {
						return ''; // Remove the tag
					}
				}
			}

			return $tag; // Keep the tag
		}, $html);

		return $html;
	}
}
