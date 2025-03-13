<?php
// Ensure this file is being included by a parent file
if (!defined('ABSPATH')) exit; // Exit if accessed directly

use RapidPress\RP_Options;

?>

<div id="<?php echo esc_attr($tab_id); ?>" class="tab-pane">
	<h2 class="content-title"><span class="dashicons dashicons-hidden"></span> Disable Assets</h2>
	<p class="desc">Disable specific assets (CSS and JS) from loading on your site. This can help improve page load times and reduce server load by disabling assets that are not needed for that page.</p>
	<div class="accordion-item">
		<div class="accordion-header">JavaScript</div>
		<div class="accordion-content">
			<div class="rapidpress-card">
				<table class="form-table" id="js-asset-management">
					<tr class="table-head">
						<th style="width: 40%;">Script URL or Handle (one per line) <span class="dashicons dashicons-editor-help" data-title="Enter the script file URL, partial filename, or the registered handle name of the script you want to disable. You can enter multiple URLs, partial filenames, or handles by separating them with a new line."></span></th>
						<th style="width: 40%;">Disable Scope <span class="dashicons dashicons-editor-help" data-title="Choose where to disable the script: 'Entire Site' applies to all pages, 'Front Page' only disables on your homepage, and 'Specific Pages' lets you select individual URLs to disable on."></span></th>
						<th style="width: 12%;">Actions <span class="dashicons dashicons-editor-help" data-title="Enable, disable, or remove a script disable rule."></span></th>
					</tr>
					<?php
					$js_rules = RP_Options::get_option('js_disable_rules', array());
					// foreach ($js_rules as $index => $rule) {
					// 	$scripts = isset($rule['scripts']) ? $rule['scripts'] : array();
					// 	$scripts_text = is_array($scripts) ? implode("\n", $scripts) : $scripts;
					// 	$is_active = isset($rule['is_active']) ? $rule['is_active'] : true;
					// 	echo '<tr>';
					// 	echo '<td><textarea cols="63" rows="3" name="rapidpress_options[js_disable_rules][' . $index . '][scripts]" placeholder="Script URL or Handle (one per line)">' . esc_textarea($scripts_text) . '</textarea></td>';
					// 	echo '<td>';
					// 	echo '<select name="rapidpress_options[js_disable_rules][' . $index . '][scope]" class="js-disable-scope">';
					// 	echo '<option value="entire_site" ' . selected($rule['scope'], 'entire_site', false) . '>Entire Site</option>';
					// 	echo '<option value="front_page" ' . selected($rule['scope'], 'front_page', false) . '>Front Page</option>';
					// 	echo '<option value="specific_pages" ' . selected($rule['scope'], 'specific_pages', false) . '>Specific Pages</option>';
					// 	echo '</select>';
					// 	echo '<div class="checkbox-radio"><label class="js-exclude-pages-wrapper" style="display:' . ($rule['scope'] === 'entire_site' ? 'inline-block' : 'none') . '"><input type="checkbox" name="rapidpress_options[js_disable_rules][' . $index . '][exclude_enabled]" class="js-exclude-enabled" value="1" ' . checked(isset($rule['exclude_enabled']) ? $rule['exclude_enabled'] : false, true, false) . '> Exclude pages?</label></div>';
					// 	$exclude_pages = isset($rule['exclude_pages']) ? $rule['exclude_pages'] : '';
					// 	$exclude_pages_text = is_array($exclude_pages) ? implode("\n", $exclude_pages) : $exclude_pages;
					// 	echo '<textarea cols="63" rows="3" name="rapidpress_options[js_disable_rules][' . $index . '][exclude_pages]" placeholder="https://example.com/page1/&#10;https://example.com/page2/" class="js-exclude-pages" style="display:' . (isset($rule['exclude_enabled']) && $rule['exclude_enabled'] ? 'block' : 'none') . ';">' . esc_textarea($exclude_pages_text) . '</textarea>';
					// 	$pages = isset($rule['pages']) ? $rule['pages'] : array();
					// 	$pages_text = is_array($pages) ? implode("\n", $pages) : $pages;
					// 	echo '<textarea cols="63" rows="3" name="rapidpress_options[js_disable_rules][' . $index . '][pages]" placeholder="https://example.com/page1/&#10;https://example.com/page2/" class="js-disable-pages" style="display:' . ($rule['scope'] === 'specific_pages' ? 'block' : 'none') . ';">' . esc_textarea($pages_text) . '</textarea>';
					// 	echo '</td>';
					// 	echo '<td>';
					// 	echo '<div class="checkbox-btn"><label><input type="checkbox" name="rapidpress_options[js_disable_rules][' . $index . '][is_active]" value="1" ' . checked($is_active, true, false) . '><span>Active</span></label></div>';
					// 	echo '<button type="button" class="button remove-js-rule">Remove</button>';
					// 	echo '</td>';
					// 	echo '</tr>';
					// }
					foreach ($js_rules as $index => $rule) {
						$scripts = isset($rule['scripts']) ? $rule['scripts'] : array();
						$scripts_text = is_array($scripts) ? implode("\n", $scripts) : $scripts;
						$is_active = isset($rule['is_active']) ? $rule['is_active'] : true;
						$scope = isset($rule['scope']) ? $rule['scope'] : 'entire_site';

						$exclude_enabled = isset($rule['exclude_enabled']) ? $rule['exclude_enabled'] : false;

						$show_exclude_pages = ($scope === 'entire_site' && $exclude_enabled) ? 'block' : 'none';

						$exclude_pages = isset($rule['exclude_pages']) ? $rule['exclude_pages'] : '';
						$exclude_pages_text = is_array($exclude_pages) ? implode("\n", $exclude_pages) : $exclude_pages;
						$pages = isset($rule['pages']) ? $rule['pages'] : array();
						$pages_text = is_array($pages) ? implode("\n", $pages) : $pages;

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
							esc_attr($index),
							esc_attr__('Script URL or Handle (one per line)', 'rapidpress'),
							esc_textarea($scripts_text),
							selected($scope, 'entire_site', false),
							esc_html__('Entire Site', 'rapidpress'),
							selected($scope, 'front_page', false),
							esc_html__('Front Page', 'rapidpress'),
							selected($scope, 'specific_pages', false),
							esc_html__('Specific Pages', 'rapidpress'),
							esc_attr($scope === 'entire_site' ? 'inline-block' : 'none'),
							checked($exclude_enabled, true, false),
							esc_html__('Exclude pages?', 'rapidpress'),
							esc_attr('https://example.com/page1/\nhttps://example.com/page2/'),
							esc_attr($show_exclude_pages),
							// esc_attr($exclude_enabled ? 'block' : 'none'),
							esc_textarea($exclude_pages_text),
							esc_attr($scope === 'specific_pages' ? 'block' : 'none'),
							esc_textarea($pages_text),
							checked($is_active, true, false),
							esc_html__('Active', 'rapidpress'),
							esc_html__('Remove', 'rapidpress')
						);
					}

					?>
				</table>
				<button type="button" id="add-js-rule" class="button">Add JavaScript Rule</button>
			</div>
		</div>
	</div>
	<div class="accordion-item">
		<div class="accordion-header">CSS</div>
		<div class="accordion-content">
			<div class="rapidpress-card">
				<table class="form-table" id="css-asset-management">
					<tr class="table-head">
						<th style="width: 40%;">CSS URL or Handle (one per line) <span class="dashicons dashicons-editor-help" data-title="Enter the CSS file URL, partial filename, or the registered handle name of the CSS you want to disable. You can enter multiple URLs, partial filenames, or handles by separating them with a new line."></span></th>
						<th style="width: 40%;">Disable Scope <span class="dashicons dashicons-editor-help" data-title="Choose where to disable the CSS: 'Entire Site' applies to all pages, 'Front Page' only disables on your homepage, and 'Specific Pages' lets you select individual URLs to disable on."></span></th>

						<th style="width: 12%;">Actions <span class="dashicons dashicons-editor-help" data-title="Enable, disable, or remove a CSS disable rule."></span></th>
					</tr>
					<?php
					$css_rules = RP_Options::get_option('css_disable_rules', array());
					// foreach ($css_rules as $index => $rule) {
					// 	$scope = isset($rule['scope']) ? $rule['scope'] : 'entire_site';
					// 	$styles = isset($rule['styles']) ? $rule['styles'] : array();
					// 	$styles_text = is_array($styles) ? implode("\n", $styles) : $styles;
					// 	$is_active = isset($rule['is_active']) ? $rule['is_active'] : true;
					// 	echo '<tr>';
					// 	echo '<td><textarea cols="63" rows="3" name="rapidpress_options[css_disable_rules][' . $index . '][styles]" placeholder="CSS URL or Handle (one per line)">' . esc_textarea($styles_text) . '</textarea></td>';
					// 	echo '<td>';
					// 	echo '<select name="rapidpress_options[css_disable_rules][' . $index . '][scope]" class="css-disable-scope">';
					// 	echo '<option value="entire_site" ' . selected($scope, 'entire_site', false) . '>Entire Site</option>';
					// 	echo '<option value="front_page" ' . selected($scope, 'front_page', false) . '>Front Page</option>';
					// 	echo '<option value="specific_pages" ' . selected($scope, 'specific_pages', false) . '>Specific Pages</option>';
					// 	echo '</select>';

					// 	echo '<div class="checkbox-radio"><label class="css-exclude-pages-wrapper" style="display:' . ($scope === 'entire_site' ? 'inline-block' : 'none') . ';"><input type="checkbox" name="rapidpress_options[css_disable_rules][' . $index . '][exclude_enabled]" class="css-exclude-enabled" value="1" ' . checked(isset($rule['exclude_enabled']) ? $rule['exclude_enabled'] : false, true, false) . '> Exclude pages?</label></div>';
					// 	$exclude_pages = isset($rule['exclude_pages']) ? $rule['exclude_pages'] : '';
					// 	$exclude_pages_text = is_array($exclude_pages) ? implode("\n", $exclude_pages) : $exclude_pages;
					// 	echo '<textarea cols="63" rows="3" name="rapidpress_options[css_disable_rules][' . $index . '][exclude_pages]" placeholder="https://example.com/page1/&#10;https://example.com/page2/" class="css-exclude-pages" style="display:' . (isset($rule['exclude_enabled']) && $rule['exclude_enabled'] ? 'block' : 'none') . ';">' . esc_textarea($exclude_pages_text) . '</textarea>';
					// 	$pages = isset($rule['pages']) ? $rule['pages'] : array();
					// 	$pages_text = is_array($pages) ? implode("\n", $pages) : $pages;
					// 	echo '<textarea cols="63" rows="3" name="rapidpress_options[css_disable_rules][' . $index . '][pages]" placeholder="https://example.com/page1/&#10;https://example.com/page2/" class="css-disable-pages" style="display:' . ($scope === 'specific_pages' ? 'block' : 'none') . ';">' . esc_textarea($pages_text) . '</textarea>';
					// 	echo '</td>';
					// 	echo '<td>';
					// 	echo '<div class="checkbox-btn"><label><input type="checkbox" name="rapidpress_options[css_disable_rules][' . $index . '][is_active]" value="1" ' . checked($is_active, true, false) . '> <span>Active</span></label></div>';
					// 	echo '<button type="button" class="button remove-css-rule">Remove</button>';
					// 	echo '</td>';
					// 	echo '</tr>';
					// }
					foreach ($css_rules as $index => $rule) {
						$scope = isset($rule['scope']) ? $rule['scope'] : 'entire_site';
						$styles = isset($rule['styles']) ? $rule['styles'] : array();
						$styles_text = is_array($styles) ? implode("\n", $styles) : $styles;
						$is_active = isset($rule['is_active']) ? $rule['is_active'] : true;
						$exclude_enabled = isset($rule['exclude_enabled']) ? $rule['exclude_enabled'] : false;

						$show_exclude_pages = ($scope === 'entire_site' && $exclude_enabled) ? 'block' : 'none';

						$exclude_pages = isset($rule['exclude_pages']) ? $rule['exclude_pages'] : '';
						$exclude_pages_text = is_array($exclude_pages) ? implode("\n", $exclude_pages) : $exclude_pages;
						$pages = isset($rule['pages']) ? $rule['pages'] : array();

						$pages_text = is_array($pages) ? implode("\n", $pages) : $pages;

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
							esc_attr($index),
							esc_attr__('CSS URL or Handle (one per line)', 'rapidpress'),
							esc_textarea($styles_text),
							selected($scope, 'entire_site', false),
							esc_html__('Entire Site', 'rapidpress'),
							selected($scope, 'front_page', false),
							esc_html__('Front Page', 'rapidpress'),
							selected($scope, 'specific_pages', false),
							esc_html__('Specific Pages', 'rapidpress'),
							esc_attr($scope === 'entire_site' ? 'inline-block' : 'none'),
							checked($exclude_enabled, true, false),
							esc_html__('Exclude pages?', 'rapidpress'),
							esc_attr('https://example.com/page1/\nhttps://example.com/page2/'),
							// esc_attr($exclude_enabled ? 'block' : 'none'),
							esc_attr($show_exclude_pages),
							esc_textarea($exclude_pages_text),
							esc_attr($scope === 'specific_pages' ? 'block' : 'none'),
							esc_textarea($pages_text),
							checked($is_active, true, false),
							esc_html__('Active', 'rapidpress'),
							esc_html__('Remove', 'rapidpress')
						);
					}

					?>
				</table>
				<button type="button" id="add-css-rule" class="button">Add CSS Rule</button>
			</div>
		</div>
	</div>
</div>