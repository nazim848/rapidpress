<?php
// Ensure this file is being included by a parent file
if (!defined('ABSPATH')) exit; // Exit if accessed directly

use RapidPress\RP_Options;

?>

<div id="<?php echo esc_attr($tab_id); ?>" class="tab-pane">
	<h2 class="content-title"><span class="dashicons dashicons-page"></span> <?php esc_html_e('Cache', 'rapidpress'); ?></h2>
	<div class="rapidpress-card">
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php esc_html_e('Enable Cache', 'rapidpress'); ?></th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[enable_cache]" value="1" <?php checked(RP_Options::get_option('enable_cache'), '1'); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="<?php esc_attr_e('When enabled, all pages will be cached.', 'rapidpress'); ?>"></span>
					</div>
				</td>
			</tr>
		</table>
	</div>
</div>