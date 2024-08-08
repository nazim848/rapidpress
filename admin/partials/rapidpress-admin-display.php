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
	$options = array(
		'rapidpress_js_defer',
		'rapidpress_js_defer_exclusions',
		// ... other options ...
	);

	foreach ($options as $option) {
		if (isset($_POST[$option])) {
			update_option($option, $_POST[$option]);
		} else {
			delete_option($option);
		}
	}
	wp_safe_redirect(add_query_arg('settings-updated', 'true', wp_get_referer()));
	exit;
	// Process and sanitize form data here
}

$settings_updated = isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true';

// Define tabs
$tabs = array(
	'dashboard' => 'Dashboard',
	'file-optimization' => 'File Optimization',
	'asset-management' => 'Asset Management',
	'caching' => 'Caching',
	'advanced' => 'Advanced'
);

// Get current tab
$active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'dashboard';

// Ensure the active tab is valid
if (!array_key_exists($active_tab, $tabs)) {
	$active_tab = 'dashboard';
}
?>

<div class="wrap">
	<h1><?php echo esc_html(get_admin_page_title()); ?></h1>

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
					echo '<div id="' . esc_attr($tab_id) . '" class="tab-pane" ' . $style . '>';
					$tab_file = plugin_dir_path(__FILE__) . 'tabs/' . $tab_id . '.php';
					if (file_exists($tab_file)) {
						include $tab_file;
					} else {
						echo '<p>Tab content not found.</p>';
					}
					echo '</div>';
				}
				?>
			</div>
			<?php if ($active_tab !== 'dashboard') : ?>
				<?php submit_button(); ?>
			<?php endif; ?>
		</form>
	</div>
</div>