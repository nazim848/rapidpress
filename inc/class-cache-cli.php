<?php

namespace RapidPress;

class Cache_CLI {
	public function __construct() {
		if (!defined('WP_CLI') || !WP_CLI) {
			return;
		}

		\WP_CLI::add_command('rapidpress cache purge', array($this, 'purge_all'));
		\WP_CLI::add_command('rapidpress cache purge-url', array($this, 'purge_url'));
		\WP_CLI::add_command('rapidpress cache preload', array($this, 'preload'));
		\WP_CLI::add_command('rapidpress cache stats', array($this, 'stats'));
	}

	public function purge_all($args, $assoc_args) {
		$store = new Cache_Store();
		$store->purge_all_html();
		\WP_CLI::success('Purged all RapidPress page cache files.');
	}

	public function purge_url($args, $assoc_args) {
		if (empty($args[0])) {
			\WP_CLI::error('Usage: wp rapidpress cache purge-url <url>');
			return;
		}

		$url = $args[0];
		$key_builder = new Cache_Key();
		$store = new Cache_Store();
		$config = new Cache_Config();

		$base_key = $key_builder->from_url($url);
		if ($base_key === '') {
			\WP_CLI::error('Could not build cache key from URL.');
			return;
		}

		$store->delete_by_key($base_key);
		if ($config->use_mobile_variant()) {
			$store->delete_by_key($base_key . '|mobile');
			$store->delete_by_key($base_key . '|desktop');
		}

		\WP_CLI::success('Purged cache for URL: ' . $url);
	}

	public function preload($args, $assoc_args) {
		$preloader = new Cache_Preloader();
		$count = $preloader->run_manual_preload();
		\WP_CLI::success('Preloaded ' . intval($count) . ' URLs.');
	}

	public function stats($args, $assoc_args) {
		$stats = (new Cache_Stats())->get_summary();
		\WP_CLI::line('Cache directory: ' . $stats['cache_dir']);
		\WP_CLI::line('Cache files: ' . $stats['file_count']);
		\WP_CLI::line('Cache size: ' . $stats['total_size_human'] . ' (' . $stats['total_size_bytes'] . ' bytes)');
	}
}
