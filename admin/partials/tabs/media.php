<?php
// Ensure this file is being included by a parent file
if (!defined('ABSPATH')) exit; // Exit if accessed directly

use RapidPress\RP_Options;

?>

<div id="<?php echo esc_attr($rapidpress_tab_id); ?>" class="tab-pane">
	<h2 class="content-title"><span class="dashicons dashicons-images-alt2"></span> <?php esc_html_e('Media', 'rapidpress'); ?></h2>

	<!-- Lazy Loading Section -->
	<div class="rapidpress-card">
		<h3><?php esc_html_e('Lazy Loading', 'rapidpress'); ?></h3>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php esc_html_e('Enable Lazy Loading', 'rapidpress'); ?></th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[lazy_load_images]" value="1" <?php checked(RP_Options::get_option('lazy_load_images'), '1'); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="<?php esc_attr_e('Enable lazy loading for all images. Uses modern loading=\"lazy\" attribute with JavaScript fallback for older browsers.', 'rapidpress'); ?>"></span>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_html_e('Skip First Images', 'rapidpress'); ?></th>
				<td>
					<input type="number" name="rapidpress_options[lazy_load_skip_first]" value="<?php echo esc_attr(RP_Options::get_option('lazy_load_skip_first', '2')); ?>" min="0" max="10" style="width: 80px;" />
					<p class="description"><?php esc_html_e('Number of images to skip lazy loading (above-the-fold images). Default: 2', 'rapidpress'); ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_html_e('JavaScript Fallback', 'rapidpress'); ?></th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[lazy_load_fallback]" value="1" <?php checked(RP_Options::get_option('lazy_load_fallback', '1'), '1'); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="<?php esc_attr_e('Enable JavaScript fallback for browsers that don\'t support native lazy loading.', 'rapidpress'); ?>"></span>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_html_e('Loading Threshold', 'rapidpress'); ?></th>
				<td>
					<input type="number" name="rapidpress_options[lazy_load_threshold]" value="<?php echo esc_attr(RP_Options::get_option('lazy_load_threshold', '200')); ?>" min="0" max="1000" style="width: 100px;" />
					<span>px</span>
					<p class="description"><?php esc_html_e('Distance from viewport when images should start loading. Default: 200px', 'rapidpress'); ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_html_e('Placeholder Type', 'rapidpress'); ?></th>
				<td>
					<select name="rapidpress_options[lazy_load_placeholder]">
						<option value="transparent" <?php selected(RP_Options::get_option('lazy_load_placeholder', 'transparent'), 'transparent'); ?>><?php esc_html_e('Transparent', 'rapidpress'); ?></option>
						<option value="blur" <?php selected(RP_Options::get_option('lazy_load_placeholder', 'transparent'), 'blur'); ?>><?php esc_html_e('Blurred Background', 'rapidpress'); ?></option>
					</select>
					<p class="description"><?php esc_html_e('Type of placeholder to show while images are loading.', 'rapidpress'); ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_html_e('Exclusions', 'rapidpress'); ?></th>
				<td>
					<textarea name="rapidpress_options[lazy_load_exclusions]" rows="5" cols="50" placeholder="<?php esc_attr_e('logo.png
.no-lazy
#hero-image
/wp-content/uploads/2023/banner.jpg', 'rapidpress'); ?>"><?php echo esc_textarea(RP_Options::get_option('lazy_load_exclusions', '')); ?></textarea>
					<p class="description">
						<?php esc_html_e('Exclude specific images from lazy loading. One per line. Supports:', 'rapidpress'); ?><br>
						<strong><?php esc_html_e('CSS Classes:', 'rapidpress'); ?></strong> .no-lazy<br>
						<strong><?php esc_html_e('IDs:', 'rapidpress'); ?></strong> #hero-image<br>
						<strong><?php esc_html_e('File patterns:', 'rapidpress'); ?></strong> logo.png, /uploads/banner.jpg
					</p>
				</td>
			</tr>
		</table>
	</div>

	<!-- Image Optimization Section -->
	<div class="rapidpress-card">
		<h3><?php esc_html_e('Image Optimization', 'rapidpress'); ?></h3>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php esc_html_e('Add Missing Dimensions', 'rapidpress'); ?></th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[add_missing_dimensions]" value="1" <?php checked(RP_Options::get_option('add_missing_dimensions'), '1'); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="<?php esc_attr_e('Automatically add width and height attributes to images that are missing dimensions.', 'rapidpress'); ?>"></span>
					</div>
				</td>
			</tr>
		</table>
	</div>
</div>
