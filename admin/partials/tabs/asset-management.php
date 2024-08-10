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
					<tr>
						<th style="width: 45%;">Script URL or Handle (one per line)</th>
						<th style="width: 45%;">Disable Scope</th>
						<th style="width: 10%;">Action</th>
					</tr>
					<?php
					$js_rules = get_option('rapidpress_js_disable_rules', array());
					foreach ($js_rules as $index => $rule) {
						echo '<tr>';
						echo '<td><textarea cols="65" rows="3" name="rapidpress_js_disable_rules[' . $index . '][scripts]" placeholder="Script URL or Handle (one per line)">' . esc_textarea(implode("\n", $rule['scripts'])) . '</textarea></td>';
						echo '<td>';
						echo '<select name="rapidpress_js_disable_rules[' . $index . '][scope]" class="js-disable-scope">';
						echo '<option value="entire_site" ' . selected($rule['scope'], 'entire_site', false) . '>Entire Site</option>';
						echo '<option value="front_page" ' . selected($rule['scope'], 'front_page', false) . '>Front Page</option>';
						echo '<option value="specific_pages" ' . selected($rule['scope'], 'specific_pages', false) . '>Specific Pages</option>';
						echo '</select>';
						echo '<textarea cols="65" rows="3" name="rapidpress_js_disable_rules[' . $index . '][pages]" placeholder="https://example.com/page1/&#10;https://example.com/page2/" class="js-disable-pages" style="display:' . ($rule['scope'] === 'specific_pages' ? 'block' : 'none') . ';">' . esc_textarea(implode("\n", $rule['pages'])) . '</textarea>';
						echo '</td>';
						echo '<td><button type="button" class="button remove-js-rule">Remove</button></td>';
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
					<tr>
						<th style="width: 45%;">CSS URL or Handle (one per line)</th>
						<th style="width: 45%;">Disable Scope</th>
						<th style="width: 10%;">Action</th>
					</tr>
					<?php
					$css_rules = get_option('rapidpress_css_disable_rules', array());
					foreach ($css_rules as $index => $rule) {
						$scope = isset($rule['scope']) ? $rule['scope'] : 'entire_site'; // Default to 'entire_site' if not set
						echo '<tr>';
						echo '<td><textarea cols="65" rows="3" name="rapidpress_css_disable_rules[' . $index . '][styles]" placeholder="CSS URL or Handle (one per line)">' . esc_textarea(implode("\n", isset($rule['styles']) ? $rule['styles'] : array())) . '</textarea></td>';
						echo '<td>';
						echo '<select name="rapidpress_css_disable_rules[' . $index . '][scope]" class="css-disable-scope">';
						echo '<option value="entire_site" ' . selected($scope, 'entire_site', false) . '>Entire Site</option>';
						echo '<option value="front_page" ' . selected($scope, 'front_page', false) . '>Front Page</option>';
						echo '<option value="specific_pages" ' . selected($scope, 'specific_pages', false) . '>Specific Pages</option>';
						echo '</select>';
						echo '<textarea cols="65" rows="3" name="rapidpress_css_disable_rules[' . $index . '][pages]" placeholder="https://example.com/page1/&#10;https://example.com/page2/" class="css-disable-pages" style="display:' . ($scope === 'specific_pages' ? 'block' : 'none') . ';">' . esc_textarea(implode("\n", isset($rule['pages']) ? $rule['pages'] : array())) . '</textarea>';
						echo '</td>';
						echo '<td><button type="button" class="button remove-css-rule">Remove</button></td>';
						echo '</tr>';
					}
					?>
				</table>
				<button type="button" id="add-css-rule" class="button">Add CSS Rule</button>
			</div>
		</div>
	</div>
</div>