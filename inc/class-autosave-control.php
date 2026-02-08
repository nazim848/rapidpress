<?php

namespace RapidPress;

if (!defined('ABSPATH')) {
	exit;
}

class Autosave_Control {
	public function __construct() {
		add_action('init', array($this, 'apply_autosave_interval'), 1);
		add_action('admin_notices', array($this, 'maybe_notice_autosave_interval'));
	}

	public function apply_autosave_interval() {
		$interval = RP_Options::get_option('autosave_interval');
		if (empty($interval)) {
			return;
		}

		if (defined('AUTOSAVE_INTERVAL')) {
			return;
		}

		$seconds = $this->normalize_interval($interval);
		if ($seconds > 0) {
			define('AUTOSAVE_INTERVAL', $seconds);
		}
	}

	public function maybe_notice_autosave_interval() {
		if (!is_admin()) {
			return;
		}

		$interval = RP_Options::get_option('autosave_interval');
		if (empty($interval)) {
			return;
		}

		if (defined('AUTOSAVE_INTERVAL')) {
			echo "<div class='notice notice-error'>";
			echo "<p>";
			echo "<strong>" . esc_html(__('RapidPress Warning', 'rapidpress')) . ":</strong> ";
			echo esc_html(__('AUTOSAVE_INTERVAL is already defined elsewhere. RapidPress will not override it.', 'rapidpress'));
			echo "</p>";
			echo "</div>";
		}
	}

	private function normalize_interval($interval) {
		$value = intval($interval);
		if ($value <= 0) {
			return 0;
		}

		if ($value === 172800) {
			return $value;
		}

		return $value * 60;
	}
}
