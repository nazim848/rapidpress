<?php
// Ensure this file is being included by a parent file
if (!defined('ABSPATH')) exit; // Exit if accessed directly

?>

<div id="file-optimization" class="tab-pane">
	<h2 class="content-title">File Optimization</h2>
	<div class="rapidpress-card">
		<input type="hidden" name="rapidpress_active_tab" id="rapidpress_active_tab" value="#minification">

		<table class="form-table">
			<tr valign="top">
				<th scope="row">Optimization Scope</th>
				<td>
					<select name="rapidpress_optimization_scope" id="rapidpress_optimization_scope">
						<option value="entire_site" <?php selected(get_option('rapidpress_optimization_scope'), 'entire_site'); ?>>Entire Site</option>
						<option value="front_page" <?php selected(get_option('rapidpress_optimization_scope'), 'front_page'); ?>>Front Page</option>
						<option value="specific_pages" <?php selected(get_option('rapidpress_optimization_scope'), 'specific_pages'); ?>>Specific Pages</option>
					</select>
					<label style="margin-left: 10px;" id="rapidpress_enable_scope_exclusions_label">
						<input type="checkbox" name="rapidpress_enable_scope_exclusions" id="rapidpress_enable_scope_exclusions" value="1" <?php checked(get_option('rapidpress_enable_scope_exclusions'), '1'); ?> />
						Exclude pages?
					</label>
				</td>
			</tr>
			<tr valign="top" id="rapidpress_specific_pages_row" style="display: none;">
				<th scope="row">Page Inclusions</th>
				<td>
					<textarea name="rapidpress_optimized_pages" id="rapidpress_optimized_pages" rows="3" cols="70" placeholder="Enter one page URL per line"><?php echo esc_textarea(get_option('rapidpress_optimized_pages', '')); ?></textarea>
					<p class="description">Enter the URLs of the pages you want to optimize, one URL per line.</p>
				</td>
			</tr>
			<tr valign="top" id="rapidpress_excluded_pages_row" style="display: none;">
				<th scope="row">Page Exclusions</th>
				<td>
					<textarea name="rapidpress_excluded_pages" id="rapidpress_excluded_pages" rows="3" cols="70" placeholder="Enter one page URL per line"><?php echo esc_textarea(get_option('rapidpress_excluded_pages', '')); ?></textarea>
					<p class="description">Enter the URLs of the pages you want to exclude from optimization, one URL per line.</p>
				</td>
			</tr>
		</table>
		<hr>
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
					<!-- <div id="rapidpress_css_exclusions_button" style="margin-top: 15px">
						<button class="button button-small button-secondary">CSS Exclusions</button>
					</div> -->
					<div class="checkbox-btn" id="rapidpress_enable_css_exclusions_btn">
						<label>
							<input type="checkbox" name="rapidpress_enable_css_exclusions" id="rapidpress_enable_css_exclusions" value="1" <?php checked(1, get_option('rapidpress_enable_css_exclusions'), true); ?> />
							<span>Enable CSS Exclusions</span>
						</label>
					</div>
				</td>
				</td>
			</tr>
			<!-- <tr valign="top" id="rapidpress_css_exclusions_button">
				<th scope="row"></th>
				<td><button class="button button-small button-secondary">Add Exclusions</button></td>
			</tr> -->
			<tr valign="top" id="rapidpress_css_exclusions_row" style="display: none;">
				<th scope="row">CSS Exclusions</th>
				<td>
					<textarea name="rapidpress_css_exclusions" rows="3" cols="70" placeholder="Enter one CSS file URL per line"><?php echo esc_textarea(get_option('rapidpress_css_exclusions', '')); ?></textarea>
					<p class="description">Enter the URLs of CSS files you want to exclude from combination, one per line.</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Minify JavaScript</th>
				<td>
					<label>
						<input type="checkbox" name="rapidpress_js_minify" value="1" <?php checked(1, get_option('rapidpress_js_minify'), true); ?> />
						Enable JavaScript minification
					</label>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Defer JavaScript</th>
				<td>
					<label>
						<input type="checkbox" name="rapidpress_js_defer" id="rapidpress_js_defer" value="1" <?php checked(1, get_option('rapidpress_js_defer'), true); ?> />
						Enable JavaScript deferring
					</label>
				</td>
			</tr>
			<tr valign="top" id="rapidpress_js_defer_exclusions_row">
				<th scope="row">JavaScript Defer Exclusions</th>
				<td>
					<textarea name="rapidpress_js_defer_exclusions" rows="3" cols="70" placeholder="Enter one JavaScript file URL per line"><?php echo esc_textarea(get_option('rapidpress_js_defer_exclusions', '')); ?></textarea>
					<p class="description">Enter the URLs of JavaScript files you want to exclude from deferring, one per line.</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Delay JavaScript Execution</th>
				<td>
					<label>
						<input type="checkbox" name="rapidpress_js_delay" id="rapidpress_js_delay" value="1" <?php checked(1, get_option('rapidpress_js_delay'), true); ?> />
						Enable JavaScript delay
					</label>
				</td>
			</tr>
			<tr valign="top" id="rapidpress_js_delay_options" style="display: none;">
				<th scope="row">Delay Duration</th>
				<td>
					<select name="rapidpress_js_delay_duration">
						<option value="1" <?php selected(get_option('rapidpress_js_delay_duration'), '1'); ?>>1 second</option>
						<option value="2" <?php selected(get_option('rapidpress_js_delay_duration'), '2'); ?>>2 seconds</option>
						<option value="3" <?php selected(get_option('rapidpress_js_delay_duration'), '3'); ?>>3 seconds</option>
						<option value="interaction" <?php selected(get_option('rapidpress_js_delay_duration'), 'interaction'); ?>>Until user interaction</option>
					</select>
				</td>
			</tr>
			<tr valign="top" id="rapidpress_js_delay_exclusions_row" style="display: none;">
				<th scope="row">JS Delay Exclusions</th>
				<td>
					<textarea name="rapidpress_js_delay_exclusions" rows="3" cols="70" placeholder="Enter one JavaScript file URL per line"><?php echo esc_textarea(get_option('rapidpress_js_delay_exclusions', '')); ?></textarea>
					<p class="description">Enter the URLs of JavaScript files you want to exclude from delay, one per line.</p>
				</td>
			</tr>
		</table>
	</div>
</div>