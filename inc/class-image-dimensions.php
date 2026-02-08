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
		add_filter('the_excerpt', array($this, 'add_missing_dimensions_to_content'), 20);
		add_filter('widget_text_content', array($this, 'add_missing_dimensions_to_content'), 20);
		add_filter('widget_custom_html_content', array($this, 'add_missing_dimensions_to_content'), 20);
		add_filter('widget_block_content', array($this, 'add_missing_dimensions_to_content'), 20);
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
		$attributes = trim($matches[1]);
		$is_self_closing = preg_match('/\/\s*$/', $attributes) === 1;
		if ($is_self_closing) {
			$attributes = preg_replace('/\/\s*$/', '', $attributes);
			$attributes = trim((string) $attributes);
		}

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
		$attributes = trim($attributes);

		if ($is_self_closing) {
			return '<img ' . $attributes . ' />';
		}

		return '<img ' . $attributes . '>';
	}

	private function extract_attachment_id($attributes) {
		if (preg_match('/\bwp-image-(\d+)\b/', $attributes, $matches)) {
			return intval($matches[1]);
		}

		if (preg_match('/\bdata-id\s*=\s*["\"]?(\d+)["\"]?/i', $attributes, $matches)) {
			return intval($matches[1]);
		}

		if (preg_match_all('/\b(?:data-src|src)\s*=\s*["\']([^"\']+)["\']/i', $attributes, $matches)) {
			if (!empty($matches[1]) && is_array($matches[1])) {
				foreach ($matches[1] as $image_url) {
					$attachment_id = $this->get_attachment_id_from_url($image_url);
					if ($attachment_id > 0) {
						return $attachment_id;
					}
				}
			}
		}

		return 0;
	}

	/**
	 * Resolve an attachment ID from an image URL.
	 *
	 * @param string $image_url Image URL.
	 * @return int
	 */
	private function get_attachment_id_from_url($image_url) {
		$image_url = trim(html_entity_decode((string) $image_url));
		if ($image_url === '' || strpos($image_url, 'data:') === 0) {
			return 0;
		}

		$attachment_id = attachment_url_to_postid($image_url);
		if ($attachment_id > 0) {
			return intval($attachment_id);
		}

		$parsed_image_url = wp_parse_url($image_url);
		$parsed_home_url = wp_parse_url(home_url());
		if (
			!is_array($parsed_image_url) ||
			empty($parsed_image_url['path']) ||
			!is_array($parsed_home_url) ||
			empty($parsed_home_url['scheme']) ||
			empty($parsed_home_url['host'])
		) {
			return 0;
		}

		$normalized_url = $parsed_home_url['scheme'] . '://' . $parsed_home_url['host'];
		if (!empty($parsed_home_url['port'])) {
			$normalized_url .= ':' . intval($parsed_home_url['port']);
		}
		$normalized_url .= $parsed_image_url['path'];

		$attachment_id = attachment_url_to_postid($normalized_url);
		return $attachment_id > 0 ? intval($attachment_id) : 0;
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
