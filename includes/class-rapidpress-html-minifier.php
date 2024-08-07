<?php

class RapidPress_HTML_Minifier {

	private $css_minifier;
	private $js_minifier;

	public function __construct() {
		add_action('init', array($this, 'start_output_buffering'));
		$this->css_minifier = new RapidPress_CSS_Minifier();
		$this->js_minifier = new RapidPress_JS_Minifier();
	}

	public function start_output_buffering() {
		ob_start(array($this, 'minify_html'));
	}

	public function minify_html($html) {
		// Don't minify if it's the admin area or a POST request
		if (is_admin() || $_SERVER['REQUEST_METHOD'] == 'POST') {
			return $html;
		}

		// Minify HTML
		if (get_option('rapidpress_html_minify')) {
			// ... (existing HTML minification code)
		}

		// Minify inline CSS
		if (get_option('rapidpress_css_minify')) {
			$html = $this->css_minifier->minify_inline_css($html);
		}

		// Minify inline JavaScript
		if (get_option('rapidpress_js_minify')) {
			$html = $this->js_minifier->minify_inline_js($html);
		}

		return $html;
	}
}
