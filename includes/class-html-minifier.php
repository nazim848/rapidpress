<?php

namespace RapidPress;

class HTML_Minifier {

	private $css_minifier;
	private $js_minifier;
	private $js_delay;

	public function __construct() {
		add_action('init', array($this, 'start_output_buffering'));
		$this->css_minifier = new \RapidPress\CSS_Minifier();
		$this->js_minifier = new \RapidPress\JS_Minifier();
		$this->js_delay = new \RapidPress\JS_Delay();
	}

	public function start_output_buffering() {
		ob_start(array($this, 'process_html'));
	}

	public function process_html($html) {
		// Exclude admin, POST requests, and pages not in the optimization scope
		if (is_admin() || $_SERVER['REQUEST_METHOD'] == 'POST' || !\RapidPress\Optimization_Scope::should_optimize()) {
			return $html;
		}

		// Apply JS delay first
		if (RP_Options::get_option('js_delay')) {
			$html = $this->js_delay->apply_js_delay($html);
		}

		// Minify HTML
		if (RP_Options::get_option('html_minify')) {
			$html = $this->perform_html_minification($html);
		}

		// Minify inline CSS
		if (RP_Options::get_option('css_minify')) {
			$html = $this->css_minifier->minify_inline_css($html);
		}

		// Minify inline JavaScript
		if (RP_Options::get_option('js_minify')) {
			$html = $this->js_minifier->minify_inline_js($html);
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
