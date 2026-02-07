<?php

namespace RapidPress;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * JS_Delay class for handling JavaScript loading delay
 * 
 * This class implements JavaScript delay functionality using WordPress's
 * proper enqueuing functions instead of direct script tag output
 */
class JS_Delay {
	private $script_handles = array();
	private $delayed_scripts = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		// Hook into WordPress script system at various points to ensure complete coverage
		add_action('wp_enqueue_scripts', array($this, 'capture_script_handles'), 9999);
		add_action('wp_enqueue_scripts', array($this, 'register_delayed_scripts'), 10000);
		
		// Add script_loader_tag filter to directly modify script tags in the HTML output
		add_filter('script_loader_tag', array($this, 'filter_script_loader_tag'), 10, 3);
	}

	/**
	 * Capture all registered script handles and their URLs
	 */
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

				}
			}
	}

	/**
	 * Register and enqueue delayed scripts
	 * 
	 * This method processes all WordPress enqueued scripts and delays them
	 * according to the plugin settings
	 */
	public function register_delayed_scripts() {
		if (is_admin() || !RP_Options::get_option('js_delay') || !\RapidPress\Optimization_Scope::should_optimize()) {
			return;
		}

			$delay_type = RP_Options::get_option('js_delay_type', 'all');
		$specific_files = $delay_type === 'specific' ? $this->get_specific_files() : array();
		$exclusions = ($delay_type === 'all' && RP_Options::get_option('enable_js_delay_exclusions')) ? $this->get_exclusions() : array();
		$delay_duration = RP_Options::get_option('js_delay_duration', '1');

			global $wp_scripts;

		if (!is_object($wp_scripts)) {
			return;
		}

		// Process all enqueued scripts
		$scripts_to_delay = array();
		foreach ($wp_scripts->queue as $key => $handle) {
			if (!isset($wp_scripts->registered[$handle])) {
				continue;
			}

			$script = $wp_scripts->registered[$handle];

			if (!isset($script->src) || empty($script->src)) {
				continue;
			}

			$src = $script->src;

			// Convert relative URLs to absolute
			if (strpos($src, '//') === 0) {
				$src = 'https:' . $src;
			} elseif (strpos($src, '/') === 0) {
				$src = site_url($src);
			}

			$should_delay = false;

			if ($delay_type === 'specific' && $this->is_specific_file($src, $specific_files, $handle)) {
				$should_delay = true;
			} elseif ($delay_type === 'all' && !$this->is_excluded($src, $exclusions, $handle)) {
				$should_delay = true;
			}

			if ($should_delay) {
				$scripts_to_delay[] = array(
					'handle' => $handle,
					'src' => $src,
					'deps' => $script->deps,
					'ver' => $script->ver,
					'in_footer' => isset($script->extra['group']) && $script->extra['group'] === 1
				);
			}
		}

		// Dequeue original scripts and register delayed versions
		foreach ($scripts_to_delay as $script) {
			wp_dequeue_script($script['handle']);
			$this->register_delayed_script($script['src'], $delay_duration, $script['handle']);
		}
	}

	/**
	 * Legacy method for backward compatibility
	 * 
	 * This method is kept for backward compatibility but now uses the WordPress enqueuing system
	 * 
	 * @param string $html The HTML content to process
	 * @return string The processed HTML content
	 */
	public function apply_js_delay($html) {
		// This method is now primarily handled by register_delayed_scripts
		// but we keep this for backward compatibility with any code that might call it directly
		
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

		// Process inline script tags that aren't handled by WordPress
		// These are typically hardcoded in theme files or added by other plugins
		$pattern = '/<script\b([^>]*)src=["\']([^"\']+)["\']([^>]*)>/i';
		$html = preg_replace_callback($pattern, function ($matches) use ($delay_type, $specific_files, $exclusions, $delay_duration) {
			$before_src = $matches[1];
			$src = $matches[2];
			$after_src = $matches[3];

			// Skip if this is a WordPress enqueued script (they're handled by register_delayed_scripts)
			if (
				strpos($before_src . $after_src, 'wp-') !== false ||
				strpos($before_src . $after_src, 'id="') !== false
			) {
				return $matches[0];
			}

			// Try to get the handle from our mapping
			$handle = $this->get_handle_for_src($src);

			// Check if this script should be delayed
			if (($delay_type === 'specific' && $this->is_specific_file($src, $specific_files, $handle)) ||
				($delay_type === 'all' && !$this->is_excluded($src, $exclusions, $handle))
			) {
				// Generate a unique ID for this script
				$script_id = 'rapidpress-delayed-' . md5($src);

				// Register and enqueue the delayed version
				$this->register_delayed_script($src, $delay_duration, $script_id);

				// Return an empty placeholder where the script tag was
				return "<!-- RapidPress delayed script: {$src} -->";
			}

			return $matches[0];
		}, $html);

		return $html;
	}

	/**
	 * Get the handle for a script source URL
	 * 
	 * @param string $src The script source URL
	 * @return string The handle for the script, or empty string if not found
	 */
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

	/**
	 * Register a delayed script with WordPress
	 *
	 * @param string $src The script source URL
	 * @param string $delay_duration The delay duration or 'interaction'
	 * @param string $original_handle The original script handle (optional)
	 * @return string The handle of the registered script
	 */
	private function register_delayed_script($src, $delay_duration, $original_handle = '') {
		// Create a unique handle for this delayed script
		$handle = !empty($original_handle) ? 'rapidpress-delayed-' . $original_handle : 'rapidpress-delayed-' . md5($src);

		// Skip if we've already registered this script
		if (in_array($handle, $this->delayed_scripts)) {
			return $handle;
		}

		// Special handling for jQuery core and migrate
		$is_jquery = (!empty($original_handle) && (strpos($original_handle, 'jquery') === 0 || strpos($original_handle, 'jquery-core') === 0 || strpos($original_handle, 'jquery-migrate') === 0));

		// Register an empty script that will serve as our container
		// For jQuery, we need to make sure it loads in the head, not the footer
		wp_register_script($handle, '', array(), RAPIDPRESS_VERSION, !$is_jquery);

		$inline_script = $this->build_delayed_loader_script($src, $delay_duration, $is_jquery);

		// Add the inline script
		wp_add_inline_script($handle, $inline_script);

		// Enqueue the script
		wp_enqueue_script($handle);

		// Remember that we've processed this script
		$this->delayed_scripts[] = $handle;

		return $handle;
	}

	/**
	 * Filter script loader tag to catch any scripts that weren't caught by the enqueue system
	 * This is our last line of defense for scripts that are hardcoded or added by other plugins
	 *
	 * @param string $tag The script tag
	 * @param string $handle The script handle
	 * @param string $src The script source
	 * @return string The modified script tag
	 */
	public function filter_script_loader_tag($tag, $handle, $src) {
		// Skip if optimization is disabled
		if (is_admin() || !RP_Options::get_option('js_delay') || !\RapidPress\Optimization_Scope::should_optimize()) {
			return $tag;
		}
		
		// Skip if this is already a delayed script
		if (strpos($handle, 'rapidpress-delayed-') === 0) {
			return $tag;
		}
		
		// Get delay settings
		$delay_type = RP_Options::get_option('js_delay_type', 'all');
		$specific_files = $delay_type === 'specific' ? $this->get_specific_files() : array();
		$exclusions = ($delay_type === 'all' && RP_Options::get_option('enable_js_delay_exclusions')) ? $this->get_exclusions() : array();
		$delay_duration = RP_Options::get_option('js_delay_duration', '1');
		
		// Check if this script should be delayed
		$should_delay = false;
		
		// Special handling for jQuery
		$is_jquery = (strpos($handle, 'jquery') === 0 || strpos($handle, 'jquery-core') === 0 || strpos($handle, 'jquery-migrate') === 0);
		
		if ($delay_type === 'specific' && $this->is_specific_file($src, $specific_files, $handle)) {
			$should_delay = true;
		} elseif ($delay_type === 'all' && !$this->is_excluded($src, $exclusions, $handle)) {
			$should_delay = true;
		}
		
		// If this script should be delayed, replace the tag with our delayed version.
		if ($should_delay) {
			$id_attr = '';
			if (preg_match('/id=[\'\"](.*?)[\'\"]/', $tag, $matches)) {
				$id_attr = $matches[1];
			}
			if ('' === $id_attr) {
				$id_attr = 'rapidpress-script-' . md5($src);
			}

			$id_attr = sanitize_html_class($id_attr);
			if ('' === $id_attr) {
				$id_attr = 'rapidpress-script-' . md5($src);
			}

			$delayed_script = $this->build_delayed_loader_script($src, $delay_duration, $is_jquery);

			return '<script id="' . esc_attr($id_attr) . '" type="text/javascript">' . $delayed_script . '</script>';
		}
		
		return $tag;
	}

	/**
	 * Build a safe loader script for deferred JS execution.
	 *
	 * @param string $src Script URL.
	 * @param string $delay_duration Delay setting.
	 * @param bool   $is_jquery Whether this is a jQuery core/migrate script.
	 * @return string
	 */
	private function build_delayed_loader_script($src, $delay_duration, $is_jquery) {
		$target = $is_jquery ? 'document.head' : 'document.body';
		$encoded_src = wp_json_encode($src);

		if ('interaction' === $delay_duration) {
			return "document.addEventListener('DOMContentLoaded', function() {" .
				'var loadScript = function() {' .
				'var script = document.createElement("script");' .
				"script.src = {$encoded_src};" .
				$target . '.appendChild(script);' .
				'["keydown","mouseover","touchmove","touchstart","wheel"].forEach(function(event) {' .
				'document.removeEventListener(event, loadScript, {passive: true});' .
				'});' .
				'};' .
				'["keydown","mouseover","touchmove","touchstart","wheel"].forEach(function(event) {' .
				'document.addEventListener(event, loadScript, {passive: true});' .
				'});' .
				'});';
		}

		$delay = intval($delay_duration) * 1000;

		return 'setTimeout(function() {' .
			'var script = document.createElement("script");' .
			"script.src = {$encoded_src};" .
			$target . '.appendChild(script);' .
			'}, ' . $delay . ');';
	}

	/**
	 * Get the list of exclusions from plugin options
	 * 
	 * @return array Array of exclusions
	 */
	private function get_exclusions() {
		$exclusions_string = RP_Options::get_option('js_delay_exclusions', '');
		return array_filter(array_map('trim', explode("\n", $exclusions_string)));
	}

	/**
	 * Check if a script should be excluded from delay
	 * 
	 * @param string $src The script source URL
	 * @param array $exclusions Array of exclusion patterns
	 * @param string $handle The script handle (optional)
	 * @return bool True if the script should be excluded, false otherwise
	 */
	private function is_excluded($src, $exclusions, $handle = '') {
		// Identify jQuery scripts by handle or URL
		$wp_core_scripts = array('jquery', 'jquery-core', 'jquery-migrate');
		$is_jquery_handle = !empty($handle) && in_array($handle, $wp_core_scripts);
		$is_jquery_url = false;
		
		// Check if the URL contains jquery
		if (stripos($src, 'jquery') !== false && (stripos($src, 'jquery.js') !== false || stripos($src, 'jquery.min.js') !== false || stripos($src, 'jquery-migrate') !== false)) {
			$is_jquery_url = true;
		}
		
		// For all exclusions, check both handle and URL
		foreach ($exclusions as $exclusion) {
			// For jQuery scripts, check if the exclusion matches the handle
			if ($is_jquery_handle && $exclusion === $handle) {
				return true;
			}
			
			// For jQuery scripts, check if the exclusion is in the URL
			if ($is_jquery_url && stripos($src, $exclusion) !== false) {
				return true;
			}
			
			// For all scripts, check if the exclusion matches the handle (exact match)
			if (!empty($handle) && $exclusion === $handle) {
				return true;
			}

			// For all scripts, check if the exclusion matches the URL (partial match)
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

	/**
	 * Get the list of specific files to delay from plugin options
	 * 
	 * @return array Array of specific files
	 */
	private function get_specific_files() {
		$specific_files_string = RP_Options::get_option('js_delay_specific_files', '');
		return array_filter(array_map('trim', explode("\n", $specific_files_string)));
	}

	/**
	 * Check if a script is in the list of specific files to delay
	 * 
	 * @param string $src The script source URL
	 * @param array $specific_files Array of specific file patterns
	 * @param string $handle The script handle (optional)
	 * @return bool True if the script is in the list, false otherwise
	 */
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
