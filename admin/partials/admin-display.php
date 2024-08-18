<?php
// Ensure this file is being included by a parent file
if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Verify user capabilities
if (!current_user_can('manage_options')) {
	wp_die(__('You do not have sufficient permissions to access this page.'));
}

// Process form submission
if (isset($_POST['rapidpress_options'])) {
	check_admin_referer('rapidpress_options_verify');
	$options = $_POST['rapidpress_options'];
	$sanitized_options = $this->sanitize_options($options);
	update_option('rapidpress_options', $sanitized_options);
	wp_safe_redirect(add_query_arg('settings-updated', 'true', wp_get_referer()));
	exit;
}
$settings_updated = isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true';

// Define tabs
$tabs = array(
	'general' => 'General',
	'file-optimization' => 'File Optimization',
	'asset-manager' => 'Asset Manager',
	// 'media' => 'Media',
	// 'cache' => 'Cache',
	// 'preloading' => 'Preloading',
	// 'database' => 'Database',
	// 'cdn' => 'CDN',
	'settings' => 'Settings'
);

// Get current tab
$active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general';

// Ensure the active tab is valid
if (!array_key_exists($active_tab, $tabs)) {
	$active_tab = 'general';
}
?>

<div class="wrap">
	<img src="<?php echo RAPIDPRESS_PLUGIN_URL . '/admin/images/rapidpress-logo.svg'; ?>" alt="RapidPress Logo" class="rapidpress-logo" width="190">

	<?php if ($settings_updated) : ?>
		<div id="setting-error-settings_updated" class="notice notice-success settings-error is-dismissible">
			<p><strong>Settings saved.</strong></p>
		</div>
	<?php endif; ?>

	<?php settings_errors(); ?>

	<div class="rapidpress-admin-content">
		<h2 class="nav-tab-wrapper">
			<?php
			foreach ($tabs as $tab_id => $tab_name) {
				$class = ($tab_id === $active_tab) ? ' nav-tab-active' : '';
				echo '<a href="#' . esc_attr($tab_id) . '" class="nav-tab' . $class . '">' . esc_html($tab_name) . '</a>';
			}
			?>
		</h2>

		<form method="post" action="options.php">
			<?php settings_fields('rapidpress_options'); ?>
			<?php wp_nonce_field('rapidpress_settings', 'rapidpress_nonce'); ?>
			<input type="hidden" id="rapidpress_active_tab" name="rapidpress_active_tab" value="<?php echo esc_attr($active_tab); ?>">

			<div class="tab-content">
				<?php
				foreach ($tabs as $tab_id => $tab_name) {
					$style = ($tab_id === $active_tab) ? '' : 'style="display:none;"';
					// echo '<div id="' . esc_attr($tab_id) . '" class="tab-pane" ' . $style . '>';
					$tab_file = plugin_dir_path(__FILE__) . 'tabs/' . $tab_id . '.php';
					if (file_exists($tab_file)) {
						include $tab_file;
					} else {
						echo '<p>Tab content not found.</p>';
					}
					// echo '</div>';
				}
				?>
			</div>
			<p class="submit" id="submit-button" style="<?php // echo $active_tab === 'general' ? 'display:none;' : ''; 
																		?>">
				<?php submit_button(null, 'primary rapidpress-btn', 'submit', false); ?>
			</p>
		</form>
	</div>
</div>