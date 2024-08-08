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



	<div class="nav-tab-wrapper">
		<?php foreach ($tabs as $tab_key => $tab_name) : ?>
			<a href="#<?php echo esc_attr($tab_key); ?>" class="nav-tab <?php echo $active_tab === $tab_key ? 'nav-tab-active' : ''; ?>"><?php echo esc_html($tab_name); ?></a>
		<?php endforeach; ?>
	</div>

	<div class="tab-content">
		<?php foreach ($tabs as $tab_key => $tab_name) : ?>
			<div id="<?php echo esc_attr($tab_key); ?>" class="tab-pane <?php echo $active_tab === $tab_key ? 'active' : ''; ?>">
				<form id="rapidpress-form-<?php echo esc_attr($tab_key); ?>" class="rapidpress-form" method="post" action="options.php">
					<?php
					settings_fields('rapidpress_options');
					do_settings_sections('rapidpress');
					include RAPIDPRESS_PLUGIN_DIR . 'admin/partials/tabs/' . $tab_key . '.php';

					if ($tab_key !== 'dashboard') {
						submit_button();
					}

					?>
				</form>
			</div>
		<?php endforeach; ?>
	</div>
</div>