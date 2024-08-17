<?php

namespace RapidPress;

class JS_Delay {
	public function apply_js_delay($html) {
		if (is_admin() || !get_option('rapidpress_js_delay') || !\RapidPress\Optimization_Scope::should_optimize()) {
			return $html;
		}

		$delay_type = get_option('rapidpress_js_delay_type', 'all');
		$specific_files = $delay_type === 'specific' ? $this->get_specific_files() : array();
		$exclusions = ($delay_type === 'all' && get_option('rapidpress_enable_js_delay_exclusions')) ? $this->get_exclusions() : array();
		$delay_duration = get_option('rapidpress_js_delay_duration', '1');

		if (empty($html)) {
			return $html;
		}

		// Use a regular expression to find and modify script tags
		$pattern = '/<script\b[^>]*src=["\']([^"\']+)["\'][^>]*>/i';
		$html = preg_replace_callback($pattern, function ($matches) use ($delay_type, $specific_files, $exclusions, $delay_duration) {
			$src = $matches[1];
			if (($delay_type === 'specific' && $this->is_specific_file($src, $specific_files)) ||
				($delay_type === 'all' && !$this->is_excluded($src, $exclusions))
			) {
				return $this->get_delay_script_tag($src, $delay_duration);
			}
			return $matches[0];
		}, $html);

		return $html;
	}

	private function get_delay_script_tag($src, $delay_duration) {
		if ($delay_duration === 'interaction') {
			return "<script type='text/javascript'>
                document.addEventListener('DOMContentLoaded', function() {
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
                });
            </script>";
		} else {
			$delay = intval($delay_duration) * 1000;
			return "<script type='text/javascript'>
                setTimeout(function() {
                    var script = document.createElement('script');
                    script.src = '$src';
                    document.body.appendChild(script);
                }, $delay);
            </script>";
		}
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

	private function get_specific_files() {
		$specific_files_string = get_option('rapidpress_js_delay_specific_files', '');
		return array_filter(array_map('trim', explode("\n", $specific_files_string)));
	}

	private function is_specific_file($src, $specific_files) {
		foreach ($specific_files as $file) {
			if (strpos($src, $file) !== false) {
				return true;
			}
		}
		return false;
	}
}
