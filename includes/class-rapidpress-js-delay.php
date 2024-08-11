<?php

class RapidPress_JS_Delay {
	public function __construct() {
		// Remove the filter hook as we're now applying delay directly in the HTML minifier
	}

	public function apply_js_delay($html) {
		if (is_admin() || !get_option('rapidpress_js_delay') || !RapidPress_Optimization_Scope::should_optimize()) {
			return $html;
		}

		$enable_exclusions = get_option('rapidpress_enable_js_delay_exclusions', '0');
		$exclusions = $enable_exclusions === '1' ? $this->get_exclusions() : array();
		$delay_duration = get_option('rapidpress_js_delay_duration', '1');

		// Check if HTML is empty
		if (empty($html)) {
			return $html;
		}

		// Use DOMDocument to parse and modify the HTML
		$dom = new DOMDocument();
		@$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

		$scripts = $dom->getElementsByTagName('script');
		$scripts_to_delay = array();

		foreach ($scripts as $script) {
			$src = $script->getAttribute('src');
			if ($src && !$this->is_excluded($src, $exclusions)) {
				$scripts_to_delay[] = $script;
			}
		}

		foreach ($scripts_to_delay as $script) {
			$new_script = $dom->createElement('script');
			$src = $script->getAttribute('src');

			if ($delay_duration === 'interaction') {
				$new_script->textContent = $this->get_interaction_delay_script($src);
			} else {
				$new_script->textContent = $this->get_timeout_delay_script($src, intval($delay_duration));
			}

			$script->parentNode->replaceChild($new_script, $script);
		}

		$html = $dom->saveHTML();
		return $html;
	}

	private function get_exclusions() {
		$exclusions_string = get_option('rapidpress_js_delay_exclusions', '');
		return array_filter(array_map('trim', explode("\n", $exclusions_string)));
	}

	private function is_excluded($src, $exclusions) {
		foreach ($exclusions as $exclusion) {
			if (strpos($src, $exclusion) !== false) {
				return true;
			}
		}
		return false;
	}

	private function get_interaction_delay_script($src) {
		return "document.addEventListener('DOMContentLoaded', function() {
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
		});";
	}

	private function get_timeout_delay_script($src, $delay) {
		return "setTimeout(function() {
			var script = document.createElement('script');
			script.src = '$src';
			document.body.appendChild(script);
		}, " . ($delay * 1000) . ");";
	}
}
