<?php
/**
 * Handle preview functionality
 */
class CDG_Elements_Preview {

	/**
	 * Initialize the class
	 */
	public function __construct() {
		add_action('admin_footer', array($this, 'render_preview_modal'));
	}

	/**
	 * Render preview modal
	 */
	public function render_preview_modal() {
		?>
		<div id="cdg-preview-modal" class="cdg-modal">
			<div class="cdg-modal-content">
				<div class="cdg-modal-header">
					<h2><?php _e('Preview', 'cdg-elements'); ?></h2>
					<button class="cdg-modal-close">&times;</button>
				</div>
				<div class="cdg-modal-body">
					<div class="preview-viewport">
						<div class="preview-scale-controls">
							<button class="preview-scale" data-scale="desktop">Desktop</button>
							<button class="preview-scale" data-scale="tablet">Tablet</button>
							<button class="preview-scale" data-scale="mobile">Mobile</button>
						</div>
						<div id="preview-frame-container"></div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Get preview frame content
	 */
	public static function get_preview_frame_content() {
		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="utf-8">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<style>
				body {
					margin: 0;
					padding: 20px;
					font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
				}
				.cdg-element {
					position: fixed;
					pointer-events: none;
					transition: filter 0.3s ease;
				}
				.cdg-below {
					position: relative;
					z-index: 1;
				}
				.cdg-above {
					position: relative;
					z-index: 3;
				}
				.preview-content {
					position: relative;
					z-index: 2;
				}
			</style>
		</head>
		<body>
			<div class="preview-content">
				<!-- Sample content -->
				<div class="cdg-below">
					<h1>Sample Header</h1>
					<p>This is sample content to demonstrate how the floating elements interact with page content.</p>
				</div>
				<div class="cdg-above">
					<h2>Above Content</h2>
					<p>This content will appear above the floating elements.</p>
				</div>
			</div>
			<div id="cdg-elements-container"></div>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}
}
