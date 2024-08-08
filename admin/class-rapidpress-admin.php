<?php

class RapidPress_Admin {
	private $plugin_name;
	private $version;

	public function __construct($plugin_name, $version) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;


		add_action('admin_notices', array($this, 'activation_notice'));
		add_action('admin_menu', array($this, 'add_plugin_admin_menu'));
		add_action('admin_init', array($this, 'register_settings'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
		add_action('wp_ajax_save_rapidpress_settings', array($this, 'save_settings_ajax'));
	}

	public function enqueue_styles($hook) {
		if ('toplevel_page_rapidpress' !== $hook) {
			return;
		}
		wp_enqueue_style('rapidpress-admin', plugin_dir_url(__FILE__) . 'css/rapidpress-admin.css', array(), $this->version, 'all');
	}

	public function enqueue_scripts($hook) {
		if ('toplevel_page_rapidpress' !== $hook) {
			return;
		}
		wp_enqueue_script('rapidpress-admin', plugin_dir_url(__FILE__) . 'js/rapidpress-admin.js', array('jquery'), $this->version, false);
		wp_localize_script('rapidpress-admin', 'rapidpress_admin', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('rapidpress_admin_nonce')
		));
	}

	public function save_settings_ajax() {
		check_ajax_referer('rapidpress_admin_nonce', 'nonce');

		if (!current_user_can('manage_options')) {
			wp_send_json_error('Insufficient permissions');
		}

		parse_str($_POST['form_data'], $form_data);
		$tab = sanitize_key($_POST['tab']);

		// Process and save the settings
		$updated = $this->process_settings($form_data, $tab);

		if ($updated) {
			wp_send_json_success();
		} else {
			wp_send_json_error();
		}
	}

	private function process_settings($form_data, $tab) {
		$updated = false;

		// Process settings based on the tab
		switch ($tab) {
			case 'file-optimization':
				$options = array(
					'rapidpress_html_minify',
					'rapidpress_css_minify',
					'rapidpress_combine_css',
					'rapidpress_css_exclusions',
					'rapidpress_js_minify',
					'rapidpress_js_defer',
					'rapidpress_js_defer_exclusions',
					'rapidpress_js_delay',
					'rapidpress_js_delay_duration',
					'rapidpress_js_delay_exclusions'
				);
				break;
				// Add cases for other tabs here
			default:
				$options = array();
		}

		foreach ($options as $option) {
			if (isset($form_data[$option])) {
				update_option($option, $form_data[$option]);
				$updated = true;
			} else {
				delete_option($option);
				$updated = true;
			}
		}

		return $updated;
	}

	public function activation_notice() {
		if (get_transient('rapidpress_activation_notice')) {
?>
			<div class="updated notice is-dismissible">
				<p><?php _e('Thank you for installing RapidPress! Please visit the ', 'rapidpress'); ?>
					<a href="<?php echo admin_url('admin.php?page=rapidpress'); ?>"><?php _e('settings page', 'rapidpress'); ?></a>
					<?php _e('to configure the plugin.', 'rapidpress'); ?>
				</p>
			</div>
<?php
			delete_transient('rapidpress_activation_notice');
		}
	}

	public function add_plugin_admin_menu() {
		add_menu_page(
			'RapidPress Settings',
			'RapidPress',
			'manage_options',
			'rapidpress',
			array($this, 'display_plugin_setup_page'),
			'dashicons-performance',
			80
		);
	}

	public function display_plugin_setup_page() {
		$active_tab = '#dashboard'; // Default to dashboard

		if (isset($_GET['tab'])) {
			$active_tab = '#' . sanitize_text_field($_GET['tab']);
		}

		include_once 'partials/rapidpress-admin-display.php';

		echo "<script>jQuery(document).ready(function($) { setActiveTab('$active_tab'); });</script>";
	}

	public function register_settings() {
		$settings = array(
			'rapidpress_html_minify' => 'boolean',
			'rapidpress_css_minify' => 'boolean',
			'rapidpress_combine_css' => 'boolean',
			'rapidpress_js_minify' => 'boolean',
			'rapidpress_js_defer' => 'boolean',
			'rapidpress_css_exclusions' => array(
				'sanitize_callback' => array($this, 'sanitize_css_combine_exclusions'),
			),
			'rapidpress_js_defer_exclusions' => array(
				'sanitize_callback' => array($this, 'sanitize_js_defer_exclusions'),
			),
			'rapidpress_js_delay' => 'boolean',
			'rapidpress_js_delay_duration' => array(
				'type' => 'string',
				'sanitize_callback' => array($this, 'sanitize_js_delay_duration'),
			),
			'rapidpress_js_delay_exclusions' => array(
				'sanitize_callback' => array($this, 'sanitize_js_delay_exclusions'),
			),

			// Add new settings here
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

	public function sanitize_css_combine_exclusions($input) {
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
