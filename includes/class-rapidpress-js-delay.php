<?php

class RapidPress_JS_Delay {
	public function __construct() {
		add_filter('script_loader_tag', array($this, 'delay_js'), 10, 3);
	}

	public function delay_js($tag, $handle, $src) {
		// Only apply delay to frontend scripts
		if (is_admin()) {
			return $tag;
		}

		if (!get_option('rapidpress_js_delay')) {
			return $tag;
		}

		$exclusions = $this->get_exclusions();

		if ($this->is_excluded($src, $exclusions)) {
			return $tag;
		}

		$delay_duration = get_option('rapidpress_js_delay_duration', '1');

		if ($delay_duration === 'interaction') {
			return $this->delay_until_interaction($tag, $src);
		} else {
			return $this->delay_by_timeout($tag, $src, intval($delay_duration));
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

	private function delay_until_interaction($tag, $src) {
		$delayed_script = sprintf(
			'<script type="text/javascript">
                document.addEventListener("DOMContentLoaded", function() {
                    var loadScript = function() {
                        var script = document.createElement("script");
                        script.src = "%s";
                        document.body.appendChild(script);
                        ["keydown", "mouseover", "touchmove", "touchstart", "wheel"].forEach(function(event) {
                            document.removeEventListener(event, loadScript, {passive: true});
                        });
                    };
                    ["keydown", "mouseover", "touchmove", "touchstart", "wheel"].forEach(function(event) {
                        document.addEventListener(event, loadScript, {passive: true});
                    });
                });
            </script>',
			esc_url($src)
		);
		return str_replace($tag, $delayed_script, $tag);
	}

	private function delay_by_timeout($tag, $src, $delay) {
		$delayed_script = sprintf(
			'<script type="text/javascript">
                setTimeout(function() {
                    var script = document.createElement("script");
                    script.src = "%s";
                    document.body.appendChild(script);
                }, %d);
            </script>',
			esc_url($src),
			$delay * 1000
		);
		return str_replace($tag, $delayed_script, $tag);
	}
}
