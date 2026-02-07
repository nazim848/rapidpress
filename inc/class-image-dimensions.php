<?php

namespace RapidPress;

if (!defined('ABSPATH')) {
	exit;
}

class Image_Dimensions {
	public function __construct() {
		if (is_admin()) {
			return;
		}

		add_filter('wp_get_attachment_image_attributes', array($this, 'add_missing_dimensions_to_attributes'), 10, 3);
		add_filter('the_content', array($this, 'add_missing_dimensions_to_content'), 20);
	}

	public function add_missing_dimensions_to_attributes($attr, $attachment, $size) {
		if (!RP_Options::get_option('add_missing_dimensions')) {
			return $attr;
		}

		if (!\RapidPress\Optimization_Scope::should_optimize()) {
			return $attr;
		}

		if (!empty($attr['width']) && !empty($attr['height'])) {
			return $attr;
		}

		$dimensions = $this->get_attachment_dimensions($attachment->ID, $size);
		if ($dimensions) {
			$attr['width'] = $dimensions['width'];
			$attr['height'] = $dimensions['height'];
		}

		return $attr;
	}

	public function add_missing_dimensions_to_content($content) {
		if (!RP_Options::get_option('add_missing_dimensions')) {
			return $content;
		}

		if (!\RapidPress\Optimization_Scope::should_optimize()) {
			return $content;
		}

		if (false === strpos($content, '<img')) {
			return $content;
		}

		return preg_replace_callback('/<img\s+([^>]+)>/i', array($this, 'process_img_tag'), $content);
	}

	private function process_img_tag($matches) {
		$attributes = $matches[1];

		if (preg_match('/\bwidth\s*=\s*["\"][^"\"]+["\"]/i', $attributes) &&
			preg_match('/\bheight\s*=\s*["\"][^"\"]+["\"]/i', $attributes)) {
			return $matches[0];
		}

		$attachment_id = $this->extract_attachment_id($attributes);
		if (!$attachment_id) {
			return $matches[0];
		}

		$dimensions = $this->get_attachment_dimensions($attachment_id, 'full');
		if (!$dimensions) {
			return $matches[0];
		}

		$attributes .= ' width="' . intval($dimensions['width']) . '" height="' . intval($dimensions['height']) . '"';

		return '<img ' . $attributes . '>';
	}

	private function extract_attachment_id($attributes) {
		if (preg_match('/\bwp-image-(\d+)\b/', $attributes, $matches)) {
			return intval($matches[1]);
		}

		if (preg_match('/\bdata-id\s*=\s*["\"]?(\d+)["\"]?/i', $attributes, $matches)) {
			return intval($matches[1]);
		}

		return 0;
	}

	private function get_attachment_dimensions($attachment_id, $size) {
		$metadata = wp_get_attachment_metadata($attachment_id);
		if (empty($metadata)) {
			return null;
		}

		if (is_array($size) && count($size) === 2) {
			return array('width' => intval($size[0]), 'height' => intval($size[1]));
		}

		if (is_string($size) && isset($metadata['sizes'][$size])) {
			return array(
				'width' => intval($metadata['sizes'][$size]['width']),
				'height' => intval($metadata['sizes'][$size]['height'])
			);
		}

		if (isset($metadata['width'], $metadata['height'])) {
			return array('width' => intval($metadata['width']), 'height' => intval($metadata['height']));
		}

		return null;
	}
}
