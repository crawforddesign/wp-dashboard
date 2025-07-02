<?php
/**
 * The admin-specific functionality of the plugin.
 */
class CDG_Elements_Admin {

	private $plugin_name;
	private $version;

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct($plugin_name, $version) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 */
	public function enqueue_styles() {
		wp_enqueue_style('wp-color-picker');
		wp_enqueue_style(
			$this->plugin_name,
			plugin_dir_url(__FILE__) . 'css/admin-style.css',
			array(),
			$this->version,
			'all'
		);
	}

	/**
	 * Register the JavaScript for the admin area.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script('wp-color-picker');
		wp_enqueue_script(
			$this->plugin_name . '-admin',
			plugin_dir_url(__FILE__) . 'js/admin-script.js',
			array('jquery', 'wp-color-picker', 'jquery-ui-draggable'),
			$this->version,
			false
		);

		// Localize script for AJAX and translations
		wp_localize_script($this->plugin_name . '-admin', 'cdgElements', array(
			'ajaxurl' => admin_url('ajax.php'),
			'nonce' => wp_create_nonce('cdg_elements_nonce'),
			'angles' => array(-5, -3, 0, 3, 5),
			'sizes' => array(
				'x-small' => __('X-Small', 'cdg-elements'),
				'small' => __('Small', 'cdg-elements'),
				'medium' => __('Medium', 'cdg-elements'),
				'large' => __('Large', 'cdg-elements')
			)
		));
	}

	/**
	 * Add plugin admin menu.
	 */
	public function add_plugin_admin_menu() {
		add_menu_page(
			__('CDG Elements', 'cdg-elements'),
			__('CDG Elements', 'cdg-elements'),
			'manage_options',
			$this->plugin_name,
			array($this, 'display_plugin_setup_page'),
			'dashicons-editor-textcolor',
			65
		);
	}

	/**
	 * Add meta box to posts and pages.
	 */
	public function add_meta_boxes() {
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
	 * Render the meta box.
	 */
	public function render_meta_box($post) {
		// Add nonce for security
		wp_nonce_field('cdg_elements_meta_box', 'cdg_elements_meta_box_nonce');

		// Get current value
		$enabled = get_post_meta($post->ID, '_cdg_elements_enabled', true);
		?>
		<p>
			<label>
				<input type="checkbox" name="cdg_elements_enabled" value="1" <?php checked($enabled, '1'); ?> />
				<?php _e('Enable floating elements on this page', 'cdg-elements'); ?>
			</label>
		</p>
		<?php
	}

	/**
	 * Save meta box data.
	 */
	public function save_meta_box_data($post_id) {
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

	/**
	 * Render the settings page for this plugin.
	 */
	public function display_plugin_setup_page() {
		include_once('views/settings-page.php');
	}

	/**
	 * Save plugin general settings.
	 */
	public function save_general_settings() {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}

		check_admin_referer('cdg_elements_settings', 'cdg_elements_settings_nonce');

		// Process and save settings here
		$settings = array(
			'default_font' => sanitize_text_field($_POST['default_font']),
			'default_color' => sanitize_hex_color($_POST['default_color']),
			'default_size' => sanitize_text_field($_POST['default_size']),
			'default_rotation' => intval($_POST['default_rotation'])
		);

		update_option('cdg_elements_settings', $settings);
	}
}