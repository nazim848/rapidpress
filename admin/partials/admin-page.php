<?php
// Ensure this file is being included by a parent file
if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Verify user capabilities
if (!current_user_can('manage_options')) {
	wp_die(
		esc_html__('You do not have sufficient permissions to access this page.', 'rapidpress'),
		'',
		array('response' => 403)
	);
}


// Define tabs
$tabs = array(
	'general' => esc_html__('General', 'rapidpress'),
	'cache' => esc_html__('Cache', 'rapidpress'),
	'file-optimization' => esc_html__('File Optimization', 'rapidpress'),
	'media' => esc_html__('Media', 'rapidpress'),
	'asset-manager' => esc_html__('Asset Manager', 'rapidpress'),
	// 'preloading' => esc_html__('Preloading', 'rapidpress'),
	// 'database' => esc_html__('Database', 'rapidpress'),
	// 'cdn' => esc_html__('CDN', 'rapidpress'),
	'tools' => esc_html__('Tools', 'rapidpress')
);

// Get current tab.
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only tab selection in admin UI.
$rapidpress_active_tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'general';

// Ensure the active tab is valid.
if (!array_key_exists($rapidpress_active_tab, $tabs)) {
	$rapidpress_active_tab = 'general';
}
?>

<div class="wrap">
	<!-- Logo -->
	<div class="rapidpress-logo"></div>
	<div class="rapidpress-admin-content">
		<h2 class="nav-tab-wrapper">
			<?php
				foreach ($tabs as $rapidpress_tab_id => $rapidpress_tab_name) {
					$rapidpress_tab_class = ($rapidpress_tab_id === $rapidpress_active_tab) ? ' nav-tab-active' : '';
					echo '<a href="#' . esc_attr($rapidpress_tab_id) . '" class="nav-tab' . esc_attr($rapidpress_tab_class) . '">' . esc_html($rapidpress_tab_name) . '</a>';
				}
			?>
		</h2>

		<form id="rapidpress-settings-form" method="post">
			<?php settings_fields('rapidpress_options'); ?>
			<?php do_settings_sections('rapidpress_options'); ?>
			<?php wp_nonce_field('rapidpress_options_verify', 'rapidpress_nonce'); ?>
				<input type="hidden" id="rapidpress_active_tab" name="rapidpress_active_tab" value="<?php echo esc_attr($rapidpress_active_tab); ?>">

			<div class="tab-content">
				<?php
					foreach ($tabs as $rapidpress_tab_id => $rapidpress_tab_name) {
						$rapidpress_tab_style = ($rapidpress_tab_id === $rapidpress_active_tab) ? '' : 'style="display:none;"';
						$rapidpress_tab_file = plugin_dir_path(__FILE__) . 'tabs/' . $rapidpress_tab_id . '.php';
						if (file_exists($rapidpress_tab_file)) {
							include $rapidpress_tab_file;
						} else {
							echo '<p>' . esc_html__('Tab content not found.', 'rapidpress') . '</p>';
						}
				}
				?>
			</div>

			<p class="submit" id="submit-button" style="display: none;">
				<?php submit_button(null, 'primary rapidpress-btn', 'submit', false); ?>
			</p>
		</form>
	</div>
</div>
