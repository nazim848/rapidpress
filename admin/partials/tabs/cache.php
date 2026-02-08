<?php
// Ensure this file is being included by a parent file
if (!defined('ABSPATH')) exit; // Exit if accessed directly

use RapidPress\RP_Options;
use RapidPress\Cache_Stats;

?>

<div id="<?php echo esc_attr($rapidpress_tab_id); ?>" class="tab-pane">
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
			<div id="rapidpress-cache-settings-wrapper">
				<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php esc_html_e('Early Cache Serving', 'rapidpress'); ?></th>
					<td>
						<div class="checkbox-radio">
							<label>
								<input type="checkbox" name="rapidpress_options[early_cache_serving]" value="1" <?php checked(RP_Options::get_option('early_cache_serving'), '1'); ?> />
							</label>
							<span class="dashicons dashicons-editor-help" data-title="<?php esc_attr_e('Serve cached pages via WordPress advanced-cache drop-in for faster cache hits. Keep disabled if another cache plugin manages advanced-cache.php.', 'rapidpress'); ?>"></span>
						</div>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php esc_html_e('Enable Preload', 'rapidpress'); ?></th>
					<td>
						<div class="checkbox-radio">
							<label>
								<input type="checkbox" name="rapidpress_options[cache_preload_enabled]" value="1" <?php checked(RP_Options::get_option('cache_preload_enabled'), '1'); ?> />
							</label>
							<span class="dashicons dashicons-editor-help" data-title="<?php esc_attr_e('Automatically warm cache with home/feed and recently updated posts/pages.', 'rapidpress'); ?>"></span>
						</div>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php esc_html_e('Preload Batch Size', 'rapidpress'); ?></th>
					<td>
						<input type="number" min="1" max="100" name="rapidpress_options[cache_preload_batch_size]" value="<?php echo esc_attr(RP_Options::get_option('cache_preload_batch_size', 20)); ?>" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php esc_html_e('Query String Policy', 'rapidpress'); ?></th>
					<td>
						<select name="rapidpress_options[cache_query_policy]">
							<option value="bypass" <?php selected(RP_Options::get_option('cache_query_policy', 'bypass'), 'bypass'); ?>><?php esc_html_e('Bypass Cache (default)', 'rapidpress'); ?></option>
							<option value="ignore" <?php selected(RP_Options::get_option('cache_query_policy', 'bypass'), 'ignore'); ?>><?php esc_html_e('Ignore Query Strings', 'rapidpress'); ?></option>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php esc_html_e('Mobile Cache Variant', 'rapidpress'); ?></th>
					<td>
						<div class="checkbox-radio">
							<label>
								<input type="checkbox" name="rapidpress_options[cache_mobile_variant]" value="1" <?php checked(RP_Options::get_option('cache_mobile_variant'), '1'); ?> />
							</label>
							<span class="dashicons dashicons-editor-help" data-title="<?php esc_attr_e('Create separate cache keys for mobile and desktop visitors.', 'rapidpress'); ?>"></span>
						</div>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php esc_html_e('Never Cache URLs', 'rapidpress'); ?></th>
					<td>
						<textarea cols="63" rows="4" name="rapidpress_options[cache_never_cache_urls]" placeholder="/checkout&#10;/cart&#10;https://example.com/custom-path"><?php echo esc_textarea(RP_Options::get_option('cache_never_cache_urls', '')); ?></textarea>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php esc_html_e('Never Cache User Agents', 'rapidpress'); ?></th>
					<td>
						<textarea cols="63" rows="4" name="rapidpress_options[cache_never_cache_user_agents]" placeholder="Lighthouse&#10;GTmetrix"><?php echo esc_textarea(RP_Options::get_option('cache_never_cache_user_agents', '')); ?></textarea>
					</td>
				</tr>
				</table>
				<p>
					<button type="button" id="rapidpress-purge-page-cache" class="button button-secondary">
						<?php esc_html_e('Purge All Page Cache', 'rapidpress'); ?>
					</button>
				</p>
				<p>
					<button type="button" id="rapidpress-preload-page-cache" class="button button-secondary">
						<?php esc_html_e('Preload Cache Now', 'rapidpress'); ?>
					</button>
				</p>
				<p>
					<button type="button" id="rapidpress-clear-css-cache" class="button button-secondary">
						<?php esc_html_e('Clear CSS Cache', 'rapidpress'); ?>
					</button>
				</p>
				<?php
				$rapidpress_last_run = get_option(\RapidPress\Cache_Preloader::LAST_RUN_OPTION, RP_Options::get_option('cache_preload_last_run', 0));
					$rapidpress_last_count = get_option(\RapidPress\Cache_Preloader::LAST_COUNT_OPTION, RP_Options::get_option('cache_preload_last_count', 0));
					$rapidpress_cache_stats = (new Cache_Stats())->get_summary();
					if (!empty($rapidpress_last_run)) :
						/* translators: 1: last preload date/time, 2: number of preloaded URLs. */
						$rapidpress_preload_status_text = sprintf(__('Last preload: %1$s (%2$d URLs)', 'rapidpress'), wp_date('Y-m-d H:i:s', intval($rapidpress_last_run)), intval($rapidpress_last_count));
					else :
						$rapidpress_preload_status_text = __('Last preload: Not run yet', 'rapidpress');
					endif;
				?>
				<p id="rapidpress-preload-status">
					<?php echo esc_html($rapidpress_preload_status_text); ?>
				</p>
				<p>
					<?php
					/* translators: 1: number of cached files, 2: total cache size in human-readable format. */
					echo esc_html(sprintf(__('Cache files: %1$d | Size: %2$s', 'rapidpress'), intval($rapidpress_cache_stats['file_count']), $rapidpress_cache_stats['total_size_human']));
					?>
				</p>
			</div>
		</div>
	</div>
