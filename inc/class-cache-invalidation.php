<?php

namespace RapidPress;

class Cache_Invalidation {
	public function get_urls_for_post($post_id) {
		$urls = array();
		$post_id = intval($post_id);
		if ($post_id <= 0) {
			return array(home_url('/'));
		}

		$permalink = get_permalink($post_id);
		if (is_string($permalink) && $permalink !== '') {
			$urls[] = $permalink;
		}

		$urls[] = home_url('/');
		$urls[] = home_url('/feed/');

		$post = get_post($post_id);
		if (!$post instanceof \WP_Post) {
			return $this->normalize_urls($urls);
		}

		$post_type_object = get_post_type_object($post->post_type);
		if ($post_type_object && !empty($post_type_object->has_archive)) {
			$archive = get_post_type_archive_link($post->post_type);
			if (is_string($archive) && $archive !== '') {
				$urls[] = $archive;
			}
		}

		$author_url = get_author_posts_url(intval($post->post_author));
		if (is_string($author_url) && $author_url !== '') {
			$urls[] = $author_url;
		}

		$taxonomies = get_object_taxonomies($post->post_type, 'names');
		if (is_array($taxonomies)) {
			foreach ($taxonomies as $taxonomy) {
				$terms = wp_get_post_terms($post_id, $taxonomy);
				if (is_wp_error($terms) || !is_array($terms)) {
					continue;
				}

				foreach ($terms as $term) {
					$link = get_term_link($term);
					if (!is_wp_error($link) && is_string($link) && $link !== '') {
						$urls[] = $link;
					}
				}
			}
		}

		return $this->normalize_urls($urls);
	}

	public function get_urls_for_comment($comment_id) {
		$comment = get_comment($comment_id);
		if (!$comment instanceof \WP_Comment) {
			return array(home_url('/'));
		}

		return $this->get_urls_for_post($comment->comment_post_ID);
	}

	private function normalize_urls($urls) {
		if (!is_array($urls)) {
			return array();
		}

		$urls = array_map('strval', $urls);
		$urls = array_filter($urls, function ($url) {
			return $url !== '';
		});
		$urls = array_unique($urls);

		return array_values($urls);
	}
}
