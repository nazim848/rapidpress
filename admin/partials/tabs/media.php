<?php
// Ensure this file is being included by a parent file
if (!defined('ABSPATH')) exit; // Exit if accessed directly

use RapidPress\RP_Options;

?>

<div id="<?php echo esc_attr($tab_id); ?>" class="tab-pane">
	<h2 class="content-title"><span class="dashicons dashicons-images-alt2"></span> <?php esc_html_e('Media', 'rapidpress'); ?></h2>
	<div class="rapidpress-card">
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php esc_html_e('Lazy Load Images', 'rapidpress'); ?></th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[lazy_load_images]" value="1" <?php checked(RP_Options::get_option('lazy_load_images'), '1'); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="<?php esc_attr_e('When enabled, all images will be lazy loaded.', 'rapidpress'); ?>"></span>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_html_e('Add Missing Dimensions', 'rapidpress'); ?></th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[add_missing_dimensions]" value="1" <?php checked(RP_Options::get_option('add_missing_dimensions'), '1'); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="<?php esc_attr_e('When enabled, all images will have their missing dimensions added.', 'rapidpress'); ?>"></span>
					</div>
				</td>
			</tr>
		</table>
	</div>
</div>