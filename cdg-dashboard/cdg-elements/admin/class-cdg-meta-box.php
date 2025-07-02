<?php
/**
 * Handles the meta box for per-page element control
 */
class CDG_Elements_Meta_Box {

	/**
	 * Initialize the meta box
	 */
	public function __construct() {
		add_action('add_meta_boxes', array($this, 'add_meta_box'));
		add_action('save_post', array($this, 'save_meta_box'));
	}

	/**
	 * Add the meta box
	 */
	public function add_meta_box() {
		$screens = array('post', 'page');
		
		foreach ($screens as $screen) {
			add_meta_box(
				'cdg_elements_settings',
				__('CDG Elements Settings', 'cdg-elements'),
				array($this, 'render_meta_box'),
				$screen,
				'side',
				'default'
			);
		}
	}

	/**
	 * Render the meta box
	 */
	public function render_meta_box($post) {
		// Add nonce for security
		wp_nonce_field('cdg_elements_meta_box', 'cdg_elements_meta_box_nonce');

		// Get current value
		$enabled = get_post_meta($post->ID, '_cdg_elements_enabled', true);
		?>
		<div class="cdg-elements-meta-box">
			<p>
				<label>
					<input type="checkbox" 
						   name="cdg_elements_enabled" 
						   value="1" 
						   <?php checked($enabled, '1'); ?>>
					<?php _e('Enable floating elements on this page', 'cdg-elements'); ?>
				</label>
			</p>
			<p class="description">
				<?php _e('When enabled, floating elements will be displayed on this page.', 'cdg-elements'); ?>
			</p>
			<div class="cdg-elements-quick-settings" style="display: <?php echo $enabled ? 'block' : 'none'; ?>;">
				<p>
					<a href="<?php echo admin_url('admin.php?page=cdg-elements&post=' . $post->ID); ?>" class="button">
						<?php _e('Manage Elements', 'cdg-elements'); ?>
					</a>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Save the meta box data
	 */
	public function save_meta_box($post_id) {
		// Security checks
		if (!isset($_POST['cdg_elements_meta_box_nonce'])) {
			return;
		}

		if (!wp_verify_nonce($_POST['cdg_elements_meta_box_nonce'], 'cdg_elements_meta_box')) {
			return;
		}

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}

		if (!current_user_can('edit_post', $post_id)) {
			return;
		}

		// Save the setting
		$enabled = isset($_POST['cdg_elements_enabled']) ? '1' : '0';
		update_post_meta($post_id, '_cdg_elements_enabled', $enabled);
	}
}