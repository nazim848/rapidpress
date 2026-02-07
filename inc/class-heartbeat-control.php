<?php

namespace RapidPress;

if (!defined('ABSPATH')) {
	exit;
}

class Heartbeat_Control {
	public function __construct() {
		add_action('admin_enqueue_scripts', array($this, 'handle_admin_heartbeat'), 1);
		add_action('wp_enqueue_scripts', array($this, 'handle_frontend_heartbeat'), 1);
		add_filter('heartbeat_settings', array($this, 'filter_heartbeat_settings'));
	}

	public function handle_admin_heartbeat() {
		$setting = RP_Options::get_option('disable_heartbeat');
		if (empty($setting)) {
			return;
		}

		if ($setting === 'disable_everywhere') {
			$this->dequeue_heartbeat();
			return;
		}

		if ($setting === 'allow_posts') {
			if (!$this->is_post_editor_screen()) {
				$this->dequeue_heartbeat();
			}
		}
	}

	public function handle_frontend_heartbeat() {
		$setting = RP_Options::get_option('disable_heartbeat');
		if (empty($setting)) {
			return;
		}

		if ($setting === 'disable_everywhere' || $setting === 'allow_posts') {
			$this->dequeue_heartbeat();
		}
	}

	public function filter_heartbeat_settings($settings) {
		$frequency = RP_Options::get_option('heartbeat_frequency');
		if (empty($frequency)) {
			return $settings;
		}

		$interval = intval($frequency);
		if ($interval > 0) {
			$settings['interval'] = $interval;
		}

		return $settings;
	}

	private function dequeue_heartbeat() {
		wp_deregister_script('heartbeat');
		wp_dequeue_script('heartbeat');
	}

	private function is_post_editor_screen() {
		global $pagenow;
		return in_array($pagenow, array('post.php', 'post-new.php'), true);
	}
}
