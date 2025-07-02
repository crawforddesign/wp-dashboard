<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}
?>

<div class="wrap cdg-elements-admin">
	<h1><?php echo esc_html(get_admin_page_title()); ?></h1>

	<div class="cdg-elements-tabs">
		<nav class="nav-tab-wrapper">
			<a href="#elements" class="nav-tab nav-tab-active"><?php _e('Elements', 'cdg-elements'); ?></a>
			<a href="#settings" class="nav-tab"><?php _e('Settings', 'cdg-elements'); ?></a>
		</nav>

		<!-- Elements Tab -->
		<div id="elements" class="tab-content active">
			<div class="cdg-elements-workspace">
				<!-- Element Controls -->
				<div class="element-controls">
					<h2><?php _e('Add New Element', 'cdg-elements'); ?></h2>
					<form id="add-element-form">
						<div class="form-group">
							<label for="element-text"><?php _e('Text', 'cdg-elements'); ?></label>
							<input type="text" id="element-text" name="element-text" required>
						</div>

						<div class="form-row">
							<div class="form-group">
								<label for="element-font"><?php _e('Font', 'cdg-elements'); ?></label>
								<select id="element-font" name="element-font">
									<option value="Arial">Arial</option>
									<option value="Helvetica">Helvetica</option>
									<option value="Times New Roman">Times New Roman</option>
									<option value="Georgia">Georgia</option>
								</select>
							</div>

							<div class="form-group">
								<label for="element-color"><?php _e('Color', 'cdg-elements'); ?></label>
								<input type="text" id="element-color" name="element-color" class="color-picker" value="#000000">
							</div>
						</div>

						<div class="form-row">
							<div class="form-group">
								<label for="element-size"><?php _e('Size', 'cdg-elements'); ?></label>
								<select id="element-size" name="element-size">
									<option value="x-small">X-Small</option>
									<option value="small">Small</option>
									<option value="medium" selected>Medium</option>
									<option value="large">Large</option>
								</select>
							</div>

							<div class="form-group">
								<label for="element-rotation"><?php _e('Rotation', 'cdg-elements'); ?></label>
								<select id="element-rotation" name="element-rotation">
									<option value="-5">-5°</option>
									<option value="-3">-3°</option>
									<option value="0" selected>0°</option>
									<option value="3">3°</option>
									<option value="5">5°</option>
								</select>
							</div>
						</div>

						<div class="form-row">
							<div class="form-group">
								<label for="element-x"><?php _e('X Position', 'cdg-elements'); ?></label>
								<input type="number" id="element-x" name="element-x" value="0">
							</div>

							<div class="form-group">
								<label for="element-y"><?php _e('Y Position', 'cdg-elements'); ?></label>
								<input type="number" id="element-y" name="element-y" value="0">
							</div>
						</div>

						<div class="form-group">
							<button type="submit" class="button button-primary"><?php _e('Add Element', 'cdg-elements'); ?></button>
						</div>
					</form>
				</div>

				<!-- Preview Area -->
				<div class="preview-area">
					<h2><?php _e('Preview', 'cdg-elements'); ?></h2>
					<div id="preview-canvas" class="preview-canvas">
						<div class="preview-content">
							<!-- Sample content for preview -->
							<div class="cdg-below">
								<h1>Sample Heading</h1>
								<p>Sample paragraph text to demonstrate layering.</p>
							</div>
						</div>
					</div>
				</div>

				<!-- Elements List -->
				<div class="elements-list">
					<h2><?php _e('Current Elements', 'cdg-elements'); ?></h2>
					<div id="elements-container">
						<!-- Elements will be dynamically added here -->
					</div>
				</div>
			</div>
		</div>

		<!-- Settings Tab -->
		<div id="settings" class="tab-content">
			<form method="post" action="options.php">
				<?php
				settings_fields('cdg_elements_settings');
				do_settings_sections('cdg_elements_settings');
				?>
				<table class="form-table">
					<tr>
						<th scope="row"><?php _e('Default Font', 'cdg-elements'); ?></th>
						<td>
							<select name="cdg_elements_settings[default_font]">
								<option value="Arial">Arial</option>
								<option value="Helvetica">Helvetica</option>
								<option value="Times New Roman">Times New Roman</option>
								<option value="Georgia">Georgia</option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Default Color', 'cdg-elements'); ?></th>
						<td>
							<input type="text" name="cdg_elements_settings[default_color]" class="color-picker">
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
	</div>
</div>