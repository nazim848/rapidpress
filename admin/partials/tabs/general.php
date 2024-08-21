<?php
// Ensure this file is being included by a parent file
if (!defined('ABSPATH')) exit; // Exit if accessed directly

use RapidPress\RP_Options;

?>

<div id="<?php echo esc_attr($tab_id); ?>" class="tab-pane">
	<h2 class="content-title"> <span class="dashicons dashicons-dashboard"></span> General</h2>
	<div class="rapidpress-card">
		<table class="form-table">
			<tr valign="top">
				<th scope="row">Disable Comments</th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[disable_comments]" value="1" <?php checked(1, RP_Options::get_option('disable_comments'), true); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="This will disable comments on all post types and remove comment-related functionality."></span>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Remove Comment URLs</th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[remove_comment_urls]" value="1" <?php checked(1, RP_Options::get_option('remove_comment_urls'), true); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="Removes the comment author link and website field from the comment form."></span>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Disable Dashicons</th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[disable_dashicons]" value="1" <?php checked(1, RP_Options::get_option('disable_dashicons'), true); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="Disables dashicons on the front end."></span>
					</div>
				</td>
			</tr>
		</table>
	</div>
</div>