<?php

class RapidPress_HTML_Minifier {

	public function __construct() {
		add_action('init', array($this, 'start_output_buffering'));
	}

	public function start_output_buffering() {
		if (get_option('rapidpress_html_minify')) {
			ob_start(array($this, 'minify_html'));
		}
	}

	public function minify_html($html) {
		// Don't minify if it's the admin area or a POST request
		if (is_admin() || $_SERVER['REQUEST_METHOD'] == 'POST') {
			return $html;
		}

		// Remove comments (but keep IE conditionals)
		$html = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $html);

		// Remove whitespace
		$html = preg_replace('/\s+/', ' ', $html);

		// Remove whitespace around HTML tags
		$html = preg_replace('/>\s+</', '><', $html);

		// Remove whitespace at the start and end of the HTML
		$html = trim($html);

		return $html;
	}
}
