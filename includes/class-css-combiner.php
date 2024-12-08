<?php

namespace RapidPress;

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
	private $max_files_to_keep = 3;

	public function __construct() {
		add_action('wp_print_styles', array($this, 'combine_css'), 100);
		add_action('wp_head', array($this, 'print_combined_css'), 999);
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

			$src = $this->get_full_url($style->src);

			if ($this->is_external_file($src) || $this->is_excluded_file($src)) {
				$this->debug_log[] = "Skipped {$src}: External or excluded file.";
				continue;
			}

			$styles_to_combine[] = $style;
			$file_path = $this->url_to_path($src);
			$last_modified = $this->get_file_last_modified($file_path);
			$styles_hash .= $src . $last_modified;
			$this->combined_handles[] = $handle;
			$this->last_modified = max($this->last_modified, $last_modified);
		}

		if (!empty($styles_to_combine)) {
			$styles_hash = md5($styles_hash);
			$this->combined_filename = "{$this->file_prefix}{$styles_hash}{$this->file_extension}";

			if (!$this->is_cached_file_valid($styles_hash)) {
				foreach ($styles_to_combine as $style) {
					$this->add_style_to_combined($style);
				}

				$this->combined_css = $this->minify_css($this->combined_css);
				$this->save_combined_css($styles_hash);
				$this->cleanup_old_files();
			}

			// Dequeue original styles
			foreach ($this->combined_handles as $handle) {
				wp_dequeue_style($handle);
			}

			// Enqueue combined style with versioning
			wp_enqueue_style('rapidpress-combined-css', $this->get_combined_css_url(), array(), $this->last_modified);
		} else {
			$this->debug_log[] = "No styles to combine.";
		}
	}

	private function get_file_last_modified($file_path) {
		return file_exists($file_path) ? filemtime($file_path) : 0;
	}

	private function cleanup_old_files() {
		$upload_dir = wp_upload_dir();
		$combined_dir = trailingslashit($upload_dir['basedir']) . 'rapidpress';

		if (!is_dir($combined_dir)) {
			return;
		}

		$files = glob($combined_dir . '/' . $this->file_prefix . '*' . $this->file_extension);

		if (count($files) <= $this->max_files_to_keep) {
			return;
		}

		usort($files, function ($a, $b) {
			return filemtime($b) - filemtime($a);
		});

		$files_to_delete = array_slice($files, $this->max_files_to_keep);

		foreach ($files_to_delete as $file) {
			if (is_file($file)) {
				wp_delete_file($file);
			}
		}
	}

	private function is_external_file($src) {
		$wp_base_url = get_bloginfo('wpurl');
		return strpos($src, $wp_base_url) === false && preg_match('/^(https?:)?\/\//i', $src);
	}

	private function is_excluded_file($src) {
		foreach ($this->excluded_files as $excluded_file) {
			if (strpos($src, $excluded_file) !== false) {
				return true;
			}
		}
		return false;
	}

	private function get_full_url($src) {
		if (strpos($src, '//') === 0) {
			return 'https:' . $src;
		}
		if (strpos($src, '/') === 0) {
			return home_url($src);
		}
		return $src;
	}

	private function add_style_to_combined($style) {
		$content = $this->get_file_content($style->src);
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
		if (strpos($src, '://') !== false) {
			$response = wp_remote_get($src);
			if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
				return wp_remote_retrieve_body($response);
			}
			return false;
		}

		$file_path = $this->url_to_path($src);
		if (file_exists($file_path)) {
			// WP_Filesystem for local files
			global $wp_filesystem;
			if (empty($wp_filesystem)) {
				require_once ABSPATH . '/wp-admin/includes/file.php';
				WP_Filesystem();
			}
			return $wp_filesystem->get_contents($file_path);
		}

		return false;
	}

	private function url_to_path($url) {
		return str_replace(
			wp_get_upload_dir()['baseurl'],
			wp_get_upload_dir()['basedir'],
			str_replace(content_url(), WP_CONTENT_DIR, $url)
		);
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
		global $wp_filesystem;

		if (!function_exists('WP_Filesystem')) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		WP_Filesystem();

		$upload_dir = wp_upload_dir();
		$combined_dir = $upload_dir['basedir'] . '/rapidpress';
		wp_mkdir_p($combined_dir);

		$combined_file = $combined_dir . '/' . $this->combined_filename;

		$wp_filesystem->put_contents(
			$combined_file,
			$this->combined_css,
			FS_CHMOD_FILE
		);

		$cache_meta = array(
			'hash' => $styles_hash,
			'expires' => time() + $this->cache_expiration,
			'last_modified' => $this->last_modified,
		);

		RP_Options::update_option('css_cache_meta', $cache_meta);
	}

	private function is_cached_file_valid($styles_hash) {
		$cache_meta = RP_Options::get_option('css_cache_meta', array());
		if (isset($cache_meta['hash']) && $cache_meta['hash'] === $styles_hash) {
			if (isset($cache_meta['last_modified']) && $cache_meta['last_modified'] >= $this->last_modified) {
				return true;
			}
		}
		return false;
	}

	private function get_combined_css_url() {
		$upload_dir = wp_upload_dir();
		return $upload_dir['baseurl'] . '/rapidpress/' . $this->combined_filename;
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
}
