<?php
// Ensure this file is being included by a parent file
if (!defined('ABSPATH')) exit; // Exit if accessed directly

use RapidPress\RP_Options;

?>
<div id="<?php echo esc_attr($tab_id); ?>" class="tab-pane">
	<h2 class="content-title"><span class="dashicons dashicons-dashboard"></span> <?php esc_html_e('General', 'rapidpress'); ?></h2>
	<div class="rapidpress-card">
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php esc_html_e('Disable Comments', 'rapidpress'); ?></th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[disable_comments]" value="1" <?php checked(1, RP_Options::get_option('disable_comments'), true); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="<?php esc_attr_e('This will disable comments on all post types and remove comment-related functionality.', 'rapidpress'); ?>"></span>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_html_e('Remove Comment URLs', 'rapidpress'); ?></th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[remove_comment_urls]" value="1" <?php checked(1, RP_Options::get_option('remove_comment_urls'), true); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="<?php esc_attr_e('Removes the comment author link and website field from the comment form.', 'rapidpress'); ?>"></span>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_html_e('Disable Dashicons', 'rapidpress'); ?></th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[disable_dashicons]" value="1" <?php checked(1, RP_Options::get_option('disable_dashicons'), true); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="<?php esc_attr_e('Disables dashicons on the front end.', 'rapidpress'); ?>"></span>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_html_e('Disable Embeds', 'rapidpress'); ?></th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[disable_embeds]" value="1" <?php checked(1, RP_Options::get_option('disable_embeds'), true); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="<?php esc_attr_e('Disable WordPress Embed JavaScript file.', 'rapidpress'); ?>"></span>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_html_e('Disable XML-RPC', 'rapidpress'); ?></th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[disable_xmlrpc]" value="1" <?php checked(1, RP_Options::get_option('disable_xmlrpc'), true); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="<?php esc_attr_e('Disable XML-RPC', 'rapidpress'); ?>"></span>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_html_e('Disable Emojis', 'rapidpress'); ?></th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[disable_emojis]" value="1" <?php checked(1, RP_Options::get_option('disable_emojis'), true); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="<?php esc_attr_e('Removes WordPress Emojis and related JavaScript file from the front end.', 'rapidpress'); ?>"></span>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_html_e('Remove jQuery Migrate', 'rapidpress'); ?></th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[remove_jquery_migrate]" value="1" <?php checked(1, RP_Options::get_option('remove_jquery_migrate'), true); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="<?php esc_attr_e('Removes jQuery Migrate JavaScript file.', 'rapidpress'); ?>"></span>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_html_e('Disable RSS Feeds', 'rapidpress'); ?></th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[disable_rss_feeds]" value="1" <?php checked(1, RP_Options::get_option('disable_rss_feeds'), true); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="<?php esc_attr_e('Disable WordPress generated RSS Feed and related links.', 'rapidpress'); ?>"></span>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_html_e('Remove RSD Link', 'rapidpress'); ?></th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[remove_rsd_link]" value="1" <?php checked(1, RP_Options::get_option('remove_rsd_link'), true); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="<?php esc_attr_e('Removes RSD (Real Simple Dicovery) link tag.', 'rapidpress'); ?>"></span>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_html_e('Hide WP Version', 'rapidpress'); ?></th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[hide_wp_version]" value="1" <?php checked(1, RP_Options::get_option('hide_wp_version'), true); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="<?php esc_attr_e('Removes WordPress version meta tag from front end.', 'rapidpress'); ?>"></span>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_html_e('Remove Global Styles', 'rapidpress'); ?></th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[remove_global_styles]" value="1" <?php checked(1, RP_Options::get_option('remove_global_styles'), true); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="<?php esc_attr_e('Removes the inline global styles (CSS and SVG).', 'rapidpress'); ?>"></span>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_html_e('Separate Block Styles', 'rapidpress'); ?></th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[separate_block_styles]" value="1" <?php checked(1, RP_Options::get_option('separate_block_styles'), true); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="<?php esc_attr_e('Load core block styles only when they are rendered instead of in a global styles.', 'rapidpress'); ?>"></span>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_html_e('Disable Self Pingbacks', 'rapidpress'); ?></th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[disable_self_pingbacks]" value="1" <?php checked(1, RP_Options::get_option('disable_self_pingbacks'), true); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="<?php esc_attr_e('Disables Self Pingbacks when linking to any internal article.', 'rapidpress'); ?>"></span>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_html_e('Disable Google Maps', 'rapidpress'); ?></th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[disable_google_maps]" value="1" <?php checked(1, RP_Options::get_option('disable_google_maps'), true); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="<?php esc_attr_e('Removes Google Maps script or iframe loading from the entire website.', 'rapidpress'); ?>"></span>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_html_e('Remove Shortlink', 'rapidpress'); ?></th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[remove_shortlink]" value="1" <?php checked(1, RP_Options::get_option('remove_shortlink'), true); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="<?php esc_attr_e('Removes Shortlink tag from the front end.', 'rapidpress'); ?>"></span>
						</label>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_html_e('Disable Rest API', 'rapidpress'); ?></th>
				<td>
					<select name="rapidpress_options[disable_rest_api]" id="rapidpress_disable_rest_api">
						<option value><?php esc_html_e('Default (Enabled)', 'rapidpress'); ?></option>
						<option value="non_admins" <?php selected(RP_Options::get_option('disable_rest_api'), 'non_admins'); ?>><?php esc_html_e('Disable for Non-Admins', 'rapidpress'); ?></option>
					</select>
					<span class="dashicons dashicons-editor-help" data-title="<?php esc_attr_e('Disable Rest API requests.', 'rapidpress'); ?>"></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_html_e('Remove REST API Links', 'rapidpress'); ?></th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[remove_rest_api_links]" value="1" <?php checked(1, RP_Options::get_option('remove_rest_api_links'), true); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="<?php esc_attr_e('Remove REST API link tag from the header.', 'rapidpress'); ?>"></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_html_e('Disable Heartbeat', 'rapidpress'); ?></th>
				<td>
					<select name="rapidpress_options[disable_heartbeat]" id="rapidpress_disable_heartbeat">
						<option value><?php esc_html_e('Default', 'rapidpress'); ?></option>
						<option value="disable_everywhere" <?php selected(RP_Options::get_option('disable_heartbeat'), 'disable_everywhere'); ?>><?php esc_html_e('Disable Everywhere', 'rapidpress'); ?></option>
						<option value="allow_posts" <?php selected(RP_Options::get_option('disable_heartbeat'), 'allow_posts'); ?>><?php esc_html_e('Only Allow When Editing Posts/Pages', 'rapidpress'); ?></option>
					</select>
					<span class="dashicons dashicons-editor-help" data-title="<?php esc_attr_e('Disables WordPress Heartbeat (used for auto saving & revision tracking).', 'rapidpress'); ?>"></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_html_e('Heartbeat Frequency', 'rapidpress'); ?></th>
				<td>
					<select name="rapidpress_options[heartbeat_frequency]" id="rapidpress_heartbeat_frequency">
						<option value><?php esc_html_e('15 Seconds (Default)', 'rapidpress'); ?></option>
						<option value="30" <?php selected(RP_Options::get_option('heartbeat_frequency'), 30); ?>><?php esc_html_e('30 Seconds', 'rapidpress'); ?></option>
						<option value="45" <?php selected(RP_Options::get_option('heartbeat_frequency'), 45); ?>><?php esc_html_e('45 Seconds', 'rapidpress'); ?></option>
						<option value="60" <?php selected(RP_Options::get_option('heartbeat_frequency'), 60); ?>><?php esc_html_e('60 Seconds', 'rapidpress'); ?></option>
					</select>
					<span class="dashicons dashicons-editor-help" data-title="<?php esc_attr_e('Controls how often the WordPress Heartbeat API is allowed to run.', 'rapidpress'); ?>"></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_html_e('Limit Post Revisions', 'rapidpress'); ?></th>
				<td>
					<select name="rapidpress_options[limit_post_revisions]" id="rapidpress_limit_post_revisions">
						<option value><?php esc_html_e('Default (Unlimited)', 'rapidpress'); ?></option>
						<option value="false" <?php selected(RP_Options::get_option('limit_post_revisions'), 'false'); ?>><?php esc_html_e('Disable Post Revisions', 'rapidpress'); ?></option>
						<option value="1" <?php selected(RP_Options::get_option('limit_post_revisions'), 1); ?>>1</option>
						<option value="2" <?php selected(RP_Options::get_option('limit_post_revisions'), 2); ?>>2</option>
						<option value="3" <?php selected(RP_Options::get_option('limit_post_revisions'), 3); ?>>3</option>
						<option value="4" <?php selected(RP_Options::get_option('limit_post_revisions'), 4); ?>>4</option>
						<option value="5" <?php selected(RP_Options::get_option('limit_post_revisions'), 5); ?>>5</option>
						<option value="10" <?php selected(RP_Options::get_option('limit_post_revisions'), 10); ?>>10</option>
						<option value="15" <?php selected(RP_Options::get_option('limit_post_revisions'), 15); ?>>15</option>
						<option value="20" <?php selected(RP_Options::get_option('limit_post_revisions'), 20); ?>>20</option>
					</select>
					<span class="dashicons dashicons-editor-help" data-title="<?php esc_attr_e('Limits the number of revisions that are allowed for posts and pages.', 'rapidpress'); ?>"></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_html_e('Autosave Interval', 'rapidpress'); ?></th>
				<td>
					<select name="rapidpress_options[autosave_interval]" id="rapidpress_autosave_interval">
						<option value><?php esc_html_e('1 Minute (Default)', 'rapidpress'); ?></option>
						<option value="172800" <?php selected(RP_Options::get_option('autosave_interval'), 172800); ?>><?php esc_html_e('Disable Autosave Interval', 'rapidpress'); ?></option>
						<option value="2" <?php selected(RP_Options::get_option('autosave_interval'), 2); ?>><?php esc_html_e('2 Minutes', 'rapidpress'); ?></option>
						<option value="3" <?php selected(RP_Options::get_option('autosave_interval'), 3); ?>><?php esc_html_e('3 Minutes', 'rapidpress'); ?></option>
						<option value="4" <?php selected(RP_Options::get_option('autosave_interval'), 4); ?>><?php esc_html_e('4 Minutes', 'rapidpress'); ?></option>
						<option value="5" <?php selected(RP_Options::get_option('autosave_interval'), 5); ?>><?php esc_html_e('5 Minutes', 'rapidpress'); ?></option>
						<option value="10" <?php selected(RP_Options::get_option('autosave_interval'), 10); ?>><?php esc_html_e('10 Minutes', 'rapidpress'); ?></option>
						<option value="15" <?php selected(RP_Options::get_option('autosave_interval'), 15); ?>><?php esc_html_e('15 Minutes', 'rapidpress'); ?></option>
						<option value="20" <?php selected(RP_Options::get_option('autosave_interval'), 20); ?>><?php esc_html_e('20 Minutes', 'rapidpress'); ?></option>
					</select>
					<span class="dashicons dashicons-editor-help" data-title="<?php esc_attr_e('Controls how often WordPress auto save posts and pages while editing.', 'rapidpress'); ?>"></span>
				</td>
			</tr>
		</table>
	</div>
</div>