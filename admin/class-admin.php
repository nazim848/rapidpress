<?php

namespace RapidPress;

class Admin {
	private $plugin_name;
	private $version;

	public function __construct($plugin_name, $version) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;

		add_action('admin_notices', array($this, 'activation_notice'));
		add_action('admin_menu', array($this, 'add_plugin_settings_menu'));
		add_action('admin_init', array($this, 'register_settings'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
		add_action('admin_bar_menu', array($this, 'add_toolbar_items'), 100);
		add_filter('plugin_action_links_' . plugin_basename(RAPIDPRESS_PLUGIN_FILE), array($this, 'add_action_links'));
	}

	public function enqueue_styles($hook) {
		if ('settings_page_rapidpress' !== $hook) {
			return;
		}
		wp_enqueue_style('rapidpress-admin', plugin_dir_url(__FILE__) . 'css/rapidpress-admin.css', array(), $this->version, 'all');
	}

	public function enqueue_scripts($hook) {
		if ('settings_page_rapidpress' !== $hook) {
			return;
		}
		wp_enqueue_script('rapidpress-admin', plugin_dir_url(__FILE__) . 'js/rapidpress-admin.js', array('jquery'), $this->version, false);
		wp_localize_script('rapidpress-admin', 'rapidpress_admin', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('rapidpress_admin_nonce')
		));
	}

	public function activation_notice() {
		if (get_transient('rapidpress_activation_notice')) {
?>
			<div class="updated notice is-dismissible">
				<p><?php _e('Thank you for installing RapidPress! Please visit the ', 'rapidpress'); ?>
					<a href="<?php echo admin_url('options-general.php?page=rapidpress'); ?>"><?php _e('settings page', 'rapidpress'); ?></a>
					<?php _e('to configure the plugin.', 'rapidpress'); ?>
				</p>
			</div>
<?php
			delete_transient('rapidpress_activation_notice');
		}
	}

	public function add_action_links($links) {
		$settings_link = '<a href="' . admin_url('options-general.php?page=rapidpress') . '">' . __('Settings', 'rapidpress') . '</a>';
		array_unshift($links, $settings_link);
		return $links;
	}

	// Add the main RapidPress menu item to the settings menu
	public function add_plugin_settings_menu() {
		// Add RapidPress to the Settings menu
		add_options_page(
			'RapidPress',
			'RapidPress',
			'manage_options',
			'rapidpress',
			array($this, 'display_plugin_setup_page')
		);
	}

	// Add the main RapidPress menu item to the toolbar
	public function add_toolbar_items($admin_bar) {
		// Add the main RapidPress menu item
		$admin_bar->add_menu(array(
			'id'    => 'rapidpress',
			'title' => 'RapidPress',
			'href'  => admin_url('options-general.php?page=rapidpress'),
			'meta'  => array(
				'title' => __('RapidPress Settings', 'rapidpress'),
			),
		));

		// Add submenu items
		$submenu_items = array(
			'dashboard' 			=> __('Dashboard', 'rapidpress'),
			'file-optimization' 	=> __('File Optimization', 'rapidpress'),
			'asset-management' => __('Asset Management', 'rapidpress'),
		);

		foreach ($submenu_items as $slug => $title) {
			$admin_bar->add_menu(array(
				'id'     => 'rapidpress-' . $slug,
				'parent' => 'rapidpress',
				'title'  => $title,
				'href'   => admin_url('options-general.php?page=rapidpress&tab=' . $slug),
				'meta'   => array(
					'title' => $title,
					'class' => 'rapidpress-toolbar-item'
				),
			));
		}
	}

	public function display_plugin_setup_page() {
		$active_tab = '#dashboard'; // Default to dashboard

		if (isset($_GET['tab'])) {
			$active_tab = '#' . sanitize_text_field($_GET['tab']);
		}

		include_once 'partials/admin-display.php';

		echo "<script>jQuery(document).ready(function($) { setActiveTab('$active_tab'); });</script>";
	}

	public function register_settings() {
		$settings = array(
			'rapidpress_optimization_scope' => array(
				'type' => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'rapidpress_optimized_pages' => array(
				'type' => 'string',
				'sanitize_callback' => array($this, 'sanitize_optimized_pages'),
			),
			'rapidpress_enable_scope_exclusions' => 'boolean',
			'rapidpress_optimization_excluded_pages' => array(
				'type' => 'string',
				'sanitize_callback' => array($this, 'sanitize_optimization_excluded_pages'),
			),
			'rapidpress_html_minify' => 'boolean',
			'rapidpress_css_minify' => 'boolean',
			'rapidpress_combine_css' => 'boolean',
			'rapidpress_enable_combine_css_exclusions' => 'boolean',
			'rapidpress_combine_css_exclusions' => array(
				'sanitize_callback' => array($this, 'sanitize_combine_css_exclusions'),
			),
			'rapidpress_js_minify' => 'boolean',
			'rapidpress_js_defer' => 'boolean',
			'rapidpress_enable_js_defer_exclusions' => 'boolean',
			'rapidpress_js_defer_exclusions' => array(
				'sanitize_callback' => array($this, 'sanitize_js_defer_exclusions'),
			),
			'rapidpress_js_delay' => 'boolean',
			'rapidpress_js_delay_type' => array(
				'type' => 'string',
				'sanitize_callback' => array($this, 'sanitize_js_delay_type'),
			),
			'rapidpress_js_delay_duration' => array(
				'type' => 'string',
				'sanitize_callback' => array($this, 'sanitize_js_delay_duration'),
			),
			'rapidpress_js_delay_specific_files' => array(
				'type' => 'string',
				'sanitize_callback' => array($this, 'sanitize_js_delay_specific_files'),
			),
			'rapidpress_enable_js_delay_exclusions' => 'boolean',
			'rapidpress_js_delay_exclusions' => array(
				'sanitize_callback' => array($this, 'sanitize_js_delay_exclusions'),
			),
			'rapidpress_js_disable_rules' => array(
				'type' => 'array',
				'sanitize_callback' => array($this, 'sanitize_js_disable_rules'),
			),
			'rapidpress_css_disable_rules' => array(
				'type' => 'array',
				'sanitize_callback' => array($this, 'sanitize_css_disable_rules'),
			),

		);

		foreach ($settings as $setting => $options) {
			$args = is_array($options) ? $options : array();

			if ($options === 'boolean') {
				$args['type'] = 'boolean';
				$args['sanitize_callback'] = 'rest_sanitize_boolean';
			}

			register_setting('rapidpress_options', $setting, $args);
			add_filter("pre_update_option_{$setting}", array($this, 'save_settings_with_tab'), 10, 3);
		}
	}

	public function sanitize_css_disable_rules($input) {
		$sanitized_rules = array();
		if (is_array($input)) {
			foreach ($input as $rule) {
				if (!empty($rule['styles'])) {
					$sanitized_rule = array(
						'styles' => $this->sanitize_scripts_or_styles($rule['styles']),
						'scope' => sanitize_text_field($rule['scope']),
						'exclude_enabled' => isset($rule['exclude_enabled']) ? '1' : '0',
						'exclude_pages' => $this->sanitize_pages($rule['exclude_pages']),
						'pages' => $this->sanitize_pages($rule['pages']),
						'is_active' => isset($rule['is_active']) ? '1' : '0',
					);
					if (!empty($sanitized_rule['styles'])) {
						$sanitized_rules[] = $sanitized_rule;
					}
				}
			}
		}
		return $sanitized_rules;
	}

	private function sanitize_scripts_or_styles($input) {
		if (is_array($input)) {
			return array_filter(array_map('trim', $input));
		} elseif (is_string($input)) {
			return array_filter(array_map('trim', explode("\n", sanitize_textarea_field($input))));
		}
		return array();
	}

	private function sanitize_pages($input) {
		if (is_array($input)) {
			return array_filter(array_map('trailingslashit', array_map('esc_url_raw', $input)));
		} elseif (is_string($input)) {
			return array_filter(array_map('trailingslashit', array_map('esc_url_raw', explode("\n", sanitize_textarea_field($input)))));
		}
		return array();
	}

	public function sanitize_optimization_excluded_pages($input) {
		$pages = explode("\n", $input);
		$sanitized_pages = array();
		foreach ($pages as $page) {
			$sanitized_pages[] = esc_url_raw(trim($page));
		}
		return implode("\n", array_filter($sanitized_pages));
	}

	public function sanitize_optimized_pages($input) {
		$pages = explode("\n", $input);
		$sanitized_pages = array();
		foreach ($pages as $page) {
			$sanitized_pages[] = esc_url_raw(trim($page));
		}
		return implode("\n", array_filter($sanitized_pages));
	}


	public function sanitize_combine_css_exclusions($input) {
		$sanitized = array();
		$lines = explode("\n", $input);
		foreach ($lines as $line) {
			$sanitized[] = esc_url_raw(trim($line));
		}
		return implode("\n", array_filter($sanitized));
	}

	public function sanitize_js_defer_exclusions($input) {
		if (!is_string($input)) {
			return '';
		}
		$exclusions = explode("\n", $input);
		$sanitized = array();
		foreach ($exclusions as $exclusion) {
			$sanitized[] = esc_url_raw(trim($exclusion));
		}
		return implode("\n", array_filter($sanitized));
	}

	public function sanitize_js_delay_duration($input) {
		$valid_options = array('1', '2', 'interaction');
		return in_array($input, $valid_options) ? $input : '1';
	}

	public function sanitize_js_delay_exclusions($input) {
		if (!is_string($input)) {
			return '';
		}
		$exclusions = explode("\n", $input);
		$sanitized = array();
		foreach ($exclusions as $exclusion) {
			$sanitized[] = esc_url_raw(trim($exclusion));
		}
		return implode("\n", array_filter($sanitized));
	}

	public function sanitize_js_delay_type($input) {
		$valid_options = array('all', 'specific');
		return in_array($input, $valid_options) ? $input : 'all';
	}

	public function sanitize_js_delay_specific_files($input) {
		if (!is_string($input)) {
			return '';
		}
		$files = explode("\n", $input);
		$sanitized = array();
		foreach ($files as $file) {
			$sanitized[] = esc_url_raw(trim($file));
		}
		return implode("\n", array_filter($sanitized));
	}

	public function sanitize_js_disable_rules($input) {
		$sanitized_rules = array();
		if (is_array($input)) {
			foreach ($input as $rule) {
				if (!empty($rule['scripts'])) {
					$sanitized_rule = array(
						'scripts' => is_array($rule['scripts'])
							? array_filter(array_map('trim', $rule['scripts']))
							: array_filter(array_map('trim', explode("\n", sanitize_textarea_field($rule['scripts'])))),
						'scope' => sanitize_text_field($rule['scope']),
						'exclude_enabled' => isset($rule['exclude_enabled']) ? '1' : '0',
						'exclude_pages' => isset($rule['exclude_pages']) ? sanitize_textarea_field($rule['exclude_pages']) : '',
						'pages' => array(),
						'is_active' => isset($rule['is_active']) ? '1' : '0',
					);
					if ($sanitized_rule['scope'] === 'specific_pages' && !empty($rule['pages'])) {
						$sanitized_rule['pages'] = is_array($rule['pages'])
							? array_filter(array_map('trailingslashit', array_map('esc_url_raw', $rule['pages'])))
							: array_filter(array_map('trailingslashit', array_map('esc_url_raw', explode("\n", sanitize_textarea_field($rule['pages'])))));
					}
					if (!empty($sanitized_rule['scripts'])) {
						$sanitized_rules[] = $sanitized_rule;
					}
				}
			}
		}
		return $sanitized_rules;
	}

	public function save_settings_with_tab($value, $old_value, $option) {
		// Clear the CSS cache after saving settings
		$this->clear_css_cache();

		if (isset($_POST['rapidpress_active_tab']) && isset($_POST['rapidpress_nonce']) && wp_verify_nonce($_POST['rapidpress_nonce'], 'rapidpress_settings')) {
			$tab = sanitize_key(ltrim($_POST['rapidpress_active_tab'], '#'));
			add_filter('wp_redirect', function ($location) use ($tab) {
				return add_query_arg('tab', $tab, $location);
			});
		}

		return $value;
	}

	public function clear_css_cache() {
		$upload_dir = wp_upload_dir();
		$combined_dir = trailingslashit($upload_dir['basedir']) . 'rapidpress-combined';

		if (is_dir($combined_dir)) {
			array_map('unlink', glob("$combined_dir/*.*"));
		}

		delete_option('rapidpress_css_cache_meta');
	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	public function get_version() {
		return $this->version;
	}
}
