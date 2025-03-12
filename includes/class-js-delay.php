<?php

namespace RapidPress;

class JS_Delay {
	private $script_handles = array();

	public function __construct() {
		// Hook into wp_enqueue_scripts to capture script handles and their URLs
		// Use a lower priority to capture scripts before they're printed
		add_action('wp_enqueue_scripts', array($this, 'capture_script_handles'), 9999);
	}

	public function capture_script_handles() {
		global $wp_scripts;

		if (!is_object($wp_scripts)) {
			return;
		}

		// Capture all registered scripts, not just queued ones
		foreach ($wp_scripts->registered as $handle => $script) {
			if (isset($script->src) && !empty($script->src)) {
				$src = $script->src;

				// Convert relative URLs to absolute
				if (strpos($src, '//') === 0) {
					$src = 'https:' . $src;
				} elseif (strpos($src, '/') === 0) {
					$src = site_url($src);
				}

				// Store both with and without version query string
				$this->script_handles[$src] = $handle;

				// Also store the base URL without query string
				$base_src = preg_replace('/\?.*$/', '', $src);
				if ($base_src !== $src) {
					$this->script_handles[$base_src] = $handle;
				}

				// Debug logging only when needed
				if (defined('WP_DEBUG') && WP_DEBUG && isset($_GET['rapidpress_debug'])) {
					error_log("RapidPress JS Delay: Registered script handle '{$handle}' with src '{$src}'");
				}
			}
		}

		// Dump all registered handles only when detailed debugging is enabled
		if (defined('WP_DEBUG') && WP_DEBUG && isset($_GET['rapidpress_debug'])) {
			error_log("RapidPress JS Delay: All registered handles: " . print_r($this->script_handles, true));
		}
	}

	public function apply_js_delay($html) {
		if (is_admin() || !RP_Options::get_option('js_delay') || !\RapidPress\Optimization_Scope::should_optimize()) {
			return $html;
		}

		$delay_type = RP_Options::get_option('js_delay_type', 'all');
		$specific_files = $delay_type === 'specific' ? $this->get_specific_files() : array();
		$exclusions = ($delay_type === 'all' && RP_Options::get_option('enable_js_delay_exclusions')) ? $this->get_exclusions() : array();
		$delay_duration = RP_Options::get_option('js_delay_duration', '1');

		if (empty($html)) {
			return $html;
		}

		// Debug the exclusions and specific files only when detailed debugging is enabled
		if (defined('WP_DEBUG') && WP_DEBUG && isset($_GET['rapidpress_debug'])) {
			error_log("RapidPress JS Delay: Delay type: {$delay_type}");
			error_log("RapidPress JS Delay: Exclusions: " . print_r($exclusions, true));
			error_log("RapidPress JS Delay: Specific files: " . print_r($specific_files, true));
		}

		// Use a regular expression to find and modify script tags
		// Capture both src and id attributes
		$pattern = '/<script\b([^>]*)src=["\']([^"\']+)["\']([^>]*)>/i';
		$html = preg_replace_callback($pattern, function ($matches) use ($delay_type, $specific_files, $exclusions, $delay_duration) {
			$before_src = $matches[1];
			$src = $matches[2];
			$after_src = $matches[3];

			// Extract ID attribute if present
			$id = '';
			if (preg_match('/id=["\']([^"\']+)["\']/i', $before_src . ' ' . $after_src, $id_matches)) {
				$id = $id_matches[1];

				// If ID ends with -js, it's likely a WordPress handle with -js suffix
				if (preg_match('/-js$/', $id)) {
					$possible_handle = str_replace('-js', '', $id);

					// Check if this ID-based handle is in our exclusions or specific files
					if ($delay_type === 'specific' && in_array($possible_handle, $specific_files)) {
						return $this->get_delay_script_tag($src, $delay_duration);
					}

					if ($delay_type === 'all' && in_array($possible_handle, $exclusions)) {
						return $matches[0];
					}
				}
			}

			// Try to get the handle from our mapping
			$handle = $this->get_handle_for_src($src);

			if (($delay_type === 'specific' && $this->is_specific_file($src, $specific_files, $handle)) ||
				($delay_type === 'all' && !$this->is_excluded($src, $exclusions, $handle))
			) {
				return $this->get_delay_script_tag($src, $delay_duration);
			}

			return $matches[0];
		}, $html);

		return $html;
	}

	private function get_handle_for_src($src) {
		// Remove query string for comparison
		$base_src = preg_replace('/\?.*$/', '', $src);

		// Try to find an exact match first (with or without query string)
		if (isset($this->script_handles[$src])) {
			$handle = $this->script_handles[$src];
			return $handle;
		}

		// Try with base URL (without query string)
		if (isset($this->script_handles[$base_src])) {
			$handle = $this->script_handles[$base_src];
			return $handle;
		}

		// If no exact match, try to find a partial match
		foreach ($this->script_handles as $registered_src => $handle) {
			// Extract the filename from both URLs for comparison
			$src_filename = basename(wp_parse_url($base_src, PHP_URL_PATH));
			$registered_filename = basename(wp_parse_url($registered_src, PHP_URL_PATH));

			if ($src_filename === $registered_filename) {
				return $handle;
			}
		}

		// Check if the script has an ID attribute that matches a known handle pattern
		if (preg_match('/-js$/', $src)) {
			$possible_handle = str_replace('-js', '', basename($base_src, '.js'));
			return $possible_handle;
		}

		return '';
	}

	private function get_delay_script_tag($src, $delay_duration) {
		if ($delay_duration === 'interaction') {
			return "<script type='text/javascript'>
                document.addEventListener('DOMContentLoaded', function() {
                    var loadScript = function() {
                        var script = document.createElement('script');
                        script.src = '$src';
                        document.body.appendChild(script);
                        ['keydown', 'mouseover', 'touchmove', 'touchstart', 'wheel'].forEach(function(event) {
                            document.removeEventListener(event, loadScript, {passive: true});
                        });
                    };
                    ['keydown', 'mouseover', 'touchmove', 'touchstart', 'wheel'].forEach(function(event) {
                        document.addEventListener(event, loadScript, {passive: true});
                    });
                });
            </script>";
		} else {
			$delay = intval($delay_duration) * 1000;
			return "<script type='text/javascript'>
                setTimeout(function() {
                    var script = document.createElement('script');
                    script.src = '$src';
                    document.body.appendChild(script);
                }, $delay);
            </script>";
		}
	}

	private function get_exclusions() {
		$exclusions_string = RP_Options::get_option('js_delay_exclusions', '');
		return array_filter(array_map('trim', explode("\n", $exclusions_string)));
	}

	private function is_excluded($src, $exclusions, $handle = '') {
		foreach ($exclusions as $exclusion) {
			// Check if the exclusion matches the handle (exact match)
			if (!empty($handle) && $exclusion === $handle) {
				return true;
			}

			// Check if the exclusion matches the URL (partial match)
			if (strpos($src, $exclusion) !== false) {
				return true;
			}

			// Check for WordPress ID-based handles (e.g., jquery-core for jquery-core-js)
			if (preg_match('/-js$/', $src)) {
				$id_based_handle = str_replace('-js', '', basename(wp_parse_url($src, PHP_URL_PATH), '.js'));
				if ($exclusion === $id_based_handle) {
					return true;
				}
			}
		}
		return false;
	}

	private function get_specific_files() {
		$specific_files_string = RP_Options::get_option('js_delay_specific_files', '');
		return array_filter(array_map('trim', explode("\n", $specific_files_string)));
	}

	private function is_specific_file($src, $specific_files, $handle = '') {
		foreach ($specific_files as $file) {
			// Check if the file matches the handle (exact match)
			if (!empty($handle) && $file === $handle) {
				return true;
			}

			// Check if the file matches the URL (partial match)
			if (strpos($src, $file) !== false) {
				return true;
			}

			// Check for WordPress ID-based handles (e.g., jquery-core for jquery-core-js)
			if (preg_match('/-js$/', $src)) {
				$id_based_handle = str_replace('-js', '', basename(wp_parse_url($src, PHP_URL_PATH), '.js'));
				if ($file === $id_based_handle) {
					return true;
				}
			}
		}
		return false;
	}
}
