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
			<?php
			$last_run = RP_Options::get_option('cache_preload_last_run');
			$last_count = RP_Options::get_option('cache_preload_last_count', 0);
			if (!empty($last_run)) :
			?>
				<p>
					<?php echo esc_html(sprintf(__('Last preload: %s (%d URLs)', 'rapidpress'), wp_date('Y-m-d H:i:s', intval($last_run)), intval($last_count))); ?>
				</p>
			<?php endif; ?>
		</div>
	</div>
