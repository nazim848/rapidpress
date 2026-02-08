<?php

namespace RapidPress;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Image Lazy Loading Class
 * 
 * Implements modern lazy loading using loading="lazy" attribute with JavaScript fallback
 * for older browsers. Integrates with the optimization scope system.
 */
class Image_Lazy_Loading {

	private $lazy_loading_script_added = false;

	/**
	 * Constructor - Initialize lazy loading functionality
	 */
	public function __construct() {
		// Only run on frontend and if lazy loading is enabled
		if (!is_admin() && RP_Options::get_option('lazy_load_images')) {
			// Disable native WP lazy loading so RapidPress rules (skip-first/exclusions) stay authoritative.
			add_filter('wp_lazy_loading_enabled', array($this, 'disable_native_lazy_loading'), 10, 3);
			add_action('wp_enqueue_scripts', array($this, 'enqueue_lazy_loading_script'));
			add_filter('the_content', array($this, 'add_lazy_loading_to_content'), 999);
			add_filter('post_thumbnail_html', array($this, 'add_lazy_loading_to_post_thumbnail'), 999);
			add_filter('wp_get_attachment_image_attributes', array($this, 'add_lazy_loading_attributes'), 999, 3);

			// Use output buffering for complete HTML processing
			add_action('template_redirect', array($this, 'start_output_buffering'));
		}
	}

	/**
	 * Disable native WordPress lazy loading while RapidPress lazy loading is active.
	 *
	 * @param bool   $default  Default WP behavior.
	 * @param string $tag_name Tag name.
	 * @param string $context  Current context.
	 * @return bool
	 */
	public function disable_native_lazy_loading($default, $tag_name = '', $context = '') {
		return false;
	}

	/**
	 * Start output buffering to process all images in the final HTML
	 */
	public function start_output_buffering() {
		// Only apply if optimization scope allows it
		if (!\RapidPress\Optimization_Scope::should_optimize()) {
			return;
		}

		ob_start(array($this, 'process_html'));
	}

	/**
	 * Process the complete HTML output to add lazy loading to all images
	 * 
	 * @param string $html Complete HTML content
	 * @return string Modified HTML with lazy loading applied
	 */
	public function process_html($html) {
		// Skip if it's a feed, admin area, or POST request
		if (is_feed() || is_admin() || (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST')) {
			return $html;
		}

		// Skip if optimization scope doesn't allow it
		if (!\RapidPress\Optimization_Scope::should_optimize()) {
			return $html;
		}

		// Process all img tags in the HTML
		$html = preg_replace_callback('/<img([^>]+)>/i', array($this, 'process_image_tag'), $html);

		return $html;
	}

	/**
	 * Process individual image tags to add lazy loading
	 * 
	 * @param array $matches Regex matches from preg_replace_callback
	 * @return string Modified image tag
	 */
	public function process_image_tag($matches) {
		$img_tag = $matches[0];
		$attributes = $matches[1];

		// Enforce explicit exclusion rules before honoring existing loading attributes.
		if ($this->should_exclude_image($attributes)) {
			$attributes = $this->normalize_excluded_image_attributes($attributes);
			return '<img' . $attributes . '>';
		}

		// Respect images that already define a loading strategy.
		if (preg_match('/loading\s*=\s*["\']([^"\']*)["\']/', $attributes)) {
			return $img_tag;
		}

		// Add loading="lazy" attribute
		$lazy_attributes = $attributes . ' loading="lazy"';

		// Add data attributes for JavaScript fallback
		$lazy_attributes = $this->add_fallback_attributes($lazy_attributes);

		return '<img' . $lazy_attributes . '>';
	}

	/**
	 * Check if an image should be excluded from lazy loading
	 * 
	 * @param string $attributes Image tag attributes
	 * @return bool True if image should be excluded
	 */
	private function should_exclude_image($attributes) {
		$exclusions = RP_Options::get_option('lazy_load_exclusions', '');

		// Check exclusion patterns
		if (!empty($exclusions) && $this->matches_exclusion_patterns($attributes, $exclusions)) {
			return true;
		}

		return false;
	}

	/**
	 * Match exclusion patterns against image attributes.
	 *
	 * @param string $attributes Image attributes.
	 * @param string $exclusions Exclusion patterns, one per line.
	 * @return bool
	 */
	private function matches_exclusion_patterns($attributes, $exclusions) {
		$exclusion_patterns = array_filter(array_map('trim', explode("\n", $exclusions)));

		foreach ($exclusion_patterns as $pattern) {
			// Check class exclusions
			if (strpos($pattern, '.') === 0) {
				$class = substr($pattern, 1);
				if (preg_match('/class\s*=\s*["\'][^"\']*\b' . preg_quote($class, '/') . '\b[^"\']*["\']/', $attributes)) {
					return true;
				}
			}
			// Check ID exclusions
			elseif (strpos($pattern, '#') === 0) {
				$id = substr($pattern, 1);
				if (preg_match('/id\s*=\s*["\']' . preg_quote($id, '/') . '["\']/', $attributes)) {
					return true;
				}
			}
			// Check src pattern exclusions
			else {
				if (preg_match('/src\s*=\s*["\'][^"\']*' . preg_quote($pattern, '/') . '[^"\']*["\']/', $attributes)) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Add fallback attributes for JavaScript-based lazy loading
	 * 
	 * @param string $attributes Current image attributes
	 * @return string Modified attributes with fallback support
	 */
	private function add_fallback_attributes($attributes) {
		// Only add fallback if enabled
		if (!RP_Options::get_option('lazy_load_fallback', '1')) {
			return $attributes;
		}

		// Extract src attribute
		if (preg_match('/src\s*=\s*["\']([^"\']*)["\']/', $attributes, $src_matches)) {
			$src = $src_matches[1];
			$attachment_id = $this->extract_attachment_id_from_attributes($attributes);

			// Add data-src for fallback and placeholder
			$placeholder = $this->get_placeholder_src($src, $attachment_id);
			$attributes = str_replace($src_matches[0], 'src="' . $placeholder . '" data-src="' . $src . '"', $attributes);

				// Add lazy loading class
				if (preg_match('/class\s*=\s*["\']([^"\']*)["\']/', $attributes)) {
					$attributes = preg_replace('/class\s*=\s*["\']([^"\']*)["\']/', 'class="$1 rapidpress-lazy"', $attributes);
				} else {
					$attributes .= ' class="rapidpress-lazy"';
				}

				if ($this->is_blur_placeholder_enabled() && preg_match('/class\s*=\s*["\']([^"\']*)["\']/', $attributes)) {
					$attributes = preg_replace('/class\s*=\s*["\']([^"\']*)["\']/', 'class="$1 rapidpress-lazy-blur"', $attributes);
				}
			}

		return $attributes;
	}

	/**
	 * Get placeholder image source
	 * 
	 * @param string $original_src Original image source URL.
	 * @param int    $attachment_id Attachment ID when available.
	 * @return string Placeholder image data URI
	 */
	private function get_placeholder_src($original_src = '', $attachment_id = 0) {
		$placeholder_type = RP_Options::get_option('lazy_load_placeholder', 'transparent');

		switch ($placeholder_type) {
			case 'transparent':
				return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMSIgaGVpZ2h0PSIxIiB2aWV3Qm94PSIwIDAgMSAxIiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxyZWN0IHdpZHRoPSIxIiBoZWlnaHQ9IjEiIGZpbGw9InRyYW5zcGFyZW50Ii8+PC9zdmc+';

			case 'blur':
				$blur_placeholder = $this->get_blur_placeholder_image($original_src, $attachment_id);
				if (!empty($blur_placeholder)) {
					return $blur_placeholder;
				}
				return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDIwMCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnM+PGZpbHRlciBpZD0iYmx1ciI+PGZlR2F1c3NpYW5CbHVyIGluPSJTb3VyY2VHcmFwaGljIiBzdGREZXZpYXRpb249IjUiLz48L2ZpbHRlcj48L2RlZnM+PHJlY3Qgd2lkdGg9IjIwMCIgaGVpZ2h0PSIyMDAiIGZpbGw9IiNmMGYwZjAiIGZpbHRlcj0idXJsKCNibHVyKSIvPjwvc3ZnPg==';

			default:
				return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMSIgaGVpZ2h0PSIxIiB2aWV3Qm94PSIwIDAgMSAxIiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxyZWN0IHdpZHRoPSIxIiBoZWlnaHQ9IjEiIGZpbGw9InRyYW5zcGFyZW50Ii8+PC9zdmc+';
		}
	}

	/**
	 * Extract WordPress attachment ID from image attributes.
	 *
	 * @param string $attributes Image attributes string.
	 * @return int
	 */
	private function extract_attachment_id_from_attributes($attributes) {
		if (preg_match('/class\s*=\s*["\'][^"\']*\bwp-image-(\d+)\b[^"\']*["\']/', (string) $attributes, $matches)) {
			return intval($matches[1]);
		}

		return 0;
	}

	/**
	 * Resolve a better blur placeholder image URL from WP attachment sizes.
	 *
	 * @param string $original_src Original image source URL.
	 * @param int    $attachment_id Attachment ID when available.
	 * @return string
	 */
	private function get_blur_placeholder_image($original_src = '', $attachment_id = 0) {
		$attachment_id = intval($attachment_id);
		$original_src = (string) $original_src;

		if ($attachment_id <= 0 && !empty($original_src) && function_exists('attachment_url_to_postid')) {
			$attachment_id = intval(attachment_url_to_postid($original_src));
		}

		if ($attachment_id <= 0 || !function_exists('wp_get_attachment_image_src')) {
			return '';
		}

		$sizes = array('thumbnail', 'medium', 'medium_large');
		foreach ($sizes as $size) {
			$image = wp_get_attachment_image_src($attachment_id, $size);
			if (!is_array($image) || empty($image[0])) {
				continue;
			}

			$candidate = (string) $image[0];
			if (!empty($original_src) && $candidate === $original_src) {
				continue;
			}

			return esc_url_raw($candidate);
		}

		return '';
	}

	/**
	 * Check whether blur placeholder mode is enabled.
	 *
	 * @return bool
	 */
	private function is_blur_placeholder_enabled() {
		return RP_Options::get_option('lazy_load_placeholder', 'transparent') === 'blur';
	}

	/**
	 * Add lazy loading to content images
	 * 
	 * @param string $content Post content
	 * @return string Modified content
	 */
	public function add_lazy_loading_to_content($content) {
		if (!\RapidPress\Optimization_Scope::should_optimize()) {
			return $content;
		}

		return preg_replace_callback('/<img([^>]+)>/i', array($this, 'process_image_tag'), $content);
	}

	/**
	 * Add lazy loading to post thumbnails
	 * 
	 * @param string $html Post thumbnail HTML
	 * @return string Modified HTML
	 */
	public function add_lazy_loading_to_post_thumbnail($html) {
		if (!\RapidPress\Optimization_Scope::should_optimize()) {
			return $html;
		}

		return preg_replace_callback('/<img([^>]+)>/i', array($this, 'process_image_tag'), $html);
	}

	/**
	 * Add lazy loading attributes to attachment images
	 * 
	 * @param array $attr Image attributes
	 * @param object $attachment Attachment object
	 * @param string $size Image size
	 * @return array Modified attributes
	 */
	public function add_lazy_loading_attributes($attr, $attachment, $size) {
		if (!\RapidPress\Optimization_Scope::should_optimize()) {
			return $attr;
		}

		$attribute_string = $this->build_attributes_string($attr);

		// Apply explicit exclusions for attachment images.
		if ($this->should_exclude_image($attribute_string)) {
			$attr = $this->normalize_excluded_attachment_attributes($attr);
			return $attr;
		}

		$attr['loading'] = 'lazy';

		// Apply fallback attributes consistently for attachment images.
		if (RP_Options::get_option('lazy_load_fallback', '1') && !empty($attr['src'])) {
			if (empty($attr['data-src'])) {
				$attr['data-src'] = $attr['src'];
			}
			$attachment_id = (is_object($attachment) && isset($attachment->ID)) ? intval($attachment->ID) : 0;
			$attr['src'] = $this->get_placeholder_src($attr['data-src'], $attachment_id);

			$existing_class = isset($attr['class']) ? (string) $attr['class'] : '';
			if (strpos(' ' . $existing_class . ' ', ' rapidpress-lazy ') === false) {
				$attr['class'] = trim($existing_class . ' rapidpress-lazy');
				$existing_class = $attr['class'];
			}

			if ($this->is_blur_placeholder_enabled() && strpos(' ' . $existing_class . ' ', ' rapidpress-lazy-blur ') === false) {
				$attr['class'] = trim($existing_class . ' rapidpress-lazy-blur');
			}
		}

		return $attr;
	}

	/**
	 * Build an attribute string from image attributes for shared exclusion checks.
	 *
	 * @param array $attr Image attributes.
	 * @return string
	 */
	private function build_attributes_string($attr) {
		if (!is_array($attr) || empty($attr)) {
			return '';
		}

		$parts = array();
		foreach ($attr as $key => $value) {
			if ($value === '' || $value === null) {
				continue;
			}
			$parts[] = sanitize_key($key) . '="' . sanitize_text_field((string) $value) . '"';
		}

		return implode(' ', $parts);
	}

	/**
	 * Normalize excluded image attributes in HTML string form.
	 *
	 * @param string $attributes Raw image attributes string.
	 * @return string
	 */
	private function normalize_excluded_image_attributes($attributes) {
		$attributes = (string) $attributes;

		// Restore original source if a previous pass swapped src/data-src.
		if (preg_match('/\sdata-src\s*=\s*["\']([^"\']*)["\']/', $attributes, $data_src_matches)) {
			$data_src = $data_src_matches[1];
			if (preg_match('/\ssrc\s*=\s*["\'][^"\']*["\']/', $attributes)) {
				$attributes = preg_replace('/\ssrc\s*=\s*["\'][^"\']*["\']/', ' src="' . $data_src . '"', $attributes, 1);
			} else {
				$attributes .= ' src="' . $data_src . '"';
			}
			$attributes = preg_replace('/\sdata-src\s*=\s*["\'][^"\']*["\']/', '', $attributes);
		}

		$attributes = preg_replace('/\sloading\s*=\s*["\'][^"\']*["\']/', '', $attributes);

			$attributes = preg_replace_callback('/\sclass\s*=\s*["\']([^"\']*)["\']/', function ($matches) {
				$classes = preg_split('/\s+/', trim($matches[1]));
				if (!is_array($classes)) {
					return $matches[0];
				}
				$classes = array_filter($classes, function ($class_name) {
					return $class_name !== '' && $class_name !== 'rapidpress-lazy' && $class_name !== 'rapidpress-lazy-blur';
				});
			if (empty($classes)) {
				return '';
			}
			return ' class="' . implode(' ', $classes) . '"';
		}, $attributes, 1);

		return $attributes;
	}

	/**
	 * Normalize excluded attachment attributes in array form.
	 *
	 * @param array $attr Attachment image attributes.
	 * @return array
	 */
	private function normalize_excluded_attachment_attributes($attr) {
		if (!is_array($attr)) {
			return $attr;
		}

		if (!empty($attr['data-src'])) {
			$attr['src'] = $attr['data-src'];
		}

		unset($attr['data-src'], $attr['loading']);

			if (!empty($attr['class'])) {
				$classes = preg_split('/\s+/', trim((string) $attr['class']));
				if (is_array($classes)) {
					$classes = array_filter($classes, function ($class_name) {
						return $class_name !== '' && $class_name !== 'rapidpress-lazy' && $class_name !== 'rapidpress-lazy-blur';
					});
				if (empty($classes)) {
					unset($attr['class']);
				} else {
					$attr['class'] = implode(' ', $classes);
				}
			}
		}

		return $attr;
	}

	/**
	 * Enqueue lazy loading JavaScript for fallback support
	 */
	public function enqueue_lazy_loading_script() {
		if (!\RapidPress\Optimization_Scope::should_optimize()) {
			return;
		}

		// Only enqueue if fallback is enabled
		if (!RP_Options::get_option('lazy_load_fallback', '1')) {
			return;
		}

		// Register and enqueue the lazy loading script
		wp_register_script(
			'rapidpress-lazy-loading',
			'',
			array(),
			RAPIDPRESS_VERSION,
			true
		);

		// Add inline script for lazy loading fallback
		$inline_script = $this->get_lazy_loading_script();
		wp_add_inline_script('rapidpress-lazy-loading', $inline_script);
		wp_enqueue_script('rapidpress-lazy-loading');
	}

	/**
	 * Get the lazy loading JavaScript code
	 * 
	 * @return string JavaScript code for lazy loading
	 */
	private function get_lazy_loading_script() {
		$threshold = (int) RP_Options::get_option('lazy_load_threshold', 200);

		return "
		(function() {
			'use strict';
			
			// Check if Intersection Observer is supported
			if ('IntersectionObserver' in window) {
				// Modern approach using Intersection Observer
				const lazyImageObserver = new IntersectionObserver(function(entries, observer) {
					entries.forEach(function(entry) {
						if (entry.isIntersecting) {
							const lazyImage = entry.target;
							lazyImage.src = lazyImage.dataset.src;
							lazyImage.onload = function() {
								lazyImage.classList.add('loaded');
								lazyImage.classList.remove('rapidpress-lazy', 'rapidpress-lazy-blur');
							};
							lazyImageObserver.unobserve(lazyImage);
						}
					});
				}, {
					rootMargin: '{$threshold}px'
				});

				document.querySelectorAll('img.rapidpress-lazy').forEach(function(lazyImage) {
					lazyImageObserver.observe(lazyImage);
				});
			} else {
				// Fallback for older browsers
				let lazyImages = document.querySelectorAll('img.rapidpress-lazy');
				let active = false;

				const lazyLoad = function() {
					if (active === false) {
						active = true;
						setTimeout(function() {
							lazyImages.forEach(function(lazyImage) {
								if ((lazyImage.getBoundingClientRect().top <= window.innerHeight + {$threshold} && lazyImage.getBoundingClientRect().bottom >= 0) && getComputedStyle(lazyImage).display !== 'none') {
									lazyImage.src = lazyImage.dataset.src;
									lazyImage.onload = function() {
										lazyImage.classList.add('loaded');
										lazyImage.classList.remove('rapidpress-lazy', 'rapidpress-lazy-blur');
									};
								}
							});
							active = false;
						}, 200);
					}
				};

				document.addEventListener('DOMContentLoaded', lazyLoad);
				window.addEventListener('scroll', lazyLoad);
				window.addEventListener('resize', lazyLoad);
				window.addEventListener('orientationchange', lazyLoad);
			}
		})();
		";
	}
}
