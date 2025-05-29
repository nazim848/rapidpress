<?php

/**
 * Tab Manager Class
 * Handles tab registration, rendering, and validation
 */

if (!defined('ABSPATH')) exit;

class RapidPress_Tab_Manager {

	private $tabs = array();
	private $active_tab = 'general';

	/**
	 * Initialize tab manager
	 */
	public function __construct() {
		$this->register_default_tabs();
		$this->set_active_tab();
	}

	/**
	 * Register default tabs
	 */
	private function register_default_tabs() {
		$this->register_tab('general', __('General', 'rapidpress'), 1);
		$this->register_tab('file-optimization', __('File Optimization', 'rapidpress'), 2);
		$this->register_tab('asset-manager', __('Asset Manager', 'rapidpress'), 3);
		$this->register_tab('media', __('Media', 'rapidpress'), 4, false); // disabled
		$this->register_tab('cache', __('Cache', 'rapidpress'), 5, false);
		$this->register_tab('preloading', __('Preloading', 'rapidpress'), 6, false);
		$this->register_tab('database', __('Database', 'rapidpress'), 7, false);
		$this->register_tab('cdn', __('CDN', 'rapidpress'), 8, false);
		$this->register_tab('settings', __('Settings', 'rapidpress'), 99);
	}

	/**
	 * Register a new tab
	 */
	public function register_tab($id, $title, $order = 10, $enabled = true, $capability = 'manage_options') {
		$this->tabs[$id] = array(
			'id' => $id,
			'title' => $title,
			'order' => $order,
			'enabled' => $enabled,
			'capability' => $capability,
			'file' => plugin_dir_path(__FILE__) . '../tabs/' . $id . '.php'
		);
	}

	/**
	 * Get all enabled tabs sorted by order
	 */
	public function get_tabs() {
		$enabled_tabs = array_filter($this->tabs, function ($tab) {
			return $tab['enabled'] && current_user_can($tab['capability']);
		});

		uasort($enabled_tabs, function ($a, $b) {
			return $a['order'] - $b['order'];
		});

		return $enabled_tabs;
	}

	/**
	 * Set active tab from request
	 */
	private function set_active_tab() {
		$requested_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general';
		$available_tabs = array_keys($this->get_tabs());

		$this->active_tab = in_array($requested_tab, $available_tabs) ? $requested_tab : 'general';
	}

	/**
	 * Get active tab
	 */
	public function get_active_tab() {
		return $this->active_tab;
	}

	/**
	 * Render tab navigation
	 */
	public function render_tab_navigation() {
		echo '<h2 class="nav-tab-wrapper">';
		foreach ($this->get_tabs() as $tab) {
			$class = ($tab['id'] === $this->active_tab) ? ' nav-tab-active' : '';
			printf(
				'<a href="#%s" class="nav-tab%s" data-tab="%s">%s</a>',
				esc_attr($tab['id']),
				esc_attr($class),
				esc_attr($tab['id']),
				esc_html($tab['title'])
			);
		}
		echo '</h2>';
	}

	/**
	 * Render tab content
	 */
	public function render_tab_content() {
		echo '<div class="tab-content-wrapper">';

		foreach ($this->get_tabs() as $tab) {
			$style = ($tab['id'] === $this->active_tab) ? '' : 'style="display:none;"';

			printf('<div id="%s" class="tab-panel" %s>', esc_attr($tab['id']), $style);

			if (file_exists($tab['file'])) {
				include $tab['file'];
			} else {
				printf(
					'<div class="notice notice-error"><p>%s</p></div>',
					sprintf(
						esc_html__('Tab content file not found: %s', 'rapidpress'),
						esc_html($tab['file'])
					)
				);
			}

			echo '</div>';
		}

		echo '</div>';
	}
}
