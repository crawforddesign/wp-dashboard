<?php
/**
 * Class CDGElementsTest
 */
class CDGElementsTest extends WP_UnitTestCase {
	private $plugin;
	private $post_id;

	public function setUp(): void {
		parent::setUp();
		
		// Create a test post
		$this->post_id = $this->factory()->post->create();
		
		// Initialize plugin
		$this->plugin = new CDG_Elements();
	}

	public function test_plugin_initialization() {
		$this->assertInstanceOf(CDG_Elements::class, $this->plugin);
		$this->assertInstanceOf(CDG_Elements_Loader::class, $this->plugin->get_loader());
	}

	public function test_element_creation() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'cdg_elements';

		// Test data
		$element_data = array(
			'post_id' => $this->post_id,
			'element_text' => 'Test Element',
			'font_family' => 'Arial',
			'color' => '#000000',
			'size' => 'medium',
			'rotation' => 0,
			'position_x' => 100,
			'position_y' => 100,
			'is_active' => 1
		);

		// Insert test element
		$wpdb->insert($table_name, $element_data);
		$element_id = $wpdb->insert_id;

		// Verify element was created
		$element = $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM $table_name WHERE id = %d",
			$element_id
		));

		$this->assertNotNull($element);
		$this->assertEquals($element_data['element_text'], $element->element_text);
		$this->assertEquals($element_data['font_family'], $element->font_family);
	}

	public function test_element_update() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'cdg_elements';

		// Create initial element
		$element_data = array(
			'post_id' => $this->post_id,
			'element_text' => 'Initial Text',
			'font_family' => 'Arial',
			'color' => '#000000',
			'size' => 'medium',
			'rotation' => 0,
			'position_x' => 100,
			'position_y' => 100,
			'is_active' => 1
		);

		$wpdb->insert($table_name, $element_data);
		$element_id = $wpdb->insert_id;

		// Update element
		$wpdb->update(
			$table_name,
			array('element_text' => 'Updated Text'),
			array('id' => $element_id)
		);

		// Verify update
		$updated_element = $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM $table_name WHERE id = %d",
			$element_id
		));

		$this->assertEquals('Updated Text', $updated_element->element_text);
	}

	public function test_element_deletion() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'cdg_elements';

		// Create element
		$element_data = array(
			'post_id' => $this->post_id,
			'element_text' => 'Test Element',
			'font_family' => 'Arial',
			'color' => '#000000',
			'size' => 'medium',
			'rotation' => 0,
			'position_x' => 100,
			'position_y' => 100,
			'is_active' => 1
		);

		$wpdb->insert($table_name, $element_data);
		$element_id = $wpdb->insert_id;

		// Delete element
		$wpdb->delete($table_name, array('id' => $element_id));

		// Verify deletion
		$deleted_element = $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM $table_name WHERE id = %d",
			$element_id
		));

		$this->assertNull($deleted_element);
	}

	public function test_element_rendering() {
		// Enable elements for the test post
		update_post_meta($this->post_id, '_cdg_elements_enabled', '1');

		// Create test element
		global $wpdb;
		$table_name = $wpdb->prefix . 'cdg_elements';
		
		$element_data = array(
			'post_id' => $this->post_id,
			'element_text' => 'Test Element',
			'font_family' => 'Arial',
			'color' => '#000000',
			'size' => 'medium',
			'rotation' => 0,
			'position_x' => 100,
			'position_y' => 100,
			'is_active' => 1
		);

		$wpdb->insert($table_name, $element_data);

		// Set up the post environment
		$this->go_to(get_permalink($this->post_id));

		// Buffer the output
		ob_start();
		do_action('wp_footer');
		$output = ob_get_clean();

		// Verify element rendering
		$this->assertStringContainsString('cdg-elements-container', $output);
		$this->assertStringContainsString('Test Element', $output);
	}

	public function test_blur_values() {
		$public = new CDG_Elements_Public('cdg-elements', '1.0.0');
		$reflection = new ReflectionClass($public);
		$method = $reflection->getMethod('get_blur_value');
		$method->setAccessible(true);

		// Test blur values for each size
		$this->assertEquals(3, $method->invoke($public, 'x-small'));
		$this->assertEquals(2, $method->invoke($public, 'small'));
		$this->assertEquals(1, $method->invoke($public, 'medium'));
		$this->assertEquals(0, $method->invoke($public, 'large'));
	}

	public function test_size_values() {
		$public = new CDG_Elements_Public('cdg-elements', '1.0.0');
		$reflection = new ReflectionClass($public);
		$method = $reflection->getMethod('get_size_value');
		$method->setAccessible(true);

		// Test size values
		$this->assertEquals(24, $method->invoke($public, 'medium'));
				$this->assertEquals(36, $method->invoke($public, 'large'));
				$this->assertEquals(24, $method->invoke($public, 'invalid-size')); // Should default to medium
			}
		
			public function test_element_activation() {
				global $wpdb;
				$table_name = $wpdb->prefix . 'cdg_elements';
		
				// Create inactive element
				$element_data = array(
					'post_id' => $this->post_id,
					'element_text' => 'Inactive Element',
					'font_family' => 'Arial',
					'color' => '#000000',
					'size' => 'medium',
					'rotation' => 0,
					'position_x' => 100,
					'position_y' => 100,
					'is_active' => 0
				);
		
				$wpdb->insert($table_name, $element_data);
				$element_id = $wpdb->insert_id;
		
				// Verify inactive element is not rendered
				$this->go_to(get_permalink($this->post_id));
				ob_start();
				do_action('wp_footer');
				$output = ob_get_clean();
		
				$this->assertStringNotContainsString('Inactive Element', $output);
		
				// Activate element
				$wpdb->update(
					$table_name,
					array('is_active' => 1),
					array('id' => $element_id)
				);
		
				// Verify active element is rendered
				ob_start();
				do_action('wp_footer');
				$output = ob_get_clean();
		
				$this->assertStringContainsString('Inactive Element', $output);
			}
		
			public function test_rotation_validation() {
				// Test valid rotation values
				$valid_rotations = array(-5, -3, 0, 3, 5);
				
				$admin_ajax = new CDG_Elements_Admin_Ajax();
				$reflection = new ReflectionClass($admin_ajax);
				$method = $reflection->getMethod('sanitize_rotation');
				$method->setAccessible(true);
		
				foreach ($valid_rotations as $rotation) {
					$this->assertEquals($rotation, $method->invoke($admin_ajax, $rotation));
				}
		
				// Test invalid rotation values
				$invalid_rotations = array(-10, -4, 1, 4, 10);
				foreach ($invalid_rotations as $rotation) {
					$this->assertEquals(0, $method->invoke($admin_ajax, $rotation));
				}
			}
		
			public function test_color_validation() {
				$admin_ajax = new CDG_Elements_Admin_Ajax();
				$reflection = new ReflectionClass($admin_ajax);
				$method = $reflection->getMethod('sanitize_elements');
				$method->setAccessible(true);
		
				// Test valid colors
				$valid_elements = array(
					array(
						'text' => 'Test',
						'font' => 'Arial',
						'color' => '#000000',
						'size' => 'medium',
						'rotation' => 0,
						'position' => array('x' => 100, 'y' => 100)
					)
				);
		
				$sanitized = $method->invoke($admin_ajax, $valid_elements);
				$this->assertEquals('#000000', $sanitized[0]['color']);
		
				// Test invalid colors
				$invalid_elements = array(
					array(
						'text' => 'Test',
						'font' => 'Arial',
						'color' => 'not-a-color',
						'size' => 'medium',
						'rotation' => 0,
						'position' => array('x' => 100, 'y' => 100)
					)
				);
		
				$sanitized = $method->invoke($admin_ajax, $invalid_elements);
				$this->assertEquals('', $sanitized[0]['color']); // Should be empty after sanitization
			}
		
			public function test_position_boundaries() {
				global $wpdb;
				$table_name = $wpdb->prefix . 'cdg_elements';
		
				// Test extreme positions
				$positions = array(
					array('x' => -1000, 'y' => -1000),
					array('x' => 99999, 'y' => 99999),
					array('x' => 0, 'y' => 0),
					array('x' => PHP_INT_MAX, 'y' => PHP_INT_MAX)
				);
		
				foreach ($positions as $position) {
					$element_data = array(
						'post_id' => $this->post_id,
						'element_text' => 'Position Test',
						'font_family' => 'Arial',
						'color' => '#000000',
						'size' => 'medium',
						'rotation' => 0,
						'position_x' => $position['x'],
						'position_y' => $position['y'],
						'is_active' => 1
					);
		
					// Should successfully insert without errors
					$result = $wpdb->insert($table_name, $element_data);
					$this->assertEquals(1, $result);
				}
			}
		
			public function test_multiple_elements_order() {
				global $wpdb;
				$table_name = $wpdb->prefix . 'cdg_elements';
		
				// Create multiple elements
				$elements = array(
					array('text' => 'First', 'position_x' => 100),
					array('text' => 'Second', 'position_x' => 200),
					array('text' => 'Third', 'position_x' => 300)
				);
		
				foreach ($elements as $element) {
					$wpdb->insert($table_name, array(
						'post_id' => $this->post_id,
						'element_text' => $element['text'],
						'font_family' => 'Arial',
						'color' => '#000000',
						'size' => 'medium',
						'rotation' => 0,
						'position_x' => $element['position_x'],
						'position_y' => 100,
						'is_active' => 1
					));
				}
		
				// Verify elements are rendered in correct order
				$this->go_to(get_permalink($this->post_id));
				ob_start();
				do_action('wp_footer');
				$output = ob_get_clean();
		
				$first_pos = strpos($output, 'First');
				$second_pos = strpos($output, 'Second');
				$third_pos = strpos($output, 'Third');
		
				$this->assertLessThan($second_pos, $first_pos);
				$this->assertLessThan($third_pos, $second_pos);
			}
		
			public function tearDown(): void {
				parent::tearDown();
				
				// Clean up test data
				global $wpdb;
				$wpdb->query("TRUNCATE TABLE {$wpdb->prefix}cdg_elements");
				delete_post_meta($this->post_id, '_cdg_elements_enabled');
				wp_delete_post($this->post_id, true);
			}
		}