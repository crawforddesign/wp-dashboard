<?php
/**
 * Handles the frontend display of floating elements
 */
class CDG_Elements_Frontend {
	private $plugin_name;
	private $version;
	private $elements;

	private function load_custom_fonts() {
		global $wpdb;
		$post_id = get_the_ID();
		$table_name = $wpdb->prefix . 'cdg_elements';
		
		// Get unique font URLs for this page
		$font_urls = $wpdb->get_col($wpdb->prepare(
			"SELECT DISTINCT font_url 
			FROM $table_name 
			WHERE post_id = %d 
			AND is_active = 1 
			AND font_url IS NOT NULL 
			AND font_url != ''",
			$post_id
		));
	
		// Enqueue each font stylesheet
		foreach ($font_urls as $url) {
			wp_enqueue_style(
				'cdg-font-' . md5($url),
				esc_url($url),
				array(),
				$this->version
			);
		}
	}
	
	// Add this to the initialize() method:
	add_action('wp_enqueue_scripts', array($this, 'load_custom_fonts'));

	public function __construct($plugin_name, $version) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->elements = array();
	}

	/**
	 * Initialize the frontend system
	 */
	public function initialize() {
		add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
		add_action('wp_footer', array($this, 'render_elements'));
		add_filter('body_class', array($this, 'add_body_class'));
	}

	/**
	 * Enqueue frontend styles
	 */
	public function enqueue_styles() {
		wp_enqueue_style(
			$this->plugin_name . '-frontend',
			plugin_dir_url(__FILE__) . 'css/frontend.css',
			array(),
			$this->version
		);
	}

	/**
	 * Enqueue frontend scripts
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			$this->plugin_name . '-frontend',
			plugin_dir_url(__FILE__) . 'js/frontend.js',
			array('jquery'),
			$this->version,
			true
		);

		// Pass data to JavaScript
		wp_localize_script(
			$this->plugin_name . '-frontend',
			'cdgElementsConfig',
			array(
				'ajaxurl' => admin_url('ajax.php'),
				'nonce' => wp_create_nonce('cdg_elements_frontend'),
				'postId' => get_the_ID()
			)
		);
	}

	/**
	 * Add body class when elements are enabled
	 */
	public function add_body_class($classes) {
		if ($this->should_display_elements()) {
			$classes[] = 'has-cdg-elements';
		}
		return $classes;
	}

	/**
	 * Check if elements should be displayed
	 */
	private function should_display_elements() {
		if (!is_singular()) {
			return false;
		}

		$post_id = get_the_ID();
		return get_post_meta($post_id, '_cdg_elements_enabled', true) === '1';
	}

	/**
	 * Load elements for current page
	 */
	private function load_elements() {
		if (!empty($this->elements)) {
			return;
		}

		global $wpdb;
		$post_id = get_the_ID();
		$table_name = $wpdb->prefix . 'cdg_elements';

		$this->elements = $wpdb->get_results($wpdb->prepare(
			"SELECT * FROM $table_name 
			WHERE post_id = %d 
			AND is_active = 1 
			ORDER BY id ASC",
			$post_id
		));
	}

	/**
	 * Render elements on the page
	 */
	public function render_elements() {
		if (!$this->should_display_elements()) {
			return;
		}

		$this->load_elements();

		if (empty($this->elements)) {
			return;
		}

		$this->render_elements_container();
	}

	/**
	 * Render the elements container
	 */
	private function render_elements_container() {
		?>
		<div id="cdg-elements-container" class="cdg-elements-container" aria-hidden="true">
			<?php foreach ($this->elements as $element) : ?>
				<?php $this->render_single_element($element); ?>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Render a single element
	 */
	private function render_single_element($element) {
		$element_id = 'cdg-element-' . esc_attr($element->id);
		$classes = array('cdg-element');
		$styles = $this->get_element_styles($element);
		?>
		<div id="<?php echo $element_id; ?>"
			 class="<?php echo esc_attr(implode(' ', $classes)); ?>"
			 data-element-id="<?php echo esc_attr($element->id); ?>"
			 <?php echo $this->build_style_attribute($styles); ?>>
			<?php echo esc_html($element->element_text); ?>
		</div>
		<?php
	}

	/**
	 * Get element styles
	 */
	private function get_element_styles($element) {
		return array(
			'left' => $element->position_x . 'px',
			'top' => $element->position_y . 'px',
			'font-family' => $element->font_family,
			'color' => $element->color,
			'font-size' => $this->get_size_value($element->size),
			'transform' => 'rotate(' . intval($element->rotation) . 'deg)',
			'filter' => $this->get_blur_filter($element->size)
		);
	}

	/**
	 * Build style attribute string
	 */
	private function build_style_attribute($styles) {
		$style_string = '';
		foreach ($styles as $property => $value) {
			if (!empty($value)) {
				$style_string .= $property . ': ' . $value . '; ';
			}
		}
		return 'style="' . esc_attr(trim($style_string)) . '"';
	}

	/**
	 * Get size value in pixels
	 */
	private function get_size_value($size) {
		$sizes = array(
			'x-small' => '12px',
			'small' => '16px',
			'medium' => '24px',
			'large' => '36px'
		);
		return isset($sizes[$size]) ? $sizes[$size] : $sizes['medium'];
	}

	/**
	 * Get blur filter value
	 */
	private function get_blur_filter($size) {
		$blur_values = array(
			'x-small' => 3,
			'small' => 2,
			'medium' => 1,
			'large' => 0
		);
		$blur = isset($blur_values[$size]) ? $blur_values[$size] : 0;
		return $blur > 0 ? "blur({$blur}px)" : 'none';
	}
}