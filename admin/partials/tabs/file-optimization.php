<?php
// Ensure this file is being included by a parent file
if (!defined('ABSPATH')) exit; // Exit if accessed directly

use RapidPress\RP_Options;

?>

<div id="<?php echo esc_attr($tab_id); ?>" class="tab-pane">
	<h2 class="content-title"><span class="dashicons dashicons-media-code"></span> File Optimization</h2>
	<div class="rapidpress-card">
		<!-- <input type="hidden" name="rapidpress_active_tab" id="rapidpress_active_tab" value="#minification"> -->
		<table class="form-table">
			<tr valign="top">
				<th scope="row">Optimization Scope</th>
				<td>
					<select name="rapidpress_options[optimization_scope]" id="rapidpress_optimization_scope">
						<option value="entire_site" <?php selected(RP_Options::get_option('optimization_scope'), 'entire_site'); ?>>Entire Site</option>
						<option value="front_page" <?php selected(RP_Options::get_option('optimization_scope'), 'front_page'); ?>>Front Page</option>
						<option value="specific_pages" <?php selected(RP_Options::get_option('optimization_scope'), 'specific_pages'); ?>>Specific Pages</option>
					</select>
					<span class="dashicons dashicons-editor-help" data-title="Choose where to apply file optimization: 'Entire Site' applies to all pages, 'Front Page' only optimizes your homepage, and 'Specific Pages' lets you select individual URLs to optimize."></span>
					<div class="checkbox-radio" style="margin-left: 10px;" id="rapidpress_enable_scope_exclusions_label">
						<label>
							<input type="checkbox" name="rapidpress_options[enable_optimization_scope_exclusions]" id="rapidpress_enable_scope_exclusions" value="1" <?php checked(RP_Options::get_option('enable_optimization_scope_exclusions'), '1'); ?> />
							Exclude pages?
						</label>
					</div>
				</td>
			</tr>
			<tr valign="top" id="rapidpress_specific_pages_row" style="display: none;">
				<th scope="row">Specific Pages</th>
				<td>
					<textarea name="rapidpress_options[optimized_pages]" id="rapidpress_optimized_pages" rows="3" cols="70" placeholder="Enter one page URL per line"><?php echo esc_textarea(RP_Options::get_option('optimized_pages', '')); ?></textarea>
					<p class="description">Enter the URLs of the pages you want to optimize, one URL per line.</p>
				</td>
			</tr>
			<tr valign="top" id="rapidpress_optimization_excluded_pages_row" style="display: none;">
				<th scope="row">Page Exclusions</th>
				<td>
					<textarea name="rapidpress_options[optimization_excluded_pages]" id="rapidpress_optimization_excluded_pages" rows="3" cols="70" placeholder="Enter one page URL per line"><?php echo esc_textarea(RP_Options::get_option('optimization_excluded_pages', '')); ?></textarea>
					<p class="description">Enter the URLs of the pages you want to exclude from optimization, one URL per line.</p>
				</td>
			</tr>
		</table>
		<hr>
		<table class="form-table">
			<tr valign="top">
				<th scope="row">HTML Minification</th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[html_minify]" value="1" <?php checked(1, RP_Options::get_option('html_minify'), true); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="Removes unnecessary whitespace, comments, and other redundant HTML code to reduce file size and improve page load times. This can reduce HTML file size by 10-20%."></span>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">CSS Minification</th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[css_minify]" value="1" <?php checked(1, RP_Options::get_option('css_minify'), true); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="Compresses CSS files by removing whitespace, comments, and optimizing code syntax. This reduces file size and improves load times without affecting how styles are rendered."></span>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Combine CSS Files</th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[combine_css]" id="rapidpress_combine_css" value="1" <?php checked(1, RP_Options::get_option('combine_css'), true); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="Merges multiple CSS files into a single file, reducing HTTP requests and improving page load speed. Particularly effective for sites with many small stylesheets."></span>
					</div>
					<div class="checkbox-btn" id="rapidpress_enable_combine_css_exclusions_btn">
						<label>
							<input type="checkbox" name="rapidpress_options[enable_combine_css_exclusions]" id="rapidpress_enable_combine_css_exclusions" value="1" <?php checked(1, RP_Options::get_option('enable_combine_css_exclusions'), true); ?> />
							<span>Enable CSS Exclusions</span>
						</label>
					</div>
				</td>
			</tr>
			<tr valign="top" id="rapidpress_combine_css_exclusions_row" style="display: none;">
				<th scope="row">CSS Exclusions</th>
				<td>
					<textarea name="rapidpress_options[combine_css_exclusions]" rows="3" cols="70" placeholder="Enter CSS file URL/Handle per line"><?php echo esc_textarea(RP_Options::get_option('combine_css_exclusions', '')); ?></textarea>
					<p class="description">Enter the CSS file URL, partial filename, or the registered handle name to exclude from combination. One per line.</p>
					<p class="description"><small>Examples:</small></p>
					<ul class="description" style="margin-top: 0; list-style-type: disc; padding-left: 20px;">
						<li><small><strong>Handle name:</strong> <code>wp-block-library</code> or <code>generate-style</code> (exact handle name, no slashes or dots)</small></li>
						<li><small><strong>Partial filename:</strong> <code>style.css</code> (matches any URL containing this string)</small></li>
						<li><small><strong>Full URL:</strong> <code>https://example.com/style.css</code></small></li>
					</ul>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Minify JavaScript</th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[js_minify]" value="1" <?php checked(1, RP_Options::get_option('js_minify'), true); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="Reduces JavaScript file size by removing comments, whitespace, and unnecessary characters. This optimization can decrease file size by 30-50% without affecting functionality."></span>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Defer JavaScript</th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[js_defer]" id="rapidpress_js_defer" value="1" <?php checked(1, RP_Options::get_option('js_defer'), true); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="Adds 'defer' attribute to script tags, allowing JavaScript to load without blocking page rendering. This improves initial page load speed and Core Web Vitals scores while ensuring scripts execute in the correct order."></span>
					</div>
					<div class="checkbox-btn" id="rapidpress_enable_js_defer_exclusions_btn">
						<label>
							<input type="checkbox" name="rapidpress_options[enable_js_defer_exclusions]" id="rapidpress_enable_js_defer_exclusions" value="1" <?php checked(1, RP_Options::get_option('enable_js_defer_exclusions'), true); ?> />
							<span>Enable JS Defer Exclusions</span>
						</label>
					</div>
				</td>
			</tr>
			<tr valign="top" id="rapidpress_js_defer_exclusions_row" style="display: none;">
				<th scope="row">JavaScript Defer Exclusions</th>
				<td>
					<textarea name="rapidpress_options[js_defer_exclusions]" rows="3" cols="70" placeholder="Enter JavaScript file URL/Handle per line"><?php echo esc_textarea(RP_Options::get_option('js_defer_exclusions', '')); ?></textarea>
					<p class="description">Enter the JavaScript file URL, partial filename, or the registered handle name to exclude from deferring. One per line.</p>
					<p class="description"><small>Examples:</small></p>
					<ul class="description" style="margin-top: 0; list-style-type: disc; padding-left: 20px;">
						<li><small><strong>Handle name:</strong> <code>jquery-core</code> or <code>generate-menu</code> (exact handle name, no slashes or dots)</small></li>
						<li><small><strong>Partial filename:</strong> <code>jquery.min.js</code> (matches any URL containing this string)</small></li>
						<li><small><strong>Full URL:</strong> <code>https://example.com/script.js</code></small></li>
					</ul>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Delay JavaScript</th>
				<td>
					<div class="checkbox-radio">
						<label>
							<input type="checkbox" name="rapidpress_options[js_delay]" id="rapidpress_js_delay" value="1" <?php checked(1, RP_Options::get_option('js_delay'), true); ?> />
						</label>
						<span class="dashicons dashicons-editor-help" data-title="Postpones JavaScript execution until after the page has loaded or user interaction occurs. This dramatically improves initial page load speed and Core Web Vitals metrics by prioritizing content rendering over script execution."></span>
					</div>
				</td>
			</tr>
			<tr valign="top" id="rapidpress_js_delay_options" style="display: none;">
				<th scope="row">Delay Options</th>
				<td>
					<select name="rapidpress_options[js_delay_type]" id="rapidpress_js_delay_type">
						<option value="all" <?php selected(RP_Options::get_option('js_delay_type'), 'all'); ?>>All JavaScripts</option>
						<option value="specific" <?php selected(RP_Options::get_option('js_delay_type'), 'specific'); ?>>Specific JavaScripts</option>
					</select>

					<div id="rapidpress_js_delay_duration" style="margin-top: 10px;">
						<select name="rapidpress_options[js_delay_duration]">
							<option value="1" <?php selected(RP_Options::get_option('js_delay_duration'), '1'); ?>>1 second</option>
							<option value="2" <?php selected(RP_Options::get_option('js_delay_duration'), '2'); ?>>2 seconds</option>
							<option value="3" <?php selected(RP_Options::get_option('js_delay_duration'), '3'); ?>>3 seconds</option>
							<option value="interaction" <?php selected(RP_Options::get_option('js_delay_duration'), 'interaction'); ?>>Until user interaction</option>
						</select>
					</div>

					<div id="js_delay_exclusions_wrapper">
						<div class="checkbox-btn" id="rapidpress_enable_js_delay_exclusions_btn">
							<label>
								<input type="checkbox" name="rapidpress_options[enable_js_delay_exclusions]" id="rapidpress_enable_js_delay_exclusions" value="1" <?php checked(1, RP_Options::get_option('enable_js_delay_exclusions'), true); ?> />
								<span>Enable JS Delay Exclusions</span>
							</label>
						</div>
					</div>
				</td>
			</tr>

			<tr valign="top" id="rapidpress_js_delay_specific" style="display: none;">
				<th scope="row">Specific JS Files</th>
				<td>
					<textarea name="rapidpress_options[js_delay_specific_files]" rows="3" cols="70" placeholder="Enter JavaScript file URL/Handle per line"><?php echo esc_textarea(RP_Options::get_option('js_delay_specific_files', '')); ?></textarea>
					<p class="description">Enter the JavaScript file URL, partial filename, or the registered handle name to delay. One per line.</p>
					<p class="description"><small>Examples:</small></p>
					<ul class="description" style="margin-top: 0; list-style-type: disc; padding-left: 20px;">
						<li><small><strong>Handle name:</strong> <code>jquery-core</code> or <code>wp-embed</code> (exact handle name, no slashes or dots)</small></li>
						<li><small><strong>Partial filename:</strong> <code>jquery.min.js</code> (matches any URL containing this string)</small></li>
						<li><small><strong>Full URL:</strong> <code>https://example.com/script.js</code></small></li>
					</ul>
				</td>
			</tr>

			<tr valign="top" id="rapidpress_js_delay_exclusions_row" style="display: none;">
				<th scope="row">JS Delay Exclusions</th>
				<td>
					<textarea name="rapidpress_options[js_delay_exclusions]" rows="3" cols="70" placeholder="Enter JavaScript file URL/Handle per line"><?php echo esc_textarea(RP_Options::get_option('js_delay_exclusions', '')); ?></textarea>
					<p class="description">Enter the JavaScript file URL, partial filename, or the registered handle name to exclude from delay. One per line.</p>
					<p class="description"><small>Examples:</small></p>
					<ul class="description" style="margin-top: 0; list-style-type: disc; padding-left: 20px;">
						<li><small><strong>Handle name:</strong> <code>jquery-core</code> or <code>generate-menu</code> (exact handle name, no slashes or dots)</small></li>
						<li><small><strong>Partial filename:</strong> <code>jquery.min.js</code> (matches any URL containing this string)</small></li>
						<li><small><strong>Full URL:</strong> <code>https://example.com/script.js</code></small></li>
					</ul>
				</td>
			</tr>
		</table>
	</div>
</div>