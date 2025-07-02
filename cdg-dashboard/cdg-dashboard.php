<?php
/*
Plugin Name: CDG Dashboard
Description: Adds widgets with buttons to quickly create new posts of selected Custom Post Types
Version: 3.0
Author: Zack Fink
*/

class MultipleCPTButtons {
	private $options;

	public function __construct() {
		add_action('admin_menu', array($this, 'add_plugin_page'));
		add_action('admin_init', array($this, 'page_init'));
		add_action('wp_dashboard_setup', array($this, 'add_dashboard_widgets'));
	}

	public function add_plugin_page() {
		add_options_page(
			'CPT Buttons Settings', 
			'CPT Buttons', 
			'manage_options', 
			'cpt-buttons-settings', 
			array($this, 'create_admin_page')
		);
	}

	public function create_admin_page() {
		$this->options = get_option('cpt_buttons_options', array());
		?>
		<div class="wrap">
			<h1>CPT Buttons Settings</h1>
			<form method="post" action="options.php">
			<?php
				settings_fields('cpt_buttons_option_group');
				do_settings_sections('cpt-buttons-settings');
				submit_button();
			?>
			</form>
		</div>
		<?php
	}

	public function page_init() {
		register_setting(
			'cpt_buttons_option_group',
			'cpt_buttons_options',
			array($this, 'sanitize')
		);

		add_settings_section(
			'cpt_buttons_setting_section',
			'Choose Custom Post Types',
			array($this, 'section_info'),
			'cpt-buttons-settings'
		);

		add_settings_field(
			'selected_cpts', 
			'Post Types', 
			array($this, 'selected_cpts_callback'), 
			'cpt-buttons-settings', 
			'cpt_buttons_setting_section'
		);      
	}

	public function sanitize($input) {
		$sanitary_values = array();
		if (isset($input['selected_cpts']) && is_array($input['selected_cpts'])) {
			$sanitary_values['selected_cpts'] = array_map('sanitize_text_field', $input['selected_cpts']);
		}
		return $sanitary_values;
	}

	public function section_info() {
		echo 'Select the Custom Post Types for the dashboard buttons below:';
	}

	public function selected_cpts_callback() {
		$post_types = get_post_types(array('_builtin' => false), 'objects');
		foreach ($post_types as $post_type) {
			$checked = (isset($this->options['selected_cpts']) && in_array($post_type->name, $this->options['selected_cpts'])) ? 'checked' : '';
			echo '<label><input type="checkbox" name="cpt_buttons_options[selected_cpts][]" value="' . $post_type->name . '" ' . $checked . '>' . $post_type->label . '</label><br>';
		}
	}

	public function add_dashboard_widgets() {
		$options = get_option('cpt_buttons_options', array());
		$selected_cpts = isset($options['selected_cpts']) ? $options['selected_cpts'] : array();
		
		foreach ($selected_cpts as $cpt) {
			wp_add_dashboard_widget(
				'custom_cpt_dashboard_widget_' . $cpt,
				'Add ' . get_post_type_object($cpt)->labels->singular_name,
				array($this, 'dashboard_widget_function'),
				null,
				array('cpt' => $cpt)
			);
		}
	}

	public function dashboard_widget_function($post, $callback_args) {
		$cpt = $callback_args['args']['cpt'];
		$post_type_object = get_post_type_object($cpt);
		
		if ($post_type_object) {
			echo '<p>Click the button below to quickly add a new ' . esc_html($post_type_object->labels->singular_name) . ':</p>';
			echo '<p><a href="' . esc_url(admin_url('post-new.php?post_type=' . $cpt)) . '" class="button button-primary">';
			echo 'Add New ' . esc_html($post_type_object->labels->singular_name);
			echo '</a></p>';
		} else {
			echo '<p>Selected post type not found. Please check the settings.</p>';
		}
	}
}

if (class_exists('MultipleCPTButtons')) {
	new MultipleCPTButtons();
}