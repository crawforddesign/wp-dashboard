<?php
/**
 * Handle all AJAX requests for the admin interface
 */
class CDG_Elements_Admin_Ajax {

	/**
	 * Initialize AJAX handlers
	 */
	public function __construct() {
		// Element Management
		add_action('wp_ajax_cdg_get_elements', array($this, 'get_elements'));
		add_action('wp_ajax_cdg_create_element', array($this, 'create_element'));
		add_action('wp_ajax_cdg_update_element', array($this, 'update_element'));
		add_action('wp_ajax_cdg_delete_element', array($this, 'delete_element'));
		add_action('wp_ajax_cdg_reorder_elements', array($this, 'reorder_elements'));
		
		// Bulk Operations
		add_action('wp_ajax_cdg_bulk_update_elements', array($this, 'bulk_update_elements'));
		add_action('wp_ajax_cdg_bulk_delete_elements', array($this, 'bulk_delete_elements'));
		
		// Preview
		add_action('wp_ajax_cdg_get_preview_content', array($this, 'get_preview_content'));
	}

	/**
	 * Get elements for a specific post
	 */
	public function get_elements() {
		$this->verify_ajax_nonce();
		
		if (!current_user_can('edit_posts')) {
			wp_send_json_error('Insufficient permissions');
		}

		$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
		
		global $wpdb;
		$table_name = $wpdb->prefix . 'cdg_elements';
		
		$elements = $wpdb->get_results($wpdb->prepare(
			"SELECT * FROM $table_name 
			WHERE post_id = %d 
			ORDER BY id ASC",
			$post_id
		));

		if ($elements === false) {
			wp_send_json_error('Database error occurred');
		}

		wp_send_json_success($elements);
	}

	/**
	 * Create a new element
	 */
	public function create_element() {
		$this->verify_ajax_nonce();
		
		if (!current_user_can('edit_posts')) {
			wp_send_json_error('Insufficient permissions');
		}

		$element_data = $this->sanitize_element_data($_POST['element']);
		$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

		if (!$post_id) {
			wp_send_json_error('Invalid post ID');
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'cdg_elements';

		$data = array(
			'post_id' => $post_id,
			'element_text' => $element_data['text'],
			'font_family' => $element_data['font'],
			'color' => $element_data['color'],
			'size' => $element_data['size'],
			'rotation' => $element_data['rotation'],
			'position_x' => $element_data['position']['x'],
			'position_y' => $element_data['position']['y'],
			'is_active' => 1
		);

		$result = $wpdb->insert($table_name, $data);

		if ($result === false) {
			wp_send_json_error('Failed to create element');
		}

		$element_id = $wpdb->insert_id;
		$new_element = $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM $table_name WHERE id = %d",
			$element_id
		));

		wp_send_json_success($new_element);
	}

	/**
	 * Update an existing element
	 */
	public function update_element() {
		$this->verify_ajax_nonce();
		
		if (!current_user_can('edit_posts')) {
			wp_send_json_error('Insufficient permissions');
		}

		$element_data = $this->sanitize_element_data($_POST['element']);
		$element_id = isset($_POST['element']['id']) ? intval($_POST['element']['id']) : 0;

		if (!$element_id) {
			wp_send_json_error('Invalid element ID');
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'cdg_elements';

		$data = array(
			'element_text' => $element_data['text'],
			'font_family' => $element_data['font'],
			'color' => $element_data['color'],
			'size' => $element_data['size'],
			'rotation' => $element_data['rotation'],
			'position_x' => $element_data['position']['x'],
			'position_y' => $element_data['position']['y']
		);

		$result = $wpdb->update(
			$table_name,
			$data,
			array('id' => $element_id)
		);

		if ($result === false) {
			wp_send_json_error('Failed to update element');
		}

		$updated_element = $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM $table_name WHERE id = %d",
			$element_id
		));

		wp_send_json_success($updated_element);
	}

	/**
	 * Delete an element
	 */
	public function delete_element() {
		$this->verify_ajax_nonce();
		
		if (!current_user_can('edit_posts')) {
			wp_send_json_error('Insufficient permissions');
		}

		$element_id = isset($_POST['element_id']) ? intval($_POST['element_id']) : 0;

		if (!$element_id) {
			wp_send_json_error('Invalid element ID');
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'cdg_elements';

		$result = $wpdb->delete(
			$table_name,
			array('id' => $element_id)
		);

		if ($result === false) {
			wp_send_json_error('Failed to delete element');
		}

		wp_send_json_success(array(
			'message' => 'Element deleted successfully',
			'element_id' => $element_id
		));
	}

	/**
	 * Reorder elements
	 */
	public function reorder_elements() {
		$this->verify_ajax_nonce();
		
		if (!current_user_can('edit_posts')) {
			wp_send_json_error('Insufficient permissions');
		}

		$element_order = isset($_POST['element_order']) ? array_map('intval', $_POST['element_order']) : array();

		if (empty($element_order)) {
			wp_send_json_error('No elements to reorder');
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'cdg_elements';

		// Begin transaction
		$wpdb->query('START TRANSACTION');

		try {
			foreach ($element_order as $position => $element_id) {
				$wpdb->update(
					$table_name,
					array('display_order' => $position),
					array('id' => $element_id)
				);
			}

			$wpdb->query('COMMIT');
			wp_send_json_success('Elements reordered successfully');

		} catch (Exception $e) {
			$wpdb->query('ROLLBACK');
			wp_send_json_error('Failed to reorder elements');
		}
	}

	/**
	 * Bulk update elements
	 */
	public function bulk_update_elements() {
		$this->verify_ajax_nonce();
		
		if (!current_user_can('edit_posts')) {
			wp_send_json_error('Insufficient permissions');
		}

		$elements = isset($_POST['elements']) ? $_POST['elements'] : array();

		if (empty($elements)) {
			wp_send_json_error('No elements to update');
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'cdg_elements';

		// Begin transaction
		$wpdb->query('START TRANSACTION');

		try {
			foreach ($elements as $element) {
				$element_data = $this->sanitize_element_data($element);
				$element_id = intval($element['id']);

				if (!$element_id) continue;

				$wpdb->update(
					$table_name,
					array(
						'element_text' => $element_data['text'],
						'font_family' => $element_data['font'],
						'color' => $element_data['color'],
						'size' => $element_data['size'],
						'rotation' => $element_data['rotation'],
						'position_x' => $element_data['position']['x'],
						'position_y' => $element_data['position']['y']
					),
					array('id' => $element_id)
				);
			}

			$wpdb->query('COMMIT');
			wp_send_json_success('Elements updated successfully');

		} catch (Exception $e) {
			$wpdb->query('ROLLBACK');
			wp_send_json_error('Failed to update elements');
		}
	}

	/**
	 * Get preview content
	 */
	public function get_preview_content() {
		$this->verify_ajax_nonce();
		
		if (!current_user_can('edit_posts')) {
			wp_send_json_error('Insufficient permissions');
		}

		ob_start();
		include(plugin_dir_path(__FILE__) . 'views/preview-content.php');
		$content = ob_get_clean();

		wp_send_json_success(array(
			'content' => $content
		));
	}

	/**
	 * Verify AJAX nonce
	 */
	private function verify_ajax_nonce() {
		if (!check_ajax_referer('cdg_elements_nonce', 'nonce', false)) {
			wp_send_json_error('Invalid security token');
		}
	}

	/**
	 * Sanitize element data
	 */
	 private function sanitize_element_data($data) {
		 return array(
			 'text' => sanitize_text_field($data['text']),
			 'font' => sanitize_text_field($data['font']),
			 'font_url' => esc_url_raw($data['font_url']),
			 'color' => sanitize_hex_color($data['color']),
			 'size' => $this->sanitize_size($data['size']),
			 'rotation' => $this->sanitize_rotation($data['rotation']),
			 'position' => array(
				 'x' => intval($data['position']['x']),
				 'y' => intval($data['position']['y'])
			 )
		 );
	 }

	/**
	 * Sanitize size value
	 */
	private function sanitize_size($size) {
		$allowed_sizes = array('x-small', 'small', 'medium', 'large');
		return in_array($size, $allowed_sizes) ? $size : 'medium';
	}

	/**
	 * Sanitize rotation value
	 */
	private function sanitize_rotation($rotation) {
		$allowed_rotations = array(-5, -3, 0, 3, 5);
		$rotation = intval($rotation);
		return in_array($rotation, $allowed_rotations) ? $rotation : 0;
	}
}