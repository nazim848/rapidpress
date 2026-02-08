<?php
// Ensure this file is being included by a parent file
if (!defined('ABSPATH')) exit; // Exit if accessed directly

use RapidPress\RP_Options;

?>

<div id="<?php echo esc_attr($rapidpress_tab_id); ?>" class="tab-pane">
	<h2 class="content-title"><span class="dashicons dashicons-hidden"></span> <?php esc_html_e('Disable Assets', 'rapidpress'); ?></h2>
	<p class="desc"><?php esc_html_e('Disable specific assets (CSS and JS) from loading on your site. This can help improve page load times and reduce server load by disabling assets that are not needed for that page.', 'rapidpress'); ?></p>
	<div class="accordion-item">
		<div class="accordion-header"><?php esc_html_e('JavaScript', 'rapidpress'); ?></div>
		<div class="accordion-content">
			<div class="rapidpress-card">
				<table class="form-table" id="js-asset-management">
					<tr class="table-head">
						<th style="width: 40%;"><?php esc_html_e('Script URL or Handle (one per line)', 'rapidpress'); ?> <span class="dashicons dashicons-editor-help" data-title="<?php esc_attr_e('Enter the script file URL, partial filename, or the registered handle name of the script you want to disable. You can enter multiple URLs, partial filenames, or handles by separating them with a new line.', 'rapidpress'); ?>"></span></th>
						<th style="width: 40%;"><?php esc_html_e('Disable Scope', 'rapidpress'); ?> <span class="dashicons dashicons-editor-help" data-title="<?php esc_attr_e('Choose where to disable the script: "Entire Site" applies to all pages, "Front Page" only disables on your homepage, and "Specific Pages" lets you select individual URLs to disable on.', 'rapidpress'); ?>"></span></th>
						<th style="width: 12%;"><?php esc_html_e('Actions', 'rapidpress'); ?> <span class="dashicons dashicons-editor-help" data-title="<?php esc_attr_e('Enable, disable, or remove a script disable rule.', 'rapidpress'); ?>"></span></th>
					</tr>
					<?php
						$rapidpress_js_rules = RP_Options::get_option('js_disable_rules', array());
						foreach ($rapidpress_js_rules as $rapidpress_index => $rapidpress_rule) {
							$rapidpress_scripts = isset($rapidpress_rule['scripts']) ? $rapidpress_rule['scripts'] : array();
							$rapidpress_scripts_text = is_array($rapidpress_scripts) ? implode("\n", $rapidpress_scripts) : $rapidpress_scripts;
							$rapidpress_is_active = isset($rapidpress_rule['is_active']) ? $rapidpress_rule['is_active'] : true;
							$rapidpress_scope = isset($rapidpress_rule['scope']) ? $rapidpress_rule['scope'] : 'entire_site';

							$rapidpress_exclude_enabled = isset($rapidpress_rule['exclude_enabled']) ? $rapidpress_rule['exclude_enabled'] : false;

							$rapidpress_show_exclude_pages = ($rapidpress_scope === 'entire_site' && $rapidpress_exclude_enabled) ? 'block' : 'none';

							$rapidpress_exclude_pages = isset($rapidpress_rule['exclude_pages']) ? $rapidpress_rule['exclude_pages'] : '';
							$rapidpress_exclude_pages_text = is_array($rapidpress_exclude_pages) ? implode("\n", $rapidpress_exclude_pages) : $rapidpress_exclude_pages;
							$rapidpress_pages = isset($rapidpress_rule['pages']) ? $rapidpress_rule['pages'] : array();
							$rapidpress_pages_text = is_array($rapidpress_pages) ? implode("\n", $rapidpress_pages) : $rapidpress_pages;

						printf(
							'<tr>
								  <td>
										<textarea cols="63" rows="3" name="rapidpress_options[js_disable_rules][%1$s][scripts]" placeholder="%2$s">%3$s</textarea>
								  </td>
								  <td>
										<select name="rapidpress_options[js_disable_rules][%1$s][scope]" class="js-disable-scope">
											 <option value="entire_site" %4$s>%5$s</option>
											 <option value="front_page" %6$s>%7$s</option>
											 <option value="specific_pages" %8$s>%9$s</option>
										</select>
										<div class="checkbox-radio">
											 <label class="js-exclude-pages-wrapper" style="display:%10$s">
												  <input type="checkbox" name="rapidpress_options[js_disable_rules][%1$s][exclude_enabled]" class="js-exclude-enabled" value="1" %11$s> %12$s
											 </label>
										</div>
										<textarea cols="63" rows="3" name="rapidpress_options[js_disable_rules][%1$s][exclude_pages]" placeholder="%13$s" class="js-exclude-pages" style="display:%14$s">%15$s</textarea>
										<textarea cols="63" rows="3" name="rapidpress_options[js_disable_rules][%1$s][pages]" placeholder="%13$s" class="js-disable-pages" style="display:%16$s">%17$s</textarea>
								  </td>
								  <td>
										<div class="checkbox-btn status">
											 <label>
												  <input type="checkbox" name="rapidpress_options[js_disable_rules][%1$s][is_active]" value="1" %18$s>
												  <span>%19$s</span>
											 </label>
										</div>
										<button type="button" class="button remove-js-rule">%20$s</button>
								  </td>
							 </tr>',
								esc_attr($rapidpress_index),
								esc_attr__('Script URL or Handle (one per line)', 'rapidpress'),
								esc_textarea($rapidpress_scripts_text),
								selected($rapidpress_scope, 'entire_site', false),
								esc_html__('Entire Site', 'rapidpress'),
								selected($rapidpress_scope, 'front_page', false),
								esc_html__('Front Page', 'rapidpress'),
								selected($rapidpress_scope, 'specific_pages', false),
								esc_html__('Specific Pages', 'rapidpress'),
								esc_attr($rapidpress_scope === 'entire_site' ? 'inline-block' : 'none'),
								checked($rapidpress_exclude_enabled, true, false),
								esc_html__('Exclude pages?', 'rapidpress'),
								esc_attr('https://example.com/page1/\nhttps://example.com/page2/'),
								esc_attr($rapidpress_show_exclude_pages),
								// esc_attr($exclude_enabled ? 'block' : 'none'),
								esc_textarea($rapidpress_exclude_pages_text),
								esc_attr($rapidpress_scope === 'specific_pages' ? 'block' : 'none'),
								esc_textarea($rapidpress_pages_text),
								checked($rapidpress_is_active, true, false),
								esc_html__('Active', 'rapidpress'),
								esc_html__('Remove', 'rapidpress')
							);
						}

					?>
				</table>
				<button type="button" id="add-js-rule" class="button"><?php esc_html_e('Add JavaScript Rule', 'rapidpress'); ?></button>
			</div>
		</div>
	</div>
	<div class="accordion-item">
		<div class="accordion-header"><?php esc_html_e('CSS', 'rapidpress'); ?></div>
		<div class="accordion-content">
			<div class="rapidpress-card">
				<table class="form-table" id="css-asset-management">
					<tr class="table-head">
						<th style="width: 40%;"><?php esc_html_e('CSS URL or Handle (one per line)', 'rapidpress'); ?> <span class="dashicons dashicons-editor-help" data-title="<?php esc_attr_e('Enter the CSS file URL, partial filename, or the registered handle name of the CSS you want to disable. You can enter multiple URLs, partial filenames, or handles by separating them with a new line.', 'rapidpress'); ?>"></span></th>
						<th style="width: 40%;"><?php esc_html_e('Disable Scope', 'rapidpress'); ?> <span class="dashicons dashicons-editor-help" data-title="<?php esc_attr_e('Choose where to disable the CSS: "Entire Site" applies to all pages, "Front Page" only disables on your homepage, and "Specific Pages" lets you select individual URLs to disable on.', 'rapidpress'); ?>"></span></th>

						<th style="width: 12%;"><?php esc_html_e('Actions', 'rapidpress'); ?> <span class="dashicons dashicons-editor-help" data-title="<?php esc_attr_e('Enable, disable, or remove a CSS disable rule.', 'rapidpress'); ?>"></span></th>
					</tr>
					<?php
						$rapidpress_css_rules = RP_Options::get_option('css_disable_rules', array());
						foreach ($rapidpress_css_rules as $rapidpress_index => $rapidpress_rule) {
							$rapidpress_scope = isset($rapidpress_rule['scope']) ? $rapidpress_rule['scope'] : 'entire_site';
							$rapidpress_styles = isset($rapidpress_rule['styles']) ? $rapidpress_rule['styles'] : array();
							$rapidpress_styles_text = is_array($rapidpress_styles) ? implode("\n", $rapidpress_styles) : $rapidpress_styles;
							$rapidpress_is_active = isset($rapidpress_rule['is_active']) ? $rapidpress_rule['is_active'] : true;
							$rapidpress_exclude_enabled = isset($rapidpress_rule['exclude_enabled']) ? $rapidpress_rule['exclude_enabled'] : false;

							$rapidpress_show_exclude_pages = ($rapidpress_scope === 'entire_site' && $rapidpress_exclude_enabled) ? 'block' : 'none';

							$rapidpress_exclude_pages = isset($rapidpress_rule['exclude_pages']) ? $rapidpress_rule['exclude_pages'] : '';
							$rapidpress_exclude_pages_text = is_array($rapidpress_exclude_pages) ? implode("\n", $rapidpress_exclude_pages) : $rapidpress_exclude_pages;
							$rapidpress_pages = isset($rapidpress_rule['pages']) ? $rapidpress_rule['pages'] : array();

							$rapidpress_pages_text = is_array($rapidpress_pages) ? implode("\n", $rapidpress_pages) : $rapidpress_pages;

						printf(
							'<tr>
								  <td>
										<textarea cols="63" rows="3" name="rapidpress_options[css_disable_rules][%1$s][styles]" placeholder="%2$s">%3$s</textarea>
								  </td>
								  <td>
										<select name="rapidpress_options[css_disable_rules][%1$s][scope]" class="css-disable-scope">
											 <option value="entire_site" %4$s>%5$s</option>
											 <option value="front_page" %6$s>%7$s</option>
											 <option value="specific_pages" %8$s>%9$s</option>
										</select>
										<div class="checkbox-radio">
											 <label class="css-exclude-pages-wrapper" style="display:%10$s">
												  <input type="checkbox" name="rapidpress_options[css_disable_rules][%1$s][exclude_enabled]" class="css-exclude-enabled" value="1" %11$s> %12$s
											 </label>
										</div>
										<textarea cols="63" rows="3" name="rapidpress_options[css_disable_rules][%1$s][exclude_pages]" placeholder="%13$s" class="css-exclude-pages" style="display:%14$s">%15$s</textarea>
										<textarea cols="63" rows="3" name="rapidpress_options[css_disable_rules][%1$s][pages]" placeholder="%13$s" class="css-disable-pages" style="display:%16$s">%17$s</textarea>
								  </td>
								  <td>
										<div class="checkbox-btn status">
											 <label>
												  <input type="checkbox" name="rapidpress_options[css_disable_rules][%1$s][is_active]" value="1" %18$s>
												  <span>%19$s</span>
											 </label>
										</div>
										<button type="button" class="button remove-css-rule">%20$s</button>
								  </td>
							 </tr>',
								esc_attr($rapidpress_index),
								esc_attr__('CSS URL or Handle (one per line)', 'rapidpress'),
								esc_textarea($rapidpress_styles_text),
								selected($rapidpress_scope, 'entire_site', false),
								esc_html__('Entire Site', 'rapidpress'),
								selected($rapidpress_scope, 'front_page', false),
								esc_html__('Front Page', 'rapidpress'),
								selected($rapidpress_scope, 'specific_pages', false),
								esc_html__('Specific Pages', 'rapidpress'),
								esc_attr($rapidpress_scope === 'entire_site' ? 'inline-block' : 'none'),
								checked($rapidpress_exclude_enabled, true, false),
								esc_html__('Exclude pages?', 'rapidpress'),
								esc_attr('https://example.com/page1/\nhttps://example.com/page2/'),
								// esc_attr($exclude_enabled ? 'block' : 'none'),
								esc_attr($rapidpress_show_exclude_pages),
								esc_textarea($rapidpress_exclude_pages_text),
								esc_attr($rapidpress_scope === 'specific_pages' ? 'block' : 'none'),
								esc_textarea($rapidpress_pages_text),
								checked($rapidpress_is_active, true, false),
								esc_html__('Active', 'rapidpress'),
								esc_html__('Remove', 'rapidpress')
							);
					}

					?>
				</table>
				<button type="button" id="add-css-rule" class="button"><?php esc_html_e('Add CSS Rule', 'rapidpress'); ?></button>
			</div>
		</div>
	</div>
</div>
