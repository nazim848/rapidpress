<?php

namespace RapidPress;

class Core_Tweaks {

	public function __construct() {
		add_action('init', [$this, 'init']);
	}

	public function init() {
		if (RP_Options::get_option('disable_comments')) {
			$this->disable_comments();
		}
		if (RP_Options::get_option('remove_comment_urls')) {
			$this->remove_comment_urls();
		}
		if (RP_Options::get_option('disable_dashicons')) {
			$this->disable_dashicons();
		}
	}

	// Disable Comments
	private function disable_comments() {
		add_action('init', [$this, 'disable_comments_post_types_support']);
		add_action('admin_init', [$this, 'disable_comments_admin_actions']);
		add_action('admin_bar_menu', [$this, 'disable_comments_admin_bar'], 999);
		add_filter('comments_open', '__return_false', 20, 0);
		add_filter('pings_open', '__return_false', 20, 0);
		add_filter('comments_array', '__return_empty_array', 10, 0);
	}

	public function disable_comments_post_types_support() {
		$post_types = get_post_types();
		foreach ($post_types as $post_type) {
			if (post_type_supports($post_type, 'comments')) {
				remove_post_type_support($post_type, 'comments');
				remove_post_type_support($post_type, 'trackbacks');
			}
		}
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
}
