<?php

class RapidPress_HTML_Minifier {

	private $css_minifier;

	public function __construct() {
		add_action('init', array($this, 'start_output_buffering'));
		$this->css_minifier = new RapidPress_CSS_Minifier();
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
			// Remove comments (but keep IE conditionals)
			$html = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $html);

			// Remove whitespace
			$html = preg_replace('/\s+/', ' ', $html);

			// Remove whitespace around HTML tags
			$html = preg_replace('/>\s+</', '><', $html);

			// Remove whitespace at the start and end of the HTML
			$html = trim($html);
		}

		// Minify inline CSS
		if (get_option('rapidpress_css_minify')) {
			$html = $this->css_minifier->minify_inline_css($html);
		}

		return $html;
	}
}
