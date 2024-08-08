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
						<th>File URL/Handle</th>
						<th>Disable on Pages</th>
						<th>Actions</th>
					</tr>
					<!-- JS rows will be dynamically added here -->
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
						<th>File URL/Handle</th>
						<th>Disable on Pages</th>
						<th>Actions</th>
					</tr>
					<!-- CSS rows will be dynamically added here -->
				</table>
				<button type="button" id="add-css-rule" class="button">Add CSS Rule</button>
			</div>
		</div>
	</div>
</div>