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
						<span class="dashicons dashicons-editor-help" title="Disable comments for all posts"></span>
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
						<span class="dashicons dashicons-editor-help" title="Remove comment URLs from the header"></span>
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
						<span class="dashicons dashicons-editor-help" title="Disable Dashicons"></span>
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
						<span class="dashicons dashicons-editor-help" title="Disable Embeds"></span>
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
						<span class="dashicons dashicons-editor-help" title="Disable XML-RPC"></span>
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
						<span class="dashicons dashicons-editor-help" title="Disable Emojis"></span>
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
						<span class="dashicons dashicons-editor-help" title="Remove jQuery Migrate"></span>
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
						<span class="dashicons dashicons-editor-help" title="Disable RSS Feeds"></span>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Disable RSS Feed Links</th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[disable_rss_feed_links]" value="1" <?php checked(1, RP_Options::get_option('disable_rss_feed_links'), true); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" title="Disable RSS Feed Links"></span>
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
						<span class="dashicons dashicons-editor-help" title="Hide WP Version"></span>
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
						<span class="dashicons dashicons-editor-help" title="Remove Global Styles"></span>
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
						<span class="dashicons dashicons-editor-help" title="Disable Self Pingbacks"></span>
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
						<span class="dashicons dashicons-editor-help" title="Disable Google Maps"></span>
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
						<span class="dashicons dashicons-editor-help" title="Remove RSD Link"></span>
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
						<span class="dashicons dashicons-editor-help" title="Remove Shortlink"></span>
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
						<option value="guest_users" <?php selected(RP_Options::get_option('disable_rest_api'), 'guest_users'); ?>>Disable for Guest Users</option>
					</select>
					<span class="dashicons dashicons-editor-help" title="Disable Rest API"></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Remove REST API Links</th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[remove_rest_api_links]" value="1" <?php checked(1, RP_Options::get_option('remove_rest_api_links'), true); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" title="Remove REST API link tag from the header"></span>
				</td>
			</tr>
			<tr valign="top"></tr>
			<th scope="row">Limit Post Revisions</th>
			<td>
				<select name="rapidpress_options[limit_post_revisions]" id="rapidpress_limit_post_revisions">
					<option value>Default (Unlimited)</option>
					<option value="disable_revisions" <?php selected(RP_Options::get_option('limit_post_revisions'), 'disable_revisions'); ?>>Disable Post Revisions</option>
					<option value="custom_revisions" <?php selected(RP_Options::get_option('limit_post_revisions'), 'custom_revisions'); ?>>Custom Revisions</option>
				</select>
				<span class="dashicons dashicons-editor-help" title="Limit Post Revisions"></span>
			</td>
			</tr>
		</table>
	</div>
</div>