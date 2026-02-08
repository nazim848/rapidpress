<?php

namespace RapidPress;

use RapidPress\RP_Options;

class Public_Core {
	private $plugin_name;
	private $version;

	public function __construct($plugin_name, $version) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	public function enqueue_styles() {
		// Add lazy loading styles if enabled
		if (RP_Options::get_option('lazy_load_images') && RP_Options::get_option('lazy_load_fallback', '1')) {
			$this->add_lazy_loading_styles();
		}
	}

	/**
	 * Add inline CSS for lazy loading functionality
	 */
	private function add_lazy_loading_styles() {
		$css = "
		.rapidpress-lazy {
			opacity: 1;
			transition: opacity 0.35s ease-in-out, filter 0.45s ease-in-out, transform 0.45s ease-in-out;
		}
		.rapidpress-lazy.loaded,
		img:not(.rapidpress-lazy) {
			opacity: 1;
		}
		.rapidpress-lazy.rapidpress-lazy-blur {
			filter: blur(14px);
			transform: scale(1.02);
		}
		.rapidpress-lazy.loaded {
			filter: blur(0);
			transform: none;
		}
		/* Prevent layout shift during loading */
		.rapidpress-lazy {
			background-color: #f0f0f0;
			background-image: linear-gradient(45deg, #f0f0f0 25%, transparent 25%),
							  linear-gradient(-45deg, #f0f0f0 25%, transparent 25%),
							  linear-gradient(45deg, transparent 75%, #f0f0f0 75%),
							  linear-gradient(-45deg, transparent 75%, #f0f0f0 75%);
			background-size: 20px 20px;
			background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
		}
		";

		wp_add_inline_style('wp-block-library', $css);
	}

	public function enqueue_scripts() {
		// Placeholder for public scripts
	}
}
