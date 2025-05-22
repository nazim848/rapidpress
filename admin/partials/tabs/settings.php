<?php
// Ensure this file is being included by a parent file
if (!defined('ABSPATH')) exit; // Exit if accessed directly

use RapidPress\RP_Options;

?>

<div id="<?php echo esc_attr($tab_id); ?>" class="tab-pane">
	<h2 class="content-title"><span class="dashicons dashicons-admin-tools"></span> <?php esc_html_e('Settings', 'rapidpress'); ?></h2>
	<div class="rapidpress-card">
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php esc_html_e('Reset Settings', 'rapidpress'); ?></th>
				<td>
					<div class="checkbox-radio">
						<button type="button" class="button" id="rapidpress-reset-settings"><?php esc_html_e('Reset Settings', 'rapidpress'); ?></button>
						<span class="dashicons dashicons-editor-help" data-title="<?php esc_attr_e('Reset all plugin settings to their default values', 'rapidpress'); ?>"></span>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_html_e('Clean Deactivate', 'rapidpress'); ?></th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[clean_deactivate]" value="1" <?php checked(RP_Options::get_option('clean_deactivate'), '1'); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="<?php esc_attr_e('When enabled, all RapidPress settings and data will be deleted from the database when the plugin is deactivated!', 'rapidpress'); ?>"></span>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_html_e('Clean Uninstall', 'rapidpress'); ?></th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[clean_uninstall]" value="1" <?php checked(RP_Options::get_option('clean_uninstall'), '1'); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="<?php esc_attr_e('When enabled, all RapidPress settings and data will be deleted from the database when the plugin is uninstalled!', 'rapidpress'); ?>"></span>
					</div>
				</td>
			</tr>
		</table>
	</div>
</div>