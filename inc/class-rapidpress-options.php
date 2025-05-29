<?php

namespace RapidPress;

class RP_Options {
	private static $option_name = 'rapidpress_options';

	public static function get_options() {
		return get_option(self::$option_name, array());
	}

	public static function get_option($key, $default = false) {
		$options = self::get_options();
		return isset($options[$key]) ? $options[$key] : $default;
	}

	public static function update_option($key, $value) {
		$options = self::get_options();
		$options[$key] = $value;
		return update_option(self::$option_name, $options);
	}

	public static function delete_option($key) {
		$options = self::get_options();
		if (isset($options[$key])) {
			unset($options[$key]);
			return update_option(self::$option_name, $options);
		}
		return false;
	}
}
