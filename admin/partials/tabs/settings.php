<?php
// Ensure this file is being included by a parent file
if (!defined('ABSPATH')) exit; // Exit if accessed directly

use RapidPress\RP_Options;

?>

<div id="<?php echo esc_attr($tab_id); ?>" class="tab-pane">
	<h2 class="content-title"><span class="dashicons dashicons-admin-tools"></span> Settings</h2>
	<div class="rapidpress-card">
		<table class="form-table">
			<tr valign="top">
				<th scope="row">Reset Settings</th>
				<td>
					<div class="checkbox-radio">
						<button type="button" class="button" id="rapidpress-reset-settings">Reset Settings</button>
						<span class="dashicons dashicons-editor-help" data-title="Reset all plugin settings to their default values"></span>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Clean Uninstall</th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[clean_uninstall]" value="1" <?php checked(RP_Options::get_option('clean_uninstall'), '1'); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="When enabled, all RapidPress settings and data will be deleted from the database when the plugin is uninstalled!"></span>
					</div>
				</td>
			</tr>
		</table>
	</div>
</div>