<?php

class RapidPress_JS_Minifier {

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
		if (!preg_match_all('/<script\b[^>]*>(.*?)<\/script>/is', $html, $matches)) {
			return $html;
		}

		foreach ($matches[0] as $i => $script_tag) {
			// Skip external scripts
			if (strpos($script_tag, 'src=') !== false) {
				continue;
			}
			$minified_js = $this->minify($matches[1][$i]);
			$html = str_replace($script_tag, "<script>{$minified_js}</script>", $html);
		}

		return $html;
	}
}
