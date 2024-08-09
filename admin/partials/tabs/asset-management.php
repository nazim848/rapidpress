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
<th style="width: 45%;">Script URL or Handle (one URL per line)</th>
						<th style="width: 45%;">Disable on Pages (one URL per line)</th>
						<th style="width: 10%;">Action</th>
					</tr>
					<?php
					$js_rules = get_option('rapidpress_js_disable_rules', array());
					foreach ($js_rules as $index => $rule) {
						echo '<tr>';
						echo '<td><textarea cols="65" rows="3" name="rapidpress_js_disable_rules[' . $index . '][scripts]" placeholder="Script URL or Handle">' . esc_textarea(implode("\n", $rule['scripts'])) . '</textarea></td>';
						echo '<td><textarea cols="65" rows="3" name="rapidpress_js_disable_rules[' . $index . '][pages]" placeholder="https://example.com/page1/&#10;https://example.com/page2/">' . esc_textarea(implode("\n", $rule['pages'])) . '</textarea></td>';
						echo '<td><button type="button" class="button remove-js-rule">Remove</button></td>';
						echo '</tr>';
					}
					?>
				</table>
				<button type="button" id="add-js-rule" class="button">Add JavaScript Rule</button>
			</div>
		</div>
	</div>
</div>