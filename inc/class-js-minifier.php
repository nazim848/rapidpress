<?php

namespace RapidPress;

class JS_Minifier {

	public function minify($js) {
		if (trim($js) === "") return $js;

		return $this->minify_js($js);
	}

	private function minify_js($js) {
		// Remove comments
		$js = preg_replace('/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/', '', $js);

		// Remove whitespace
		$js = preg_replace('/\s+/', ' ', $js);

		// Remove whitespace around operators
		$js = preg_replace('/\s*([=+\-*\/%<>!&|,{}()[\];:?])\s*/', '$1', $js);

		// Remove trailing semicolons
		$js = preg_replace('/;+\}/', '}', $js);

		return trim($js);
	}

	public function minify_inline_js($html) {
		if (!preg_match_all('/<script\b([^>]*)>(.*?)<\/script>/is', $html, $matches)) {
			return $html;
		}

		foreach ($matches[0] as $i => $script_tag) {
			$attributes = isset($matches[1][$i]) ? $matches[1][$i] : '';
			$inline_js = isset($matches[2][$i]) ? $matches[2][$i] : '';

			// Skip external scripts.
			if (stripos($attributes, 'src=') !== false) {
				continue;
			}

			// Skip non-JS script types such as application/ld+json.
			if (
				preg_match('/\btype\s*=\s*["\']([^"\']+)["\']/i', $attributes, $type_match) &&
				!in_array(strtolower($type_match[1]), array('text/javascript', 'application/javascript', 'module', 'text/ecmascript', 'application/ecmascript'), true)
			) {
				continue;
			}

			// Skip RapidPress delayed loaders; naive comment stripping can corrupt URLs.
			if (strpos($attributes, 'rapidpress-script-') !== false || strpos($attributes, 'rapidpress-delayed-') !== false) {
				continue;
			}

			// Skip WordPress inline companion blocks (-js-before / -js-after).
			// These often contain plugin-generated code that this naive minifier can break.
			if (strpos($attributes, '-js-after') !== false || strpos($attributes, '-js-before') !== false) {
				continue;
			}

			// Skip RapidPress lazy-loading inline script block.
			if (strpos($attributes, 'rapidpress-lazy-loading') !== false) {
				continue;
			}

			// Skip scripts likely to contain URL protocols that this minifier cannot safely parse.
			if (strpos($inline_js, '://') !== false) {
				continue;
			}

			$minified_js = $this->minify($inline_js);
			$html = str_replace($script_tag, "<script>{$minified_js}</script>", $html);
		}

		return $html;
	}
}
