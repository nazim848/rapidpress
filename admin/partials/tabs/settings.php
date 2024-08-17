<?php
// Ensure this file is being included by a parent file
if (!defined('ABSPATH')) exit; // Exit if accessed directly

?>

<div id="<?php echo esc_attr($tab_id); ?>" class="tab-pane">
	<h2 class="content-title"><span class="dashicons dashicons-admin-tools"></span> Settings</h2>
	<div class="rapidpress-card">
		<table class="form-table">
			<tr valign="top">
				<th scope="row">Reset Settings</th>
				<td>
					<div class="checkbox-radio">
						<button type="button" class="button" onclick="rapidpress_reset_settings()">Reset Settings</button>
						<span class="dashicons dashicons-editor-help" title="Reset all plugin settings to their default values"></span>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Clean Uninstall</th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_clean_uninstall" value="1" />
						</label>
						<span class="dashicons dashicons-editor-help" title="When enabled, all RapidPress settings and data will be deleted from the database when the plugin is uninstalled!"></span>
					</div>
				</td>
			</tr>
		</table>
	</div>
</div>