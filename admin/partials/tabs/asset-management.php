<?php
// Ensure this file is being included by a parent file
if (!defined('ABSPATH')) exit; // Exit if accessed directly

?>

<div id="asset-management" class="tab-pane">
	<h2 class="content-title">Asset Management</h2>
	<div class="accordion-item">
		<div class="accordion-header">JavaScript</div>
		<div class="accordion-content">
			<div class="rapidpress-card">
				<table class="form-table" id="js-asset-management">
					<tr class="table-head">
						<th style="width: 40%;">Script URL or Handle (one per line)</th>
						<th style="width: 40%;">Disable Scope</th>
						<th style="width: 12%;">Status / Action</th>
					</tr>
					<?php
					$js_rules = get_option('rapidpress_js_disable_rules', array());
					foreach ($js_rules as $index => $rule) {
						$scripts = isset($rule['scripts']) ? $rule['scripts'] : array();
						$scripts_text = is_array($scripts) ? implode("\n", $scripts) : $scripts;
						$is_active = isset($rule['is_active']) ? $rule['is_active'] : true;
						echo '<tr>';
						echo '<td><textarea cols="63" rows="3" name="rapidpress_js_disable_rules[' . $index . '][scripts]" placeholder="Script URL or Handle (one per line)">' . esc_textarea($scripts_text) . '</textarea></td>';
						echo '<td>';
						echo '<select name="rapidpress_js_disable_rules[' . $index . '][scope]" class="js-disable-scope">';
						echo '<option value="entire_site" ' . selected($rule['scope'], 'entire_site', false) . '>Entire Site</option>';
						echo '<option value="front_page" ' . selected($rule['scope'], 'front_page', false) . '>Front Page</option>';
						echo '<option value="specific_pages" ' . selected($rule['scope'], 'specific_pages', false) . '>Specific Pages</option>';
						echo '</select>';
						echo '<label class="js-exclude-pages-wrapper" style="display:' . ($rule['scope'] === 'entire_site' ? 'inline-block' : 'none') . '; margin: 0 0 10px 10px;"><input type="checkbox" name="rapidpress_js_disable_rules[' . $index . '][exclude_enabled]" class="js-exclude-enabled" value="1" ' . checked(isset($rule['exclude_enabled']) ? $rule['exclude_enabled'] : false, true, false) . '> Exclude pages?</label>';
						$exclude_pages = isset($rule['exclude_pages']) ? $rule['exclude_pages'] : '';
						$exclude_pages_text = is_array($exclude_pages) ? implode("\n", $exclude_pages) : $exclude_pages;
						echo '<textarea cols="63" rows="3" name="rapidpress_js_disable_rules[' . $index . '][exclude_pages]" placeholder="https://example.com/page1/&#10;https://example.com/page2/" class="js-exclude-pages" style="display:' . (isset($rule['exclude_enabled']) && $rule['exclude_enabled'] ? 'block' : 'none') . ';">' . esc_textarea($exclude_pages_text) . '</textarea>';
						$pages = isset($rule['pages']) ? $rule['pages'] : array();
						$pages_text = is_array($pages) ? implode("\n", $pages) : $pages;
						echo '<textarea cols="63" rows="3" name="rapidpress_js_disable_rules[' . $index . '][pages]" placeholder="https://example.com/page1/&#10;https://example.com/page2/" class="js-disable-pages" style="display:' . ($rule['scope'] === 'specific_pages' ? 'block' : 'none') . ';">' . esc_textarea($pages_text) . '</textarea>';
						echo '</td>';
						echo '<td>';
						echo '<div class="checkbox-btn"><label><input type="checkbox" name="rapidpress_js_disable_rules[' . $index . '][is_active]" value="1" ' . checked($is_active, true, false) . '><span>Active</span></label></div>';
						echo '<button type="button" class="button remove-js-rule">Remove</button>';
						echo '</td>';
						echo '</tr>';
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
						<th style="width: 40%;">CSS URL or Handle (one per line)</th>
						<th style="width: 40%;">Disable Scope</th>
						<th style="width: 12%;">Status / Action</th>
					</tr>
					<?php
					$css_rules = get_option('rapidpress_css_disable_rules', array());
					foreach ($css_rules as $index => $rule) {
						$scope = isset($rule['scope']) ? $rule['scope'] : 'entire_site';
						$styles = isset($rule['styles']) ? $rule['styles'] : array();
						$styles_text = is_array($styles) ? implode("\n", $styles) : $styles;
						$is_active = isset($rule['is_active']) ? $rule['is_active'] : true;
						echo '<tr>';
						echo '<td><textarea cols="63" rows="3" name="rapidpress_css_disable_rules[' . $index . '][styles]" placeholder="CSS URL or Handle (one per line)">' . esc_textarea($styles_text) . '</textarea></td>';
						echo '<td>';
						echo '<select name="rapidpress_css_disable_rules[' . $index . '][scope]" class="css-disable-scope">';
						echo '<option value="entire_site" ' . selected($scope, 'entire_site', false) . '>Entire Site</option>';
						echo '<option value="front_page" ' . selected($scope, 'front_page', false) . '>Front Page</option>';
						echo '<option value="specific_pages" ' . selected($scope, 'specific_pages', false) . '>Specific Pages</option>';
						echo '</select>';

						echo '<label class="css-exclude-pages-wrapper" style="display:' . ($scope === 'entire_site' ? 'inline-block' : 'none') . ';"><input type="checkbox" name="rapidpress_css_disable_rules[' . $index . '][exclude_enabled]" class="css-exclude-enabled" value="1" ' . checked(isset($rule['exclude_enabled']) ? $rule['exclude_enabled'] : false, true, false) . '> Exclude pages?</label>';
						$exclude_pages = isset($rule['exclude_pages']) ? $rule['exclude_pages'] : '';
						$exclude_pages_text = is_array($exclude_pages) ? implode("\n", $exclude_pages) : $exclude_pages;
						echo '<textarea cols="63" rows="3" name="rapidpress_css_disable_rules[' . $index . '][exclude_pages]" placeholder="https://example.com/page1/&#10;https://example.com/page2/" class="css-exclude-pages" style="display:' . (isset($rule['exclude_enabled']) && $rule['exclude_enabled'] ? 'block' : 'none') . ';">' . esc_textarea($exclude_pages_text) . '</textarea>';
						$pages = isset($rule['pages']) ? $rule['pages'] : array();
						$pages_text = is_array($pages) ? implode("\n", $pages) : $pages;
						echo '<textarea cols="63" rows="3" name="rapidpress_css_disable_rules[' . $index . '][pages]" placeholder="https://example.com/page1/&#10;https://example.com/page2/" class="css-disable-pages" style="display:' . ($scope === 'specific_pages' ? 'block' : 'none') . ';">' . esc_textarea($pages_text) . '</textarea>';
						echo '</td>';
						echo '<td>';
						echo '<div class="checkbox-btn"><label><input type="checkbox" name="rapidpress_css_disable_rules[' . $index . '][is_active]" value="1" ' . checked($is_active, true, false) . '> <span>Active</span></label></div>';
						echo '<button type="button" class="button remove-css-rule">Remove</button>';
						echo '</td>';
						echo '</tr>';
					}
					?>
				</table>
				<button type="button" id="add-css-rule" class="button">Add CSS Rule</button>
			</div>
		</div>
	</div>
</div>