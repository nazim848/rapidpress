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
	'file-optimization' => esc_html__('File Optimization', 'rapidpress'),
	'asset-manager' => esc_html__('Asset Manager', 'rapidpress'),
	// 'media' => esc_html__('Media', 'rapidpress'),
	// 'cache' => esc_html__('Cache', 'rapidpress'),
	// 'preloading' => esc_html__('Preloading', 'rapidpress'),
	// 'database' => esc_html__('Database', 'rapidpress'),
	// 'cdn' => esc_html__('CDN', 'rapidpress'),
	'settings' => esc_html__('Settings', 'rapidpress')
);

// Get current tab
$active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general';

// Ensure the active tab is valid
if (!array_key_exists($active_tab, $tabs)) {
	$active_tab = 'general';
}
?>

<div class="wrap">
	<!-- Logo -->
	<div class="rapidpress-logo"></div>
	<div class="rapidpress-admin-content">
		<h2 class="nav-tab-wrapper">
			<?php
			foreach ($tabs as $tab_id => $tab_name) {
				$class = ($tab_id === $active_tab) ? ' nav-tab-active' : '';
				echo '<a href="#' . esc_attr($tab_id) . '" class="nav-tab' . esc_attr($class) . '">' . esc_html($tab_name) . '</a>';
			}
			?>
		</h2>

		<form id="rapidpress-settings-form" method="post">
			<?php settings_fields('rapidpress_options'); ?>
			<?php do_settings_sections('rapidpress_options'); ?>
			<?php wp_nonce_field('rapidpress_options_verify', 'rapidpress_nonce'); ?>
			<input type="hidden" id="rapidpress_active_tab" name="rapidpress_active_tab" value="<?php echo esc_attr($active_tab); ?>">

			<div class="tab-content">
				<?php
				foreach ($tabs as $tab_id => $tab_name) {
					$style = ($tab_id === $active_tab) ? '' : 'style="display:none;"';
					$tab_file = plugin_dir_path(__FILE__) . 'tabs/' . $tab_id . '.php';
					if (file_exists($tab_file)) {
						include $tab_file;
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