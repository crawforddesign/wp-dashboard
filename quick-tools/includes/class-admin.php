<?php
/**
 * The admin-specific functionality of the plugin.
 */
class Quick_Tools_Admin {

    /**
     * The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     */
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
    public function enqueue_styles($hook) {
        // Only load on our admin pages and dashboard
        if ($hook !== 'toplevel_page_quick-tools' && $hook !== 'index.php' && strpos($hook, 'qt_documentation') === false) {
            return;
        }

        wp_enqueue_style(
            $this->plugin_name,
            QUICK_TOOLS_PLUGIN_URL . 'admin/css/admin-style.css',
            array(),
            $this->version,
            'all'
        );
    }

    /**
     * Register the JavaScript for the admin area.
     */
    public function enqueue_scripts($hook) {
        // Only load on our admin pages and dashboard
        if ($hook !== 'toplevel_page_quick-tools' && $hook !== 'index.php' && strpos($hook, 'qt_documentation') === false) {
            return;
        }

        wp_enqueue_script(
            $this->plugin_name,
            QUICK_TOOLS_PLUGIN_URL . 'admin/js/admin-script.js',
            array('jquery'),
            $this->version,
            true
        );

        // Localize script for AJAX
        wp_localize_script(
            $this->plugin_name,
            'quickToolsAjax',
            array(
                'ajaxurl' => admin_url('ajax.php'),
                'nonce' => wp_create_nonce('quick_tools_nonce'),
                'strings' => array(
                    'searching' => __('Searching...', 'quick-tools'),
                    'no_results' => __('No documentation found.', 'quick-tools'),
                    'error' => __('An error occurred. Please try again.', 'quick-tools'),
                    'confirm_import' => __('Are you sure you want to import this documentation? This cannot be undone.', 'quick-tools'),
                    'export_success' => __('Documentation exported successfully!', 'quick-tools'),
                    'import_success' => __('Documentation imported successfully!', 'quick-tools'),
                )
            )
        );
    }

    /**
     * Add plugin admin menu.
     */
    public function add_plugin_admin_menu() {
        add_menu_page(
            __('Quick Tools', 'quick-tools'),
            __('Quick Tools', 'quick-tools'),
            'manage_options',
            $this->plugin_name,
            array($this, 'display_plugin_admin_page'),
            'dashicons-admin-tools',
            80
        );
    }

    /**
     * Render the settings page for this plugin.
     */
    public function display_plugin_admin_page() {
        include_once QUICK_TOOLS_PLUGIN_DIR . 'admin/views/admin-page.php';
    }

    /**
     * Register plugin settings.
     */
    public function register_settings() {
        register_setting(
            'quick_tools_settings_group',
            'quick_tools_settings',
            array($this, 'sanitize_settings')
        );

        // Documentation Settings Section
        add_settings_section(
            'qt_documentation_section',
            __('Documentation Settings', 'quick-tools'),
            array($this, 'documentation_section_callback'),
            'quick-tools-documentation'
        );

        add_settings_field(
            'show_documentation_widgets',
            __('Show Documentation Widgets', 'quick-tools'),
            array($this, 'checkbox_field_callback'),
            'quick-tools-documentation',
            'qt_documentation_section',
            array(
                'field' => 'show_documentation_widgets',
                'description' => __('Display documentation widgets on the dashboard.', 'quick-tools')
            )
        );

        add_settings_field(
            'documentation_widget_limit',
            __('Max Items per Widget', 'quick-tools'),
            array($this, 'number_field_callback'),
            'quick-tools-documentation',
            'qt_documentation_section',
            array(
                'field' => 'documentation_widget_limit',
                'min' => 1,
                'max' => 10,
                'default' => 5,
                'description' => __('Maximum number of documentation items to show per category widget.', 'quick-tools')
            )
        );

        add_settings_field(
            'show_documentation_status',
            __('Show Status Indicators', 'quick-tools'),
            array($this, 'checkbox_field_callback'),
            'quick-tools-documentation',
            'qt_documentation_section',
            array(
                'field' => 'show_documentation_status',
                'description' => __('Show publication status (Published, Draft, etc.) in widgets.', 'quick-tools')
            )
        );

        // CPT Dashboard Settings Section
        add_settings_section(
            'qt_cpt_section',
            __('Custom Post Type Settings', 'quick-tools'),
            array($this, 'cpt_section_callback'),
            'quick-tools-cpt'
        );

        add_settings_field(
            'show_cpt_widgets',
            __('Show CPT Widgets', 'quick-tools'),
            array($this, 'checkbox_field_callback'),
            'quick-tools-cpt',
            'qt_cpt_section',
            array(
                'field' => 'show_cpt_widgets',
                'description' => __('Display custom post type quick-add widgets on the dashboard.', 'quick-tools')
            )
        );

        add_settings_field(
            'selected_cpts',
            __('Selected Post Types', 'quick-tools'),
            array($this, 'cpt_selection_callback'),
            'quick-tools-cpt',
            'qt_cpt_section'
        );

        add_settings_field(
            'show_recent_posts',
            __('Show Recent Posts', 'quick-tools'),
            array($this, 'checkbox_field_callback'),
            'quick-tools-cpt',
            'qt_cpt_section',
            array(
                'field' => 'show_recent_posts',
                'description' => __('Show recent posts in CPT widgets.', 'quick-tools')
            )
        );

        add_settings_field(
            'recent_posts_limit',
            __('Recent Posts Limit', 'quick-tools'),
            array($this, 'number_field_callback'),
            'quick-tools-cpt',
            'qt_cpt_section',
            array(
                'field' => 'recent_posts_limit',
                'min' => 1,
                'max' => 10,
                'default' => 3,
                'description' => __('Number of recent posts to show in CPT widgets.', 'quick-tools')
            )
        );
    }

    /**
     * Sanitize settings before saving.
     */
    public function sanitize_settings($input) {
        $sanitized = array();

        // Boolean fields
        $boolean_fields = array(
            'show_documentation_widgets',
            'show_documentation_status',
            'show_cpt_widgets',
            'show_recent_posts'
        );

        foreach ($boolean_fields as $field) {
            $sanitized[$field] = isset($input[$field]) ? true : false;
        }

        // Number fields
        $sanitized['documentation_widget_limit'] = isset($input['documentation_widget_limit']) 
            ? max(1, min(10, intval($input['documentation_widget_limit']))) 
            : 5;

        $sanitized['recent_posts_limit'] = isset($input['recent_posts_limit']) 
            ? max(1, min(10, intval($input['recent_posts_limit']))) 
            : 3;

        // Array fields
        $sanitized['selected_cpts'] = isset($input['selected_cpts']) && is_array($input['selected_cpts']) 
            ? array_map('sanitize_text_field', $input['selected_cpts']) 
            : array();

        return $sanitized;
    }

    /**
     * Section callbacks.
     */
    public function documentation_section_callback() {
        echo '<p>' . __('Configure how documentation is displayed on the dashboard.', 'quick-tools') . '</p>';
    }

    public function cpt_section_callback() {
        echo '<p>' . __('Choose which custom post types to show as quick-add widgets on the dashboard.', 'quick-tools') . '</p>';
    }

    /**
     * Field callbacks.
     */
    public function checkbox_field_callback($args) {
        $settings = get_option('quick_tools_settings', array());
        $field = $args['field'];
        $checked = isset($settings[$field]) ? $settings[$field] : true;
        
        echo '<label>';
        echo '<input type="checkbox" name="quick_tools_settings[' . $field . ']" value="1" ' . checked($checked, true, false) . '>';
        echo ' ' . $args['description'];
        echo '</label>';
    }

    public function number_field_callback($args) {
        $settings = get_option('quick_tools_settings', array());
        $field = $args['field'];
        $value = isset($settings[$field]) ? $settings[$field] : $args['default'];
        
        echo '<input type="number" name="quick_tools_settings[' . $field . ']" value="' . esc_attr($value) . '" ';
        echo 'min="' . $args['min'] . '" max="' . $args['max'] . '" class="small-text">';
        if (isset($args['description'])) {
            echo '<p class="description">' . $args['description'] . '</p>';
        }
    }

    public function cpt_selection_callback() {
        $settings = get_option('quick_tools_settings', array());
        $selected_cpts = isset($settings['selected_cpts']) ? $settings['selected_cpts'] : array();
        $post_types = Quick_Tools_CPT_Dashboard::get_available_post_types();

        if (empty($post_types)) {
            echo '<p>' . __('No custom post types found.', 'quick-tools') . '</p>';
            return;
        }

        echo '<div class="qt-cpt-selection">';
        foreach ($post_types as $post_type) {
            $checked = in_array($post_type->name, $selected_cpts);
            echo '<label class="qt-cpt-option">';
            echo '<input type="checkbox" name="quick_tools_settings[selected_cpts][]" value="' . esc_attr($post_type->name) . '" ' . checked($checked, true, false) . '>';
            echo ' <strong>' . esc_html($post_type->labels->name) . '</strong>';
            echo ' <span class="qt-cpt-description">(' . esc_html($post_type->name) . ')</span>';
            echo '</label><br>';
        }
        echo '</div>';
        echo '<p class="description">' . __('Select which post types should have quick-add widgets on the dashboard.', 'quick-tools') . '</p>';
    }

    /**
     * AJAX handler for documentation search.
     */
    public function ajax_search_documentation() {
        check_ajax_referer('quick_tools_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions.', 'quick-tools'));
        }

        $search_term = sanitize_text_field($_POST['search_term']);
        $documentation = new Quick_Tools_Documentation();
        $results = $documentation->search_documentation($search_term);

        wp_send_json_success($results);
    }

    /**
     * AJAX handler for documentation export.
     */
    public function ajax_export_documentation() {
        check_ajax_referer('quick_tools_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions.', 'quick-tools'));
        }

        $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
        $documentation = new Quick_Tools_Documentation();
        $export_data = $documentation->export_documentation($category);

        // Set headers for file download
        $filename = 'quick-tools-documentation-' . date('Y-m-d-H-i-s') . '.json';
        
        wp_send_json_success(array(
            'data' => $export_data,
            'filename' => $filename
        ));
    }

    /**
     * AJAX handler for documentation import.
     */
    public function ajax_import_documentation() {
        check_ajax_referer('quick_tools_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions.', 'quick-tools'));
        }

        if (!isset($_FILES['import_file'])) {
            wp_send_json_error(__('No file uploaded.', 'quick-tools'));
        }

        $file = $_FILES['import_file'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(__('File upload error.', 'quick-tools'));
        }

        $file_content = file_get_contents($file['tmp_name']);
        $import_data = json_decode($file_content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error(__('Invalid JSON file.', 'quick-tools'));
        }

        $documentation = new Quick_Tools_Documentation();
        $result = $documentation->import_documentation($import_data);

        if ($result === false) {
            wp_send_json_error(__('Import failed.', 'quick-tools'));
        }

        wp_send_json_success($result);
    }
}