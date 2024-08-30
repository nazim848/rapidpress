<?php
// Ensure this file is being included by a parent file
if (!defined('ABSPATH')) exit; // Exit if accessed directly

use RapidPress\RP_Options;

?>
Test
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
			<tr valign="top">
				<th scope="row">Disable Embeds</th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[disable_embeds]" value="1" <?php checked(1, RP_Options::get_option('disable_embeds'), true); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="Disable WordPress Embed JavaScript file."></span>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Disable XML-RPC</th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[disable_xmlrpc]" value="1" <?php checked(1, RP_Options::get_option('disable_xmlrpc'), true); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="Disable XML-RPC"></span>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Disable Emojis</th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[disable_emojis]" value="1" <?php checked(1, RP_Options::get_option('disable_emojis'), true); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="Removes WordPress Emojis and related JavaScript file from the front end."></span>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Remove jQuery Migrate</th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[remove_jquery_migrate]" value="1" <?php checked(1, RP_Options::get_option('remove_jquery_migrate'), true); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="Removes jQuery Migrate JavaScript file."></span>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Disable RSS Feeds</th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[disable_rss_feeds]" value="1" <?php checked(1, RP_Options::get_option('disable_rss_feeds'), true); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="Disable WordPress generated RSS Feed and related links."></span>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Remove RSD Link</th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[remove_rsd_link]" value="1" <?php checked(1, RP_Options::get_option('remove_rsd_link'), true); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="Removes RSD (Real Simple Dicovery) link tag."></span>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Hide WP Version</th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[hide_wp_version]" value="1" <?php checked(1, RP_Options::get_option('hide_wp_version'), true); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="Removes WordPress version meta tag from front end."></span>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Remove Global Styles</th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[remove_global_styles]" value="1" <?php checked(1, RP_Options::get_option('remove_global_styles'), true); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="Removes the inline global styles (CSS and SVG)."></span>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Separate Block Styles</th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[separate_block_styles]" value="1" <?php checked(1, RP_Options::get_option('separate_block_styles'), true); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="Load core block styles only when they are rendered instead of in a global styles."></span>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Disable Self Pingbacks</th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[disable_self_pingbacks]" value="1" <?php checked(1, RP_Options::get_option('disable_self_pingbacks'), true); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="Disables Self Pingbacks when linking to any internal article."></span>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Disable Google Maps</th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[disable_google_maps]" value="1" <?php checked(1, RP_Options::get_option('disable_google_maps'), true); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="Removes Google Maps script or iframe loading from the entire website."></span>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Remove Shortlink</th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[remove_shortlink]" value="1" <?php checked(1, RP_Options::get_option('remove_shortlink'), true); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="Removes Shortlink tag from the front end."></span>
						</label>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Disable Rest API</th>
				<td>
					<select name="rapidpress_options[disable_rest_api]" id="rapidpress_disable_rest_api">
						<option value>Default (Enabled)</option>
						<option value="non_admins" <?php selected(RP_Options::get_option('disable_rest_api'), 'non_admins'); ?>>Disable for Non-Admins</option>
					</select>
					<span class="dashicons dashicons-editor-help" data-title="Disable Rest API requests."></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Remove REST API Links</th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[remove_rest_api_links]" value="1" <?php checked(1, RP_Options::get_option('remove_rest_api_links'), true); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="Remove REST API link tag from the header."></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Disable Heartbeat</th>
				<td>
					<select name="rapidpress_options[disable_heartbeat]" id="rapidpress_disable_heartbeat">
						<option value>Default</option>
						<option value="disable_everywhere" <?php selected(RP_Options::get_option('disable_heartbeat'), 'disable_everywhere'); ?>>Disable Everywhere</option>
						<option value="allow_posts" <?php selected(RP_Options::get_option('disable_heartbeat'), 'allow_posts'); ?>>Only Allow When Editing Posts/Pages</option>
					</select>
					<span class="dashicons dashicons-editor-help" data-title="Disables WordPress Heartbeat (used for auto saving & revision tracking)."></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Heartbeat Frequency</th>
				<td>
					<select name="rapidpress_options[heartbeat_frequency]" id="rapidpress_heartbeat_frequency">
						<option value>15 Seconds (Default)</option>
						<option value="30" <?php selected(RP_Options::get_option('heartbeat_frequency'), 30); ?>>30 Seconds</option>
						<option value="45" <?php selected(RP_Options::get_option('heartbeat_frequency'), 45); ?>>45 Seconds</option>
						<option value="60" <?php selected(RP_Options::get_option('heartbeat_frequency'), 60); ?>>60 Seconds</option>
					</select>
					<span class="dashicons dashicons-editor-help" data-title="Controls how often the WordPress Heartbeat API is allowed to run."></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Limit Post Revisions</th>
				<td>
					<select name="rapidpress_options[limit_post_revisions]" id="rapidpress_limit_post_revisions">
						<option value>Default (Unlimited)</option>
						<option value="false" <?php selected(RP_Options::get_option('limit_post_revisions'), 'false'); ?>>Disable Post Revisions</option>
						<option value="1" <?php selected(RP_Options::get_option('limit_post_revisions'), 1); ?>>1</option>
						<option value="2" <?php selected(RP_Options::get_option('limit_post_revisions'), 2); ?>>2</option>
						<option value="3" <?php selected(RP_Options::get_option('limit_post_revisions'), 3); ?>>3</option>
						<option value="4" <?php selected(RP_Options::get_option('limit_post_revisions'), 4); ?>>4</option>
						<option value="5" <?php selected(RP_Options::get_option('limit_post_revisions'), 5); ?>>5</option>
						<option value="10" <?php selected(RP_Options::get_option('limit_post_revisions'), 10); ?>>10</option>
						<option value="15" <?php selected(RP_Options::get_option('limit_post_revisions'), 15); ?>>15</option>
						<option value="20" <?php selected(RP_Options::get_option('limit_post_revisions'), 20); ?>>20</option>
					</select>
					<span class="dashicons dashicons-editor-help" data-title="Limits the number of revisions that are allowed for posts and pages."></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Autosave Interval</th>
				<td>
					<select name="rapidpress_options[autosave_interval]" id="rapidpress_autosave_interval">
						<option value>1 Minute (Default)</option>
						<option value="172800" <?php selected(RP_Options::get_option('autosave_interval'), 172800); ?>>Disable Autosave Interval</option>
						<option value="2" <?php selected(RP_Options::get_option('autosave_interval'), 2); ?>>2 Minutes</option>
						<option value="3" <?php selected(RP_Options::get_option('autosave_interval'), 3); ?>>3 Minutes</option>
						<option value="4" <?php selected(RP_Options::get_option('autosave_interval'), 4); ?>>4 Minutes</option>
						<option value="5" <?php selected(RP_Options::get_option('autosave_interval'), 5); ?>>5 Minutes</option>
						<option value="10" <?php selected(RP_Options::get_option('autosave_interval'), 10); ?>>10 Minutes</option>
						<option value="15" <?php selected(RP_Options::get_option('autosave_interval'), 15); ?>>15 Minutes</option>
						<option value="20" <?php selected(RP_Options::get_option('autosave_interval'), 20); ?>>20 Minutes</option>
					</select>
					<span class="dashicons dashicons-editor-help" data-title="Controls how often WordPress auto save posts and pages while editing."></span>
				</td>
			</tr>
		</table>
	</div>
</div>