<?php
// Ensure this file is being included by a parent file
if (!defined('ABSPATH')) exit; // Exit if accessed directly

?>

<div id="file-optimization" class="tab-pane">
	<h2>File Optimization Settings</h2>
	<form method="post" action="options.php">
		<?php
		settings_fields('rapidpress_options');
		do_settings_sections('rapidpress');
		?>
		<div class="rapidpress-card">
			<input type="hidden" name="rapidpress_active_tab" id="rapidpress_active_tab" value="#minification">
			<table class="form-table">
				<tr valign="top">
					<th scope="row">HTML Minification</th>
					<td>
						<label>
							<input type="checkbox" name="rapidpress_html_minify" value="1" <?php checked(1, get_option('rapidpress_html_minify'), true); ?> />
							Enable HTML minification
						</label>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">CSS Minification</th>
					<td>
						<label>
							<input type="checkbox" name="rapidpress_css_minify" value="1" <?php checked(1, get_option('rapidpress_css_minify'), true); ?> />
							Enable CSS minification (inline styles)
						</label>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Combine CSS Files</th>
					<td>
						<label>
							<input type="checkbox" name="rapidpress_combine_css" id="rapidpress_combine_css" value="1" <?php checked(1, get_option('rapidpress_combine_css'), true); ?> />
							Enable CSS file combination
						</label>
					</td>
				</tr>
				<tr valign="top" id="rapidpress_css_exclusions_row">
					<th scope="row">CSS Exclusions</th>
					<td>
						<textarea name="rapidpress_css_exclusions" rows="4" cols="50" placeholder="Enter one CSS file URL per line"><?php echo esc_textarea(get_option('rapidpress_css_exclusions', '')); ?></textarea>
						<p class="description">Enter the URLs of CSS files you want to exclude from combination, one per line.</p>
					</td>
				</tr>
			</table>
		</div>
		<?php submit_button(); ?>
	</form>
</div>