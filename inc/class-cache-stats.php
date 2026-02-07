<?php

namespace RapidPress;

class Cache_Stats {
	private $cache_store;

	public function __construct($cache_store = null) {
		$this->cache_store = $cache_store instanceof Cache_Store ? $cache_store : new Cache_Store();
	}

	public function get_summary() {
		$cache_dir = $this->cache_store->get_cache_dir();
		if ($cache_dir === '' || !is_dir($cache_dir)) {
			return array(
				'cache_dir' => $cache_dir,
				'file_count' => 0,
				'total_size_bytes' => 0,
				'total_size_human' => size_format(0),
			);
		}

		$files = glob(trailingslashit($cache_dir) . '*.html');
		if (!is_array($files)) {
			$files = array();
		}

		$total_size = 0;
		foreach ($files as $file) {
			$size = filesize($file);
			if (is_int($size) || is_float($size)) {
				$total_size += $size;
			}
		}

		return array(
			'cache_dir' => $cache_dir,
			'file_count' => count($files),
			'total_size_bytes' => intval($total_size),
			'total_size_human' => size_format($total_size),
		);
	}
}
