<?php

class RapidPress_HTML_Minifier {

	private $css_minifier;
	private $js_minifier;
	private $js_delay;

	public function __construct() {
		add_action('init', array($this, 'start_output_buffering'));
		$this->css_minifier = new RapidPress_CSS_Minifier();
		$this->js_minifier = new RapidPress_JS_Minifier();
		$this->js_delay = new RapidPress_JS_Delay();
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
			$html = $this->perform_html_minification($html);
		}

		// Minify inline CSS
		if (get_option('rapidpress_css_minify')) {
			$html = $this->css_minifier->minify_inline_css($html);
		}

		// Minify inline JavaScript
		if (get_option('rapidpress_js_minify')) {
			$html = $this->js_minifier->minify_inline_js($html);
		}

		// Apply JS delay after minification
		if (get_option('rapidpress_js_delay')) {
			$html = $this->js_delay->apply_js_delay($html);
		}

		return $html;
	}

	private function perform_html_minification($html) {
		// Remove comments (not containing IE conditional statements)
		$html = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $html);

		// Remove whitespace
		$html = preg_replace('/\s+/', ' ', $html);

		// Remove whitespace around HTML tags
		$html = preg_replace('/\s*(<\/?[^>]+>)\s*/', '$1', $html);

		// Remove extra spaces
		$html = preg_replace('/ +/', ' ', $html);

		return trim($html);
	}
}
