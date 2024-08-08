<?php
// Ensure this file is being included by a parent file
if (!defined('ABSPATH')) exit; // Exit if accessed directly

?>

<div id="asset-management" class="tab-pane">
	<h2 class="content-title">Asset Management</h2>
	<div class="accordion-item">
		<div class="accordion-header active">JavaScript</div>
		<div class="accordion-content active">
			<div class="rapidpress-card">
				<table class="form-table" id="js-asset-management">
					<tr>
						<th>Script Handle or URL</th>
						<th>Disable on Pages (one URL per line)</th>
						<th>Actions</th>
					</tr>
					<?php
					$js_rules = get_option('rapidpress_js_disable_rules', array());
					foreach ($js_rules as $index => $rule) {
						echo '<tr>';
						echo '<td><input type="text" name="rapidpress_js_disable_rules[' . $index . '][handle]" value="' . esc_attr($rule['handle']) . '" placeholder="e.g., comment-reply or full URL" />';
						echo '<input type="text" name="rapidpress_js_disable_rules[' . $index . '][url]" value="' . (isset($rule['url']) ? esc_attr($rule['url']) : '') . '" placeholder="Full script URL (optional)" /></td>';
						echo '<td><textarea name="rapidpress_js_disable_rules[' . $index . '][pages]" placeholder="https://example.com/page1/&#10;https://example.com/page2/">' . esc_textarea($rule['pages']) . '</textarea></td>';
						echo '<td><button type="button" class="button remove-js-rule">Remove</button></td>';
						echo '</tr>';
					}
					?>
				</table>
				<button type="button" id="add-js-rule" class="button">Add JavaScript Rule</button>
			</div>
		</div>
	</div>
	<!-- ... (keep the existing CSS section) ... -->
</div>