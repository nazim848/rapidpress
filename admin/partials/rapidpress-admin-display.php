<div class="wrap">
	<h1><?php echo esc_html(get_admin_page_title()); ?></h1>

	<?php settings_errors(); ?>

	<div class="rapidpress-admin-content">
		<nav class="nav-tab-wrapper">
			<a href="#dashboard" class="nav-tab nav-tab-active">Dashboard</a>
			<a href="#minification" class="nav-tab">Minification</a>
			<a href="#caching" class="nav-tab">Caching</a>
			<a href="#cdn" class="nav-tab">CDN</a>
			<a href="#advanced" class="nav-tab">Advanced</a>
		</nav>

		<div class="tab-content">
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
						<!-- Add more optimizations here as we implement them -->
					</ul>
				</div>
			</div>
			<div id="minification" class="tab-pane">
				<h2>Minification Settings</h2>

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
						</table>
					</div>
					<?php submit_button(); ?>
				</form>

			</div>

			<div id="caching" class="tab-pane">
				<h2>Caching Settings</h2>
				<!-- Caching content -->
			</div>

			<div id="cdn" class="tab-pane">
				<h2>CDN Settings</h2>
				<!-- CDN content -->
			</div>

			<div id="advanced" class="tab-pane">
				<h2>Advanced Settings</h2>
				<!-- Advanced content -->
			</div>
		</div>
	</div>
</div>