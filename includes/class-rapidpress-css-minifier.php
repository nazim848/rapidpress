<?php

class RapidPress_CSS_Minifier {

	public function minify($css) {
		// Remove comments
		$css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);

		// Remove space after colons
		$css = str_replace(': ', ':', $css);

		// Remove whitespace
		$css = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $css);

		return $css;
	}

	public function minify_inline_css($html) {
		if (!preg_match_all('/<style\b[^>]*>(.*?)<\/style>/is', $html, $matches)) {
			return $html;
		}

		foreach ($matches[0] as $i => $style_tag) {
			$minified_css = $this->minify($matches[1][$i]);
			$html = str_replace($style_tag, "<style>{$minified_css}</style>", $html);
		}

		return $html;
	}

	public function minify_css_files() {
		// This is a placeholder for future implementation
		// Here we would scan for CSS files, minify them, and cache the results
	}
}
