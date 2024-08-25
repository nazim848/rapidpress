<?php

namespace RapidPress;

class Core_Tweaks {

	public function __construct() {
		add_action('init', [$this, 'initialize_tweaks']);
	}

	public function initialize_tweaks() {
		if (RP_Options::get_option('disable_comments')) {
			$this->disable_comments();
		}
		if (RP_Options::get_option('remove_comment_urls')) {
			$this->remove_comment_urls();
		}
		if (RP_Options::get_option('disable_dashicons')) {
			$this->disable_dashicons();
		}
		if (RP_Options::get_option('disable_embeds')) {
			$this->disable_embeds();
		}
		if (RP_Options::get_option('disable_xmlrpc')) {
			$this->disable_xmlrpc();
		}
		if (RP_Options::get_option('disable_emojis')) {
			$this->disable_emojis();
		}
		if (RP_Options::get_option('remove_jquery_migrate')) {
			$this->remove_jquery_migrate();
		}
		if (RP_Options::get_option('disable_rss_feeds')) {
			$this->disable_rss_feeds();
		}
		if (RP_Options::get_option('remove_rsd_link')) {
			$this->remove_rsd_link();
		}
		if (RP_Options::get_option('hide_wp_version')) {
			$this->hide_wp_version();
		}
	}

	// Disable Comments
	private function disable_comments() {
		add_action('admin_init', [$this, 'disable_comments_admin_actions']);
		add_action('admin_bar_menu', [$this, 'disable_comments_admin_bar'], 999);
		add_filter('comments_open', '__return_false', 20, 0);
		add_filter('pings_open', '__return_false', 20, 0);
		add_filter('comments_array', '__return_empty_array', 10, 0);
		add_action('init', [$this, 'disable_comments_post_types_support'], 100);
	}

	public function disable_comments_admin_actions() {
		// Remove comments menu and redirect
		remove_menu_page('edit-comments.php');
		remove_submenu_page('options-general.php', 'options-discussion.php');

		global $pagenow;
		if ($pagenow === 'edit-comments.php' || $pagenow === 'options-discussion.php') {
			wp_safe_redirect(admin_url());
			exit;
		}

		// Remove dashboard widgets
		remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
		remove_action('dashboard_activity_widget_content', 'wp_dashboard_recent_comments');
	}

	public function disable_comments_admin_bar($wp_admin_bar) {
		$wp_admin_bar->remove_node('comments');
	}

	public function disable_comments_post_types_support() {
		$post_types = get_post_types();
		if (is_array($post_types)) {
			foreach ($post_types as $post_type) {
				if (post_type_supports($post_type, 'comments')) {
					remove_post_type_support($post_type, 'comments');
					remove_post_type_support($post_type, 'trackbacks');
				}
			}
		}
	}

	// Remove Comment URLs
	private function remove_comment_urls() {
		add_filter('get_comment_author_link', [$this, 'remove_comment_author_link'], 10, 3);
		add_filter('get_comment_author_url', '__return_false');
		add_filter('comment_form_default_fields', [$this, 'remove_website_field'], 9999);
	}

	public function remove_comment_author_link($return, $author, $comment_ID) {
		return wp_kses_post($author);
	}

	public function remove_website_field($fields) {
		unset($fields['url']);
		return $fields;
	}

	// Disable Dashicons
	private function disable_dashicons() {
		add_action('wp_enqueue_scripts', [$this, 'disable_dashicons_style'], 10);
	}

	public function disable_dashicons_style() {
		if (!is_user_logged_in()) {
			wp_dequeue_style('dashicons');
			wp_deregister_style('dashicons');
		}
	}

	// Disable Embeds
	private function disable_embeds() {
		global $wp;
		$wp->public_query_vars = array_diff($wp->public_query_vars, array('embed'));
		add_filter('tiny_mce_plugins', [$this, 'disable_embeds_tiny_mce_plugin']);
		add_filter('rewrite_rules_array', [$this, 'disable_embeds_rewrites']);
		add_filter('embed_oembed_discover', '__return_false');
		remove_filter('oembed_dataparse', 'wp_filter_oembed_result', 10);
		remove_filter('pre_oembed_result', 'wp_filter_pre_oembed_result', 10);
		remove_action('wp_head', 'wp_oembed_add_discovery_links');
		remove_action('wp_head', 'wp_oembed_add_host_js');
	}

	public function disable_embeds_tiny_mce_plugin($plugins) {
		return array_diff($plugins, array('wpembed'));
	}

	public function disable_embeds_rewrites($rules) {
		foreach ($rules as $rule => $rewrite) {
			if (is_string($rewrite) && false !== strpos($rewrite, 'embed=true')) {
				unset($rules[$rule]);
			}
		}
		return $rules;
	}

	// Disable XML-RPC
	private function disable_xmlrpc() {
		add_filter('xmlrpc_enabled', '__return_false');
		add_filter('pings_open', '__return_false', 9999);
		add_filter('pre_update_option_enable_xmlrpc', '__return_false');
		add_filter('pre_option_enable_xmlrpc', '__return_zero');
		add_filter('wp_headers', [$this, 'remove_x_pingback']);
		add_filter('bloginfo_url', [$this, 'remove_pingback_url'], 10, 2);
		add_action('wp_head', [$this, 'remove_pingback_link']);
		add_action('init', [$this, 'block_xmlrpc_requests'], 100);
	}

	public function remove_x_pingback($headers) {
		unset($headers['X-Pingback'], $headers['x-pingback']);
		return $headers;
	}

	public function remove_pingback_url($output, $show) {
		if ($show === 'pingback_url') {
			return '';
		}
		return $output;
	}

	public function remove_pingback_link() {
		remove_action('wp_head', 'pingback_link');
	}

	public function block_xmlrpc_requests() {
		// Check if the current request is for xmlrpc.php
		if (strpos($_SERVER['REQUEST_URI'], 'xmlrpc.php') !== false) {
			// Set headers
			header('HTTP/1.1 403 Forbidden');
			header('Status: 403 Forbidden');
			header('Connection: Close');

			// Output message
			echo 'XML-RPC services are disabled on this site.';
			exit;
		}
	}

	// Disable Emojis
	private function disable_emojis() {
		remove_action('wp_head', 'print_emoji_detection_script', 7);
		remove_action('admin_print_scripts', 'print_emoji_detection_script');
		remove_action('admin_print_styles', 'print_emoji_styles');
		remove_action('wp_print_styles', 'print_emoji_styles');
		remove_filter('comment_text_rss', 'wp_staticize_emoji');
		remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
		remove_filter('the_content_feed', 'wp_staticize_emoji');
		add_filter('tiny_mce_plugins', [$this, 'disable_emojis_tinymce']);
		add_filter('wp_resource_hints', [$this, 'disable_emojis_dns_prefetch'], 10, 2);
		add_filter('emoji_svg_url', '__return_false');
	}

	public function disable_emojis_tinymce($plugins) {
		if (is_array($plugins)) {
			return array_diff($plugins, ['wpemoji']);
		}
		return [];
	}

	public function disable_emojis_dns_prefetch($urls, $relation_type) {
		if ('dns-prefetch' === $relation_type) {
			$emoji_svg_url = apply_filters('emoji_svg_url', 'https://s.w.org/images/core/emoji/11/svg/');
			$urls = array_diff($urls, [$emoji_svg_url]);
		}
		return $urls;
	}

	// Remove jQuery Migrate
	private function remove_jquery_migrate() {
		add_action('wp_default_scripts', [$this, 'remove_jquery_migrate_script']);
	}

	public function remove_jquery_migrate_script($scripts) {
		if (!is_admin() && isset($scripts->registered['jquery'])) {
			$script = $scripts->registered['jquery'];
			if ($script->deps) {
				$script->deps = array_diff($script->deps, ['jquery-migrate']);
			}
		}
	}

	// Disable RSS Feeds
	private function disable_rss_feeds() {
		remove_action('wp_head', 'feed_links', 2);
		remove_action('wp_head', 'feed_links_extra', 3);
		add_action('do_feed', [$this, 'disable_rss_feed'], 1);
		add_action('do_feed_rdf', [$this, 'disable_rss_feed'], 1);
		add_action('do_feed_rss', [$this, 'disable_rss_feed'], 1);
		add_action('do_feed_rss2', [$this, 'disable_rss_feed'], 1);
		add_action('do_feed_atom', [$this, 'disable_rss_feed'], 1);
		add_action('do_feed_rss2_comments', [$this, 'disable_rss_feed'], 1);
		add_action('do_feed_atom_comments', [$this, 'disable_rss_feed'], 1);
		remove_action('wp_head', 'wlwmanifest_link');
		add_filter('feed_links_show_comments_feed', '__return_false');
	}

	public function disable_rss_feed() {
		wp_die(
			__('RSS feeds are disabled.', 'rapidpress'),
			'',
			['response' => 403]
		);
	}

	// Remove RSD Link
	public function remove_rsd_link() {
		remove_action('wp_head', 'rsd_link');
	}

	// Hide WP Version
	private function hide_wp_version() {
		remove_action('wp_head', 'wp_generator');
		add_filter('the_generator', '__return_false');
	}
}
