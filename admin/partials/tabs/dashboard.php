<?php
// Ensure this file is being included by a parent file
if (!defined('ABSPATH')) exit; // Exit if accessed directly

?>

<div id="dashboard" class="tab-pane active">
	<h2>Dashboard</h2>
	<div class="rapidpress-card">
		<h3>Performance Score</h3>
		<div class="performance-score">85</div>
		<p>Your site is performing well. Here are some suggestions to improve further:</p>
		<ul>
			<li>Enable HTML minification</li>
			<li>Configure browser caching</li>
		</ul>
	</div>
	<div class="rapidpress-card">
		<h3>Active Optimizations</h3>
		<ul>
			<li>HTML Minification: <?php echo get_option('rapidpress_html_minify') ? 'Enabled' : 'Disabled'; ?></li>
			<li>CSS Minification: <?php echo get_option('rapidpress_css_minify') ? 'Enabled' : 'Disabled'; ?></li>
			<li>CSS Combination: <?php echo get_option('rapidpress_combine_css') ? 'Enabled' : 'Disabled'; ?></li>
			<li>JavaScript Minification: <?php echo get_option('rapidpress_js_minify') ? 'Enabled' : 'Disabled'; ?></li>
			<!-- Add more optimizations here as we implement them -->
		</ul>
	</div>
</div>