<?php

namespace RapidPress;

class Admin {
	private $plugin_name;
	private $version;

	public function __construct($plugin_name, $version) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;

		add_action('wp_ajax_rapidpress_save_settings', array($this, 'ajax_save_settings'));

		add_action('admin_notices', array($this, 'activation_notice'));
		add_action('admin_menu', array($this, 'add_plugin_settings_menu'));
		add_action('admin_init', array($this, 'register_settings'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
		add_action('admin_bar_menu', array($this, 'add_toolbar_items'), 100);
		add_filter('plugin_action_links_' . plugin_basename(RAPIDPRESS_PLUGIN_FILE), array($this, 'add_action_links'));
		add_action('wp_ajax_rapidpress_reset_settings', array($this, 'reset_settings'));
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
			'nonce' => wp_create_nonce('rapidpress_options_verify')
		));
	}

	public function ajax_save_settings() {
		if (!current_user_can('manage_options')) {
			wp_send_json_error('Insufficient permissions');
		}

		if (
			!isset($_POST['rapidpress_nonce']) ||
			!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['rapidpress_nonce'])), 'rapidpress_options_verify')
		) {
			wp_send_json_error('Invalid nonce');
		}

		$new_options = isset($_POST['rapidpress_options']) ? $this->sanitize_options(map_deep(wp_unslash($_POST['rapidpress_options']), 'sanitize_text_field')) : array();
		$old_options = get_option('rapidpress_options', array());

		// Handle JS and CSS disable rules
		if (!isset($new_options['js_disable_rules'])) {
			$new_options['js_disable_rules'] = array();
		}
		if (!isset($new_options['css_disable_rules'])) {
			$new_options['css_disable_rules'] = array();
		}

		// Remove the special handling that was preserving old rules
		// if (isset($new_options['js_disable_rules']) && $new_options['js_disable_rules'] === 'js_disable_rules') {
		//     $new_options['js_disable_rules'] = isset($old_options['js_disable_rules']) ? $old_options['js_disable_rules'] : array();
		// }
		// if (isset($new_options['css_disable_rules']) && $new_options['css_disable_rules'] === 'css_disable_rules') {
		//     $new_options['css_disable_rules'] = isset($old_options['css_disable_rules']) ? $old_options['css_disable_rules'] : array();
		// }

		// Compare new options with old options
		$changed = false;
		foreach ($new_options as $key => $value) {
			if (!isset($old_options[$key]) || $old_options[$key] !== $value) {
				$changed = true;
				break;
			}
		}

		if (!$changed) {
			wp_send_json_success('Settings are up to date');
			return;
		}

		// Update with new options directly instead of merging
		$update_result = update_option('rapidpress_options', $new_options);

		if ($update_result) {
			wp_send_json_success('Settings saved successfully');
		} else {
			// Check if the options are actually the same
			$current_options = get_option('rapidpress_options', array());
			if ($current_options == $new_options) {
				wp_send_json_success('Settings are up to date');
			} else {
				wp_send_json_error('Failed to update options in the database.');
			}
		}
	}

	public function activation_notice() {
		if (get_transient('rapidpress_activation_notice')) {
?>
			<div class="updated notice is-dismissible">
				<p><?php esc_html_e('Thank you for installing RapidPress! Please visit the ', 'rapidpress'); ?>
					<a href="<?php echo esc_url(admin_url('options-general.php?page=rapidpress')); ?>"><?php esc_html_e('settings page', 'rapidpress'); ?></a>
					<?php esc_html_e('to configure the plugin.', 'rapidpress'); ?>
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
				'title' => __('RapidPress', 'rapidpress'),
			),
		));

		// Add submenu items
		$submenu_items = array(
			'general' 			=> __('General', 'rapidpress'),
			'file-optimization' 	=> __('File Optimization', 'rapidpress'),
			'asset-manager' 		=> __('Asset Manager', 'rapidpress'),
			'settings' 			=> __('Settings', 'rapidpress'),
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
		// $active_tab = '#general'; // Default to general
		// if (isset($_GET['tab'])) {
		// 	$active_tab = '#' . sanitize_text_field(wp_unslash($_GET['tab']));
		// }

		if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'rapidpress_tab_nonce')) {
			$active_tab = '#general';
		} else {
			$active_tab = isset($_GET['tab']) ? '#' . sanitize_text_field(wp_unslash($_GET['tab'])) : '#general';
		}

		include_once 'partials/admin-display.php';
	}

	public function register_settings() {
		register_setting('rapidpress_options', 'rapidpress_options', array(
			'type' => 'array',
			'sanitize_callback' => array($this, 'sanitize_options'),
		));
	}

	public function sanitize_options($options) {
		if (!is_array($options)) {
			return array();
		}
		$sanitized_options = array();
		$sanitization_rules = array(
			'disable_comments'                     => 'boolean',
			'remove_comment_urls'                  => 'boolean',
			'disable_dashicons'                    => 'boolean',
			'disable_embeds'                    	=> 'boolean',
			'disable_xmlrpc'                    	=> 'boolean',
			'disable_emojis'                   	 	=> 'boolean',
			'remove_jquery_migrate'                => 'boolean',
			'disable_rss_feeds'                    => 'boolean',
			'remove_rsd_link'                    	=> 'boolean',
			'hide_wp_version'                    	=> 'boolean',
			'remove_global_styles'                 => 'boolean',
			'separate_block_styles'                => 'boolean',
			'disable_self_pingbacks'               => 'boolean',
			'disable_google_maps'                  => 'boolean',
			'remove_shortlink'                    	=> 'boolean',
			'disable_rest_api'							=> 'text_field',
			'remove_rest_api_links'                => 'boolean',
			'limit_post_revisions'                 => 'limit_post_revisions',
			'optimization_scope'                   => 'text_field',
			'optimized_pages'                      => 'multiline_urls',
			'enable_optimization_scope_exclusions' => 'boolean',
			'optimization_excluded_pages'          => 'multiline_urls',
			'html_minify'                          => 'boolean',
			'css_minify'                           => 'boolean',
			'combine_css'                          => 'boolean',
			'enable_combine_css_exclusions'        => 'boolean',
			'combine_css_exclusions'               => 'multiline_urls',
			'js_minify'                            => 'boolean',
			'js_defer'                             => 'boolean',
			'enable_js_defer_exclusions'           => 'boolean',
			'js_defer_exclusions'                  => 'multiline_urls',
			'js_delay'                             => 'boolean',
			'js_delay_type'                        => 'js_delay_type',
			'js_delay_duration'                    => 'js_delay_duration',
			'js_delay_specific_files'              => 'multiline_urls',
			'enable_js_delay_exclusions'           => 'boolean',
			'js_delay_exclusions'                  => 'multiline_urls',
			'js_disable_rules'                     => 'js_disable_rules',
			'css_disable_rules'                    => 'css_disable_rules',
			'clean_uninstall'                      => 'boolean',
			'clean_deactivate'                      => 'boolean',
		);

		foreach ($sanitization_rules as $option => $rule) {
			if (isset($options[$option])) {
				switch ($rule) {
					case 'boolean':
						$sanitized_options[$option] = rest_sanitize_boolean($options[$option]);
						break;

					case 'multiline_urls':
						$sanitized_options[$option] = $this->sanitize_multiline_urls($options[$option]);
						break;

					case 'text_field':
						$sanitized_options[$option] = sanitize_text_field($options[$option]);
						break;

					default:
						$method = "sanitize_{$rule}";
						if (method_exists($this, $method)) {
							$sanitized_options[$option] = $this->$method($options[$option]);
						}
						break;
				}
			}
		}

		return $sanitized_options;
	}

	// Sanitizes textarea input containing multiple URLs or handles (one per line)
	private function sanitize_multiline_urls($input) {
		if (empty($input)) {
			return '';
		}

		// If input is already an array, convert to string
		if (is_array($input)) {
			$input = implode("\n", $input);
		}

		// First, decode any URL-encoded spaces
		$input = urldecode($input);

		// Split the input by any combination of delimiters (spaces, commas, or newlines)
		$urls = preg_split('/[\s,]+/', $input);

		// Sanitize each URL/handle and filter empty ones
		$sanitized_urls = array_filter(array_map(function ($url) {
			$url = trim($url);
			if (empty($url)) {
				return '';
			}

			// Handle wildcards and relative paths
			if (strpos($url, '*') !== false || strpos($url, '/') === 0) {
				return sanitize_text_field($url);
			}

			// Check if this looks like a handle (no slashes, dots, or protocols)
			if (!preg_match('~[/.]|(?:f|ht)tps?://~i', $url)) {
				// This is likely a handle, just sanitize it as text
				return sanitize_text_field($url);
			}

			// Check if this is a partial URL (contains .css or .js extension)
			if (preg_match('~\.(css|js)($|\?)~i', $url) && !preg_match('~^(?:f|ht)tps?://|//~i', $url)) {
				// This is likely a partial URL, just sanitize it as text
				return sanitize_text_field($url);
			}

			// Ensure URL has protocol (only for full URLs)
			if (!preg_match('~^(?:f|ht)tps?://~i', $url) && strpos($url, '.') !== false && strpos($url, '.') < strpos($url, '/')) {
				// This looks like a domain (has a dot before any slash), add protocol
				$url = 'https://' . $url;
			}

			return esc_url_raw($url);
		}, $urls));

		// Return URLs with one per line
		return implode("\n", array_filter($sanitized_urls));
	}

	public function reset_settings() {
		check_ajax_referer('rapidpress_options_verify', 'nonce');

		if (!current_user_can('manage_options')) {
			wp_send_json_error('Insufficient permissions');
		}

		$default_options = [
			// 'optimization_scope' => 'entire_site',
			// 'enable_optimization_scope_exclusions' => '0',
			// 'optimized_pages' => '',
			// 'optimization_excluded_pages' => '',
			// 'html_minify' => '0',
			// 'css_minify' => '0',
			// 'combine_css' => '0',
			// 'enable_combine_css_exclusions' => '0',
			// 'combine_css_exclusions' => '',
			// 'js_minify' => '0',
			// 'js_defer' => '0',
			// 'enable_js_defer_exclusions' => '0',
			// 'js_defer_exclusions' => '',
			// 'js_delay' => '0',
			// 'js_delay_type' => 'all',
			// 'js_delay_duration' => '1',
			// 'enable_js_delay_exclusions' => '0',
			// 'js_delay_exclusions' => '',
			// 'js_delay_specific_files' => '',
			// 'js_disable_rules' => [],
			// 'css_disable_rules' => []
		];
		update_option('rapidpress_options', $default_options);
		wp_send_json_success('Settings reset successfully');
	}

	private function sanitize_limit_post_revisions($value) {

		if (empty($value)) {
			return '';
		}

		if ($value === false || $value === 'false') {
			return 'false';
		}

		return intval($value);
	}

	public function sanitize_css_disable_rules($input) {
		$sanitized_rules = array();
		if (is_array($input)) {
			foreach ($input as $rule) {
				if (!empty($rule['styles'])) {
					$sanitized_rule = array(
						'styles' => $this->sanitize_multiline_urls($rule['styles']),
						'scope' => sanitize_text_field($rule['scope']),
						'exclude_enabled' => isset($rule['exclude_enabled']) && $rule['exclude_enabled'] === '1' ? '1' : '0',
						'exclude_pages' => $this->sanitize_multiline_urls($rule['exclude_pages']),
						'pages' => $this->sanitize_multiline_urls($rule['pages']),
						'is_active' => isset($rule['is_active']) && $rule['is_active'] === '1' ? '1' : '0',
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

	public function sanitize_js_delay_duration($input) {
		$valid_options = array('1', '2', '3', 'interaction');
		return in_array($input, $valid_options) ? $input : '1';
	}

	public function sanitize_js_delay_type($input) {
		$valid_options = array('all', 'specific');
		return in_array($input, $valid_options) ? $input : 'all';
	}

	public function sanitize_js_disable_rules($input) {
		$sanitized_rules = array();
		if (is_array($input)) {
			foreach ($input as $rule) {
				if (!empty($rule['scripts'])) {
					$sanitized_rule = array(
						'scripts' => $this->sanitize_multiline_urls($rule['scripts']),
						'scope' => sanitize_text_field($rule['scope']),
						'exclude_enabled' => isset($rule['exclude_enabled']) && $rule['exclude_enabled'] === '1' ? '1' : '0',
						'exclude_pages' => $this->sanitize_multiline_urls($rule['exclude_pages']),
						'pages' => $this->sanitize_multiline_urls($rule['pages']),
						'is_active' => isset($rule['is_active']) && $rule['is_active'] === '1' ? '1' : '0',
					);
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

		if (
			isset($_POST['rapidpress_active_tab'], $_POST['rapidpress_nonce']) &&
			wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['rapidpress_nonce'])), 'rapidpress_settings')
		) {
			$tab = sanitize_key(ltrim(sanitize_text_field(wp_unslash($_POST['rapidpress_active_tab'])), '#'));
			add_filter('wp_redirect', function ($location) use ($tab) {
				return add_query_arg('tab', $tab, $location);
			});
		}


		return $value;
	}

	public function clear_css_cache() {
		$upload_dir = wp_upload_dir();
		$combined_dir = trailingslashit($upload_dir['basedir']) . 'rapidpress';

		if (is_dir($combined_dir)) {
			array_map('unlink', glob("$combined_dir/*.*"));
		}

		RP_Options::delete_option('css_cache_meta');
	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	public function get_version() {
		return $this->version;
	}
}
