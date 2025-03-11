<?php

namespace RapidPress;

class JS_Delay {
	private $script_handles = array();

	public function __construct() {
		// Hook into wp_enqueue_scripts to capture script handles and their URLs
		add_action('wp_print_scripts', array($this, 'capture_script_handles'), 999);
	}

	public function capture_script_handles() {
		global $wp_scripts;

		if (!is_object($wp_scripts)) {
			return;
		}

		foreach ($wp_scripts->queue as $handle) {
			if (isset($wp_scripts->registered[$handle]) && isset($wp_scripts->registered[$handle]->src)) {
				$src = $wp_scripts->registered[$handle]->src;

				// Convert relative URLs to absolute
				if (strpos($src, '//') === 0) {
					$src = 'https:' . $src;
				} elseif (strpos($src, '/') === 0) {
					$src = site_url($src);
				}

				$this->script_handles[$src] = $handle;

				// Add a comment for debugging
				if (defined('WP_DEBUG') && WP_DEBUG) {
					error_log("RapidPress JS Delay: Registered script handle '{$handle}' with src '{$src}'");
				}
			}
		}

		// Dump all registered handles for debugging
		if (defined('WP_DEBUG') && WP_DEBUG) {
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

		// Debug the exclusions and specific files
		if (defined('WP_DEBUG') && WP_DEBUG) {
			error_log("RapidPress JS Delay: Delay type: {$delay_type}");
			error_log("RapidPress JS Delay: Exclusions: " . print_r($exclusions, true));
			error_log("RapidPress JS Delay: Specific files: " . print_r($specific_files, true));
		}

		// Use a regular expression to find and modify script tags
		$pattern = '/<script\b[^>]*src=["\']([^"\']+)["\'][^>]*>/i';
		$html = preg_replace_callback($pattern, function ($matches) use ($delay_type, $specific_files, $exclusions, $delay_duration) {
			$src = $matches[1];
			$handle = $this->get_handle_for_src($src);

			if (defined('WP_DEBUG') && WP_DEBUG) {
				error_log("RapidPress JS Delay: Processing script with src '{$src}' and handle '{$handle}'");
			}

			if (($delay_type === 'specific' && $this->is_specific_file($src, $specific_files, $handle)) ||
				($delay_type === 'all' && !$this->is_excluded($src, $exclusions, $handle))
			) {
				if (defined('WP_DEBUG') && WP_DEBUG) {
					error_log("RapidPress JS Delay: Delaying script with src '{$src}' and handle '{$handle}'");
				}
				return $this->get_delay_script_tag($src, $delay_duration);
			}

			if (defined('WP_DEBUG') && WP_DEBUG) {
				error_log("RapidPress JS Delay: Not delaying script with src '{$src}' and handle '{$handle}'");
			}
			return $matches[0];
		}, $html);

		return $html;
	}

	private function get_handle_for_src($src) {
		// Try to find an exact match first
		if (isset($this->script_handles[$src])) {
			$handle = $this->script_handles[$src];
			if (defined('WP_DEBUG') && WP_DEBUG) {
				error_log("RapidPress JS Delay: Found exact handle match: '{$handle}' for src '{$src}'");
			}
			return $handle;
		}

		// If no exact match, try to find a partial match
		foreach ($this->script_handles as $registered_src => $handle) {
			// Extract the filename from both URLs for comparison
			$src_filename = basename(parse_url($src, PHP_URL_PATH));
			$registered_filename = basename(parse_url($registered_src, PHP_URL_PATH));

			if ($src_filename === $registered_filename) {
				if (defined('WP_DEBUG') && WP_DEBUG) {
					error_log("RapidPress JS Delay: Found partial handle match: '{$handle}' for src '{$src}' (matched filename: {$src_filename})");
				}
				return $handle;
			}
		}

		if (defined('WP_DEBUG') && WP_DEBUG) {
			error_log("RapidPress JS Delay: No handle found for src '{$src}'");
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
				if (defined('WP_DEBUG') && WP_DEBUG) {
					error_log("RapidPress JS Delay: Excluded script by handle: '{$handle}' matches exclusion '{$exclusion}'");
				}
				return true;
			}

			// Check if the exclusion matches the URL (partial match)
			if (strpos($src, $exclusion) !== false) {
				if (defined('WP_DEBUG') && WP_DEBUG) {
					error_log("RapidPress JS Delay: Excluded script by URL: '{$src}' contains '{$exclusion}'");
				}
				return true;
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
				if (defined('WP_DEBUG') && WP_DEBUG) {
					error_log("RapidPress JS Delay: Specific script by handle: '{$handle}' matches file '{$file}'");
				}
				return true;
			}

			// Check if the file matches the URL (partial match)
			if (strpos($src, $file) !== false) {
				if (defined('WP_DEBUG') && WP_DEBUG) {
					error_log("RapidPress JS Delay: Specific script by URL: '{$src}' contains '{$file}'");
				}
				return true;
			}
		}
		return false;
	}
}
