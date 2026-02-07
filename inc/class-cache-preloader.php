<?php

namespace RapidPress;

class Cache_Preloader {
	const CRON_HOOK = 'rapidpress_cache_preload_event';
	const LAST_RUN_OPTION = 'rapidpress_cache_preload_last_run';
	const LAST_COUNT_OPTION = 'rapidpress_cache_preload_last_count';

	public function __construct() {
		add_action('init', array($this, 'maybe_sync_schedule'));
		add_action(self::CRON_HOOK, array($this, 'run_scheduled_preload'));
	}

	public function maybe_sync_schedule() {
		self::sync_schedule_from_options();
	}

	public static function sync_schedule_from_options() {
		$enabled = rest_sanitize_boolean(RP_Options::get_option('cache_preload_enabled', false));
		$scheduled = wp_next_scheduled(self::CRON_HOOK);

		if ($enabled && !$scheduled) {
			wp_schedule_event(time() + 60, 'hourly', self::CRON_HOOK);
		}

		if (!$enabled && $scheduled) {
			wp_unschedule_event($scheduled, self::CRON_HOOK);
		}
	}

	public static function clear_schedule() {
		$timestamp = wp_next_scheduled(self::CRON_HOOK);
		while ($timestamp) {
			wp_unschedule_event($timestamp, self::CRON_HOOK);
			$timestamp = wp_next_scheduled(self::CRON_HOOK);
		}
	}

	public function run_scheduled_preload() {
		if (!rest_sanitize_boolean(RP_Options::get_option('cache_preload_enabled', false))) {
			return;
		}

		$this->run_manual_preload();
	}

	public function run_manual_preload() {
		$urls = $this->collect_urls();
		$count = $this->preload_urls($urls);
		update_option(self::LAST_RUN_OPTION, time(), false);
		update_option(self::LAST_COUNT_OPTION, intval($count), false);

		return $count;
	}

	private function collect_urls() {
		$urls = array(home_url('/'));
		$urls[] = home_url('/feed/');

		$batch_size = intval(RP_Options::get_option('cache_preload_batch_size', 20));
		$batch_size = max(1, min(100, $batch_size));

		$posts = get_posts(array(
			'post_type' => array('post', 'page'),
			'post_status' => 'publish',
			'posts_per_page' => $batch_size,
			'orderby' => 'modified',
			'order' => 'DESC',
			'fields' => 'ids',
			'suppress_filters' => true,
		));

		if (is_array($posts)) {
			foreach ($posts as $post_id) {
				$url = get_permalink($post_id);
				if (is_string($url) && $url !== '') {
					$urls[] = $url;
				}
			}
		}

		return array_values(array_unique(array_filter($urls)));
	}

	private function preload_urls($urls) {
		if (!is_array($urls)) {
			return 0;
		}

		$count = 0;
		foreach ($urls as $url) {
			$response = wp_remote_get($url, array(
				'timeout' => 10,
				'redirection' => 3,
				'blocking' => true,
				'headers' => array(
					'X-RapidPress-Preload' => '1',
				),
			));

			if (!is_wp_error($response) && intval(wp_remote_retrieve_response_code($response)) < 400) {
				$count++;
			}
		}

		return $count;
	}
}
