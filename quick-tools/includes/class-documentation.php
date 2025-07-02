<?php
/**
 * The documentation-specific functionality of the plugin.
 */
class Quick_Tools_Documentation {

    /**
     * The post type name for documentation.
     */
    const POST_TYPE = 'qt_documentation';

    /**
     * The taxonomy name for documentation categories.
     */
    const TAXONOMY = 'qt_doc_category';

    /**
     * Default categories for documentation.
     */
    const DEFAULT_CATEGORIES = [
        'getting-started' => 'Getting Started',
        'advanced' => 'Advanced',
        'troubleshooting' => 'Troubleshooting'
    ];

    /**
     * Register the documentation post type.
     */
    public function register_post_type() {
        $labels = array(
            'name' => _x('Documentation', 'Post Type General Name', 'quick-tools'),
            'singular_name' => _x('Documentation', 'Post Type Singular Name', 'quick-tools'),
            'menu_name' => __('Documentation', 'quick-tools'),
            'name_admin_bar' => __('Documentation', 'quick-tools'),
            'archives' => __('Documentation Archives', 'quick-tools'),
            'attributes' => __('Documentation Attributes', 'quick-tools'),
            'parent_item_colon' => __('Parent Documentation:', 'quick-tools'),
            'all_items' => __('All Documentation', 'quick-tools'),
            'add_new_item' => __('Add New Documentation', 'quick-tools'),
            'add_new' => __('Add New', 'quick-tools'),
            'new_item' => __('New Documentation', 'quick-tools'),
            'edit_item' => __('Edit Documentation', 'quick-tools'),
            'update_item' => __('Update Documentation', 'quick-tools'),
            'view_item' => __('View Documentation', 'quick-tools'),
            'view_items' => __('View Documentation', 'quick-tools'),
            'search_items' => __('Search Documentation', 'quick-tools'),
        );

        $args = array(
            'label' => __('Documentation', 'quick-tools'),
            'description' => __('Internal documentation for website editors', 'quick-tools'),
            'labels' => $labels,
            'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields'),
            'taxonomies' => array(self::TAXONOMY),
            'hierarchical' => false,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 25,
            'menu_icon' => 'dashicons-media-document',
            'show_in_admin_bar' => true,
            'show_in_nav_menus' => false,
            'can_export' => true,
            'has_archive' => false,
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'capability_type' => 'post',
            'capabilities' => array(
                'edit_post' => 'manage_options',
                'read_post' => 'manage_options',
                'delete_post' => 'manage_options',
                'edit_posts' => 'manage_options',
                'edit_others_posts' => 'manage_options',
                'publish_posts' => 'manage_options',
                'read_private_posts' => 'manage_options',
            ),
            'show_in_rest' => false,
        );

        register_post_type(self::POST_TYPE, $args);
    }

    /**
     * Register the documentation category taxonomy.
     */
    public function register_taxonomy() {
        $labels = array(
            'name' => _x('Documentation Categories', 'Taxonomy General Name', 'quick-tools'),
            'singular_name' => _x('Documentation Category', 'Taxonomy Singular Name', 'quick-tools'),
            'menu_name' => __('Categories', 'quick-tools'),
            'all_items' => __('All Categories', 'quick-tools'),
            'parent_item' => __('Parent Category', 'quick-tools'),
            'parent_item_colon' => __('Parent Category:', 'quick-tools'),
            'new_item_name' => __('New Category Name', 'quick-tools'),
            'add_new_item' => __('Add New Category', 'quick-tools'),
            'edit_item' => __('Edit Category', 'quick-tools'),
            'update_item' => __('Update Category', 'quick-tools'),
            'view_item' => __('View Category', 'quick-tools'),
            'separate_items_with_commas' => __('Separate categories with commas', 'quick-tools'),
            'add_or_remove_items' => __('Add or remove categories', 'quick-tools'),
            'choose_from_most_used' => __('Choose from the most used', 'quick-tools'),
            'popular_items' => __('Popular Categories', 'quick-tools'),
            'search_items' => __('Search Categories', 'quick-tools'),
            'not_found' => __('Not Found', 'quick-tools'),
            'no_terms' => __('No categories', 'quick-tools'),
            'items_list' => __('Categories list', 'quick-tools'),
            'items_list_navigation' => __('Categories list navigation', 'quick-tools'),
        );

        $args = array(
            'labels' => $labels,
            'hierarchical' => false,
            'public' => false,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => false,
            'show_tagcloud' => false,
            'show_in_rest' => false,
            'capabilities' => array(
                'manage_terms' => 'manage_options',
                'edit_terms' => 'manage_options',
                'delete_terms' => 'manage_options',
                'assign_terms' => 'manage_options',
            ),
        );

        register_taxonomy(self::TAXONOMY, array(self::POST_TYPE), $args);

        // Create default categories if they don't exist
        $this->create_default_categories();
    }

    /**
     * Create default documentation categories.
     */
    private function create_default_categories() {
        foreach (self::DEFAULT_CATEGORIES as $slug => $name) {
            if (!term_exists($slug, self::TAXONOMY)) {
                wp_insert_term($name, self::TAXONOMY, array('slug' => $slug));
            }
        }
    }

    /**
     * Add documentation dashboard widgets.
     */
    public function add_dashboard_widgets() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $settings = get_option('quick_tools_settings', array());
        $show_widgets = isset($settings['show_documentation_widgets']) ? $settings['show_documentation_widgets'] : true;

        if (!$show_widgets) {
            return;
        }

        $categories = get_terms(array(
            'taxonomy' => self::TAXONOMY,
            'hide_empty' => false,
        ));

        foreach ($categories as $category) {
            wp_add_dashboard_widget(
                'qt_documentation_' . $category->slug,
                sprintf(__('Documentation: %s', 'quick-tools'), $category->name),
                array($this, 'render_dashboard_widget'),
                null,
                array('category' => $category)
            );
        }
    }

    /**
     * Render a documentation dashboard widget.
     */
    public function render_dashboard_widget($post, $callback_args) {
        $category = $callback_args['args']['category'];
        $settings = get_option('quick_tools_settings', array());
        $max_items = isset($settings['documentation_widget_limit']) ? intval($settings['documentation_widget_limit']) : 5;
        $show_status = isset($settings['show_documentation_status']) ? $settings['show_documentation_status'] : true;

        $docs = get_posts(array(
            'post_type' => self::POST_TYPE,
            'post_status' => 'publish',
            'numberposts' => $max_items,
            'orderby' => 'menu_order title',
            'order' => 'ASC',
            'tax_query' => array(
                array(
                    'taxonomy' => self::TAXONOMY,
                    'field' => 'term_id',
                    'terms' => $category->term_id,
                ),
            ),
        ));

        if (empty($docs)) {
            echo '<p>' . sprintf(__('No documentation found in %s category.', 'quick-tools'), $category->name) . '</p>';
            echo '<p><a href="' . admin_url('post-new.php?post_type=' . self::POST_TYPE) . '" class="button">' . __('Add Documentation', 'quick-tools') . '</a></p>';
            return;
        }

        echo '<div class="qt-documentation-widget">';
        
        foreach ($docs as $doc) {
            $view_url = admin_url('admin.php?page=qt-view-documentation&post_id=' . $doc->ID);
            $edit_url = admin_url('post.php?post=' . $doc->ID . '&action=edit');
            
            echo '<div class="qt-doc-item">';
            echo '<div class="qt-doc-actions">';
            echo '<a href="' . esc_url($view_url) . '" class="button button-primary">' . esc_html($doc->post_title) . '</a>';
            
            if ($show_status) {
                $status_class = 'qt-status-' . $doc->post_status;
                $status_text = ucfirst($doc->post_status);
                echo '<span class="qt-doc-status ' . $status_class . '">' . $status_text . '</span>';
            }
            
            echo '<a href="' . esc_url($edit_url) . '" class="qt-edit-link" title="' . __('Edit', 'quick-tools') . '">✏️</a>';
            echo '</div>';
            
            if (!empty($doc->post_excerpt)) {
                echo '<p class="qt-doc-excerpt">' . esc_html($doc->post_excerpt) . '</p>';
            }
            echo '</div>';
        }

        // Add search and manage links
        echo '<div class="qt-widget-footer">';
        echo '<a href="#" class="qt-search-trigger button button-secondary">' . __('Search Documentation', 'quick-tools') . '</a>';
        echo '<a href="' . admin_url('edit.php?post_type=' . self::POST_TYPE) . '" class="button button-secondary">' . __('Manage All', 'quick-tools') . '</a>';
        echo '</div>';

        echo '</div>';
    }

    /**
     * Add documentation viewer menu item.
     */
    public function add_documentation_viewer() {
        add_submenu_page(
            'edit.php?post_type=' . self::POST_TYPE,
            __('View Documentation', 'quick-tools'),
            __('View Documentation', 'quick-tools'),
            'manage_options',
            'qt-view-documentation',
            array($this, 'render_documentation_viewer')
        );
    }

    /**
     * Render the documentation viewer page.
     */
    public function render_documentation_viewer() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'quick-tools'));
        }

        if (!isset($_GET['post_id'])) {
            wp_die(__('No documentation specified.', 'quick-tools'));
        }

        $post = get_post(intval($_GET['post_id']));
        if (!$post || $post->post_type !== self::POST_TYPE) {
            wp_die(__('Documentation not found.', 'quick-tools'));
        }

        // Track view count
        $view_count = get_post_meta($post->ID, '_qt_view_count', true);
        $view_count = $view_count ? intval($view_count) + 1 : 1;
        update_post_meta($post->ID, '_qt_view_count', $view_count);
        update_post_meta($post->ID, '_qt_last_viewed', current_time('mysql'));

        ?>
        <div class="wrap qt-documentation-viewer">
            <div class="qt-doc-header">
                <h1><?php echo esc_html($post->post_title); ?></h1>
                <div class="qt-doc-meta">
                    <span class="qt-doc-date"><?php echo sprintf(__('Last updated: %s', 'quick-tools'), get_the_modified_date('', $post)); ?></span>
                    <a href="<?php echo esc_url(admin_url('post.php?post=' . $post->ID . '&action=edit')); ?>" class="button button-secondary">
                        <?php _e('Edit Documentation', 'quick-tools'); ?>
                    </a>
                </div>
            </div>
            
            <div class="qt-doc-content">
                <?php echo apply_filters('the_content', $post->post_content); ?>
            </div>

            <div class="qt-doc-footer">
                <a href="<?php echo esc_url(admin_url('edit.php?post_type=' . self::POST_TYPE)); ?>" class="button">
                    ← <?php _e('Back to All Documentation', 'quick-tools'); ?>
                </a>
            </div>
        </div>
        <?php
    }

    /**
     * Get documentation for search.
     */
    public function search_documentation($search_term) {
        if (empty($search_term)) {
            return array();
        }

        $docs = get_posts(array(
            'post_type' => self::POST_TYPE,
            'post_status' => 'publish',
            'numberposts' => 20,
            's' => $search_term,
            'orderby' => 'relevance',
        ));

        $results = array();
        foreach ($docs as $doc) {
            $categories = wp_get_post_terms($doc->ID, self::TAXONOMY);
            $category_names = array_map(function($cat) {
                return $cat->name;
            }, $categories);

            $results[] = array(
                'id' => $doc->ID,
                'title' => $doc->post_title,
                'excerpt' => $doc->post_excerpt ?: wp_trim_words($doc->post_content, 20),
                'categories' => $category_names,
                'view_url' => admin_url('admin.php?page=qt-view-documentation&post_id=' . $doc->ID),
                'edit_url' => admin_url('post.php?post=' . $doc->ID . '&action=edit'),
            );
        }

        return $results;
    }

    /**
     * Export documentation as JSON.
     */
    public function export_documentation($category_slug = '') {
        $args = array(
            'post_type' => self::POST_TYPE,
            'post_status' => 'publish',
            'numberposts' => -1,
        );

        if (!empty($category_slug)) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => self::TAXONOMY,
                    'field' => 'slug',
                    'terms' => $category_slug,
                ),
            );
        }

        $docs = get_posts($args);
        $export_data = array();

        foreach ($docs as $doc) {
            $categories = wp_get_post_terms($doc->ID, self::TAXONOMY);
            $category_slugs = array_map(function($cat) {
                return $cat->slug;
            }, $categories);

            $export_data[] = array(
                'title' => $doc->post_title,
                'content' => $doc->post_content,
                'excerpt' => $doc->post_excerpt,
                'categories' => $category_slugs,
                'menu_order' => $doc->menu_order,
                'date' => $doc->post_date,
            );
        }

        return array(
            'version' => QUICK_TOOLS_VERSION,
            'export_date' => current_time('mysql'),
            'documentation' => $export_data,
        );
    }

    /**
     * Import documentation from JSON.
     */
    public function import_documentation($import_data) {
        if (!isset($import_data['documentation']) || !is_array($import_data['documentation'])) {
            return false;
        }

        $imported = 0;
        $errors = array();

        foreach ($import_data['documentation'] as $doc_data) {
            try {
                $post_data = array(
                    'post_type' => self::POST_TYPE,
                    'post_title' => sanitize_text_field($doc_data['title']),
                    'post_content' => wp_kses_post($doc_data['content']),
                    'post_excerpt' => sanitize_textarea_field($doc_data['excerpt']),
                    'post_status' => 'publish',
                    'menu_order' => isset($doc_data['menu_order']) ? intval($doc_data['menu_order']) : 0,
                );

                $post_id = wp_insert_post($post_data);

                if (is_wp_error($post_id)) {
                    $errors[] = sprintf(__('Failed to import "%s": %s', 'quick-tools'), $doc_data['title'], $post_id->get_error_message());
                    continue;
                }

                // Assign categories
                if (isset($doc_data['categories']) && is_array($doc_data['categories'])) {
                    wp_set_post_terms($post_id, $doc_data['categories'], self::TAXONOMY);
                }

                $imported++;
            } catch (Exception $e) {
                $errors[] = sprintf(__('Failed to import "%s": %s', 'quick-tools'), $doc_data['title'], $e->getMessage());
            }
        }

        return array(
            'imported' => $imported,
            'errors' => $errors,
        );
    }
}