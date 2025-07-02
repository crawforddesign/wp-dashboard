<?php
/**
 * The public-facing functionality of the plugin.
 */
class CDG_Elements_Public {
	private $plugin_name;
	private $version;

	public function __construct($plugin_name, $version) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 */
	public function enqueue_styles() {
		wp_enqueue_style(
			$this->plugin_name,
			plugin_dir_url(__FILE__) . 'css/public.css',
			array(),
			$this->version,
			'all'
		);
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url(__FILE__) . 'js/public.js',
			array('jquery'),
			$this->version,
			true
		);

		wp_localize_script($this->plugin_name, 'cdgElementsPublic', array(
			'ajaxurl' => admin_url('ajax.php'),
			'nonce' => wp_create_nonce('cdg_elements_public_nonce')
		));
	}

	/**
	 * Render floating elements on the page
	 */
	public function render_elements() {
		// Check if elements are enabled for this page
		if (!$this->should_render_elements()) {
			return;
		}

		global $wpdb;
		$post_id = get_the_ID();
		$table_name = $wpdb->prefix . 'cdg_elements';

		$elements = $wpdb->get_results($wpdb->prepare(
			"SELECT * FROM $table_name WHERE post_id = %d AND is_active = 1",
			$post_id
		));

		if (empty($elements)) {
			return;
		}

		$this->render_elements_container($elements);
	}

	/**
	 * Check if elements should be rendered
	 */
	private function should_render_elements() {
		if (!is_singular()) {
			return false;
		}

		$post_id = get_the_ID();
		return get_post_meta($post_id, '_cdg_elements_enabled', true) === '1';
	}

	/**
	 * Render the elements container
	 */
	private function render_elements_container($elements) {
		?>
		<div id="cdg-elements-container" class="cdg-elements-container">
			<?php foreach ($elements as $element) : ?>
				<?php $this->render_single_element($element); ?>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Render a single floating element
	 */
	private function render_single_element($element) {
		$blur_value = $this->get_blur_value($element->size);
		?>
		<div class="cdg-element"
			 style="left: <?php echo esc_attr($element->position_x); ?>px;
					top: <?php echo esc_attr($element->position_y); ?>px;
					font-family: <?php echo esc_attr($element->font_family); ?>;
					color: <?php echo esc_attr($element->color); ?>;
					font-size: <?php echo esc_attr($this->get_size_value($element->size)); ?>px;
					transform: rotate(<?php echo esc_attr($element->rotation); ?>deg);
					filter: blur(<?php echo esc_attr($blur_value); ?>px);">
			<?php echo esc_html($element->element_text); ?>
		</div>
		<?php
	}

	/**
	 * Get size value in pixels
	 */
	private function get_size_value($size) {
		$sizes = array(
			'x-small' => 12,
			'small' => 16,
			'medium' => 24,
			'large' => 36
		);
		return isset($sizes[$size]) ? $sizes[$size] : $sizes['medium'];
	}

	/**
	 * Get blur value in pixels
	 */
	private function get_blur_value($size) {
		$blurs = array(
			'x-small' => 3,
			'small' => 2,
			'medium' => 1,
			'large' => 0
		);
		return isset($blurs[$size]) ? $blurs[$size] : 0;
	}
}