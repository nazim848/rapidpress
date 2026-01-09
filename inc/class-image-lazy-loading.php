<?php

namespace RapidPress;

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
			add_action('wp_enqueue_scripts', array($this, 'enqueue_lazy_loading_script'));
			add_filter('the_content', array($this, 'add_lazy_loading_to_content'), 999);
			add_filter('post_thumbnail_html', array($this, 'add_lazy_loading_to_post_thumbnail'), 999);
			add_filter('wp_get_attachment_image_attributes', array($this, 'add_lazy_loading_attributes'), 999, 3);

			// Use output buffering for complete HTML processing
			add_action('template_redirect', array($this, 'start_output_buffering'));
		}
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

		// Skip if image already has loading attribute
		if (preg_match('/loading\s*=\s*["\']([^"\']*)["\']/', $attributes)) {
			return $img_tag;
		}

		// Skip if image should be excluded
		if ($this->should_exclude_image($attributes)) {
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
		// Get exclusion settings
		$skip_first = (int) RP_Options::get_option('lazy_load_skip_first', 2);
		$exclusions = RP_Options::get_option('lazy_load_exclusions', '');

		// Skip first N images (above the fold)
		static $image_count = 0;
		$image_count++;

		if ($image_count <= $skip_first) {
			return true;
		}

		// Check exclusion patterns
		if (!empty($exclusions)) {
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

			// Add data-src for fallback and placeholder
			$placeholder = $this->get_placeholder_src();
			$attributes = str_replace($src_matches[0], 'src="' . $placeholder . '" data-src="' . $src . '"', $attributes);

			// Add lazy loading class
			if (preg_match('/class\s*=\s*["\']([^"\']*)["\']/', $attributes)) {
				$attributes = preg_replace('/class\s*=\s*["\']([^"\']*)["\']/', 'class="$1 rapidpress-lazy"', $attributes);
			} else {
				$attributes .= ' class="rapidpress-lazy"';
			}
		}

		return $attributes;
	}

	/**
	 * Get placeholder image source
	 * 
	 * @return string Placeholder image data URI
	 */
	private function get_placeholder_src() {
		$placeholder_type = RP_Options::get_option('lazy_load_placeholder', 'transparent');

		switch ($placeholder_type) {
			case 'transparent':
				return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMSIgaGVpZ2h0PSIxIiB2aWV3Qm94PSIwIDAgMSAxIiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxyZWN0IHdpZHRoPSIxIiBoZWlnaHQ9IjEiIGZpbGw9InRyYW5zcGFyZW50Ii8+PC9zdmc+';

			case 'blur':
				return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDIwMCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnM+PGZpbHRlciBpZD0iYmx1ciI+PGZlR2F1c3NpYW5CbHVyIGluPSJTb3VyY2VHcmFwaGljIiBzdGREZXZpYXRpb249IjUiLz48L2ZpbHRlcj48L2RlZnM+PHJlY3Qgd2lkdGg9IjIwMCIgaGVpZ2h0PSIyMDAiIGZpbGw9IiNmMGYwZjAiIGZpbHRlcj0idXJsKCNibHVyKSIvPjwvc3ZnPg==';

			default:
				return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMSIgaGVpZ2h0PSIxIiB2aWV3Qm94PSIwIDAgMSAxIiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxyZWN0IHdpZHRoPSIxIiBoZWlnaHQ9IjEiIGZpbGw9InRyYW5zcGFyZW50Ii8+PC9zdmc+';
		}
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

		// Skip if loading attribute already exists
		if (isset($attr['loading'])) {
			return $attr;
		}

		// Add loading="lazy" attribute
		$attr['loading'] = 'lazy';

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
								lazyImage.classList.remove('rapidpress-lazy');
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
										lazyImage.classList.remove('rapidpress-lazy');
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
