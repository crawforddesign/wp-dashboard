<?php
/**
 * The CPT dashboard functionality of the plugin.
 */
class Quick_Tools_CPT_Dashboard {

    /**
     * Add CPT dashboard widgets.
     */
    public function add_dashboard_widgets() {
        $settings = get_option('quick_tools_settings', array());
        $selected_cpts = isset($settings['selected_cpts']) ? $settings['selected_cpts'] : array();
        $show_widgets = isset($settings['show_cpt_widgets']) ? $settings['show_cpt_widgets'] : true;

        if (!$show_widgets || empty($selected_cpts)) {
            return;
        }

        foreach ($selected_cpts as $cpt) {
            $post_type_object = get_post_type_object($cpt);
            
            if (!$post_type_object) {
                continue;
            }

            // Check if user can create posts of this type
            if (!current_user_can($post_type_object->cap->create_posts)) {
                continue;
            }

            wp_add_dashboard_widget(
                'qt_cpt_widget_' . $cpt,
                sprintf(__('Quick Add: %s', 'quick-tools'), $post_type_object->labels->singular_name),
                array($this, 'render_cpt_widget'),
                null,
                array('cpt' => $cpt, 'post_type_object' => $post_type_object)
            );
        }
    }

    /**
     * Render a CPT dashboard widget.
     */
    public function render_cpt_widget($post, $callback_args) {
        $cpt = $callback_args['args']['cpt'];
        $post_type_object = $callback_args['args']['post_type_object'];

        if (!$post_type_object) {
            echo '<p>' . __('Post type not found. Please check the settings.', 'quick-tools') . '</p>';
            return;
        }

        $add_new_url = admin_url('post-new.php?post_type=' . $cpt);
        $manage_url = admin_url('edit.php?post_type=' . $cpt);

        // Get recent posts count
        $recent_posts = wp_count_posts($cpt);
        $published_count = isset($recent_posts->publish) ? $recent_posts->publish : 0;
        $draft_count = isset($recent_posts->draft) ? $recent_posts->draft : 0;

        echo '<div class="qt-cpt-widget">';
        
        // Main action button
        echo '<div class="qt-cpt-main-action">';
        echo '<a href="' . esc_url($add_new_url) . '" class="button button-primary button-hero">';
        echo '<span class="dashicons dashicons-plus-alt2"></span> ';
        echo sprintf(__('Add New %s', 'quick-tools'), esc_html($post_type_object->labels->singular_name));
        echo '</a>';
        echo '</div>';

        // Quick stats
        echo '<div class="qt-cpt-stats">';
        echo '<div class="qt-stat-item">';
        echo '<span class="qt-stat-number">' . number_format_i18n($published_count) . '</span>';
        echo '<span class="qt-stat-label">' . __('Published', 'quick-tools') . '</span>';
        echo '</div>';
        
        if ($draft_count > 0) {
            echo '<div class="qt-stat-item">';
            echo '<span class="qt-stat-number">' . number_format_i18n($draft_count) . '</span>';
            echo '<span class="qt-stat-label">' . __('Drafts', 'quick-tools') . '</span>';
            echo '</div>';
        }
        echo '</div>';

        // Recent posts
        $this->render_recent_posts($cpt, $post_type_object);

        // Quick actions
        echo '<div class="qt-cpt-actions">';
        echo '<a href="' . esc_url($manage_url) . '" class="button button-secondary">';
        echo sprintf(__('Manage All %s', 'quick-tools'), esc_html($post_type_object->labels->name));
        echo '</a>';
        
        // Add custom action for specific post types if needed
        $this->render_custom_actions($cpt, $post_type_object);
        
        echo '</div>';
        
        echo '</div>';
    }

    /**
     * Render recent posts for the CPT widget.
     */
    private function render_recent_posts($cpt, $post_type_object) {
        $settings = get_option('quick_tools_settings', array());
        $show_recent = isset($settings['show_recent_posts']) ? $settings['show_recent_posts'] : true;
        $recent_limit = isset($settings['recent_posts_limit']) ? intval($settings['recent_posts_limit']) : 3;

        if (!$show_recent) {
            return;
        }

        $recent_posts = get_posts(array(
            'post_type' => $cpt,
            'post_status' => array('publish', 'draft', 'pending'),
            'numberposts' => $recent_limit,
            'orderby' => 'modified',
            'order' => 'DESC',
        ));

        if (empty($recent_posts)) {
            return;
        }

        echo '<div class="qt-recent-posts">';
        echo '<h4>' . sprintf(__('Recent %s', 'quick-tools'), $post_type_object->labels->name) . '</h4>';
        echo '<ul class="qt-recent-list">';

        foreach ($recent_posts as $post) {
            $edit_url = admin_url('post.php?post=' . $post->ID . '&action=edit');
            $status_class = 'qt-status-' . $post->post_status;
            
            echo '<li class="qt-recent-item">';
            echo '<a href="' . esc_url($edit_url) . '" class="qt-recent-title">' . esc_html($post->post_title ?: __('(no title)', 'quick-tools')) . '</a>';
            echo '<span class="qt-recent-status ' . $status_class . '">' . ucfirst($post->post_status) . '</span>';
            echo '<span class="qt-recent-date">' . human_time_diff(strtotime($post->post_modified)) . ' ' . __('ago', 'quick-tools') . '</span>';
            echo '</li>';
        }

        echo '</ul>';
        echo '</div>';
    }

    /**
     * Render custom actions for specific post types.
     */
    private function render_custom_actions($cpt, $post_type_object) {
        // Hook for developers to add custom actions
        do_action('qt_cpt_custom_actions', $cpt, $post_type_object);

        // Add some common helpful actions based on post type
        switch ($cpt) {
            case 'product':
                if (class_exists('WooCommerce')) {
                    echo '<a href="' . admin_url('admin.php?page=wc-settings') . '" class="button button-small">' . __('WooCommerce Settings', 'quick-tools') . '</a>';
                }
                break;
                
            case 'event':
                // Common event plugin integrations could go here
                break;
                
            default:
                // Check if post type has categories or tags
                $taxonomies = get_object_taxonomies($cpt, 'objects');
                $public_taxonomies = array_filter($taxonomies, function($tax) {
                    return $tax->public && $tax->show_ui;
                });
                
                if (!empty($public_taxonomies)) {
                    $first_taxonomy = array_shift($public_taxonomies);
                    $taxonomy_url = admin_url('edit-tags.php?taxonomy=' . $first_taxonomy->name . '&post_type=' . $cpt);
                    echo '<a href="' . esc_url($taxonomy_url) . '" class="button button-small">';
                    echo sprintf(__('Manage %s', 'quick-tools'), $first_taxonomy->labels->name);
                    echo '</a>';
                }
                break;
        }
    }

    /**
     * Get available post types for selection.
     */
    public static function get_available_post_types() {
        $post_types = get_post_types(array(
            '_builtin' => false,
            'public' => true,
        ), 'objects');

        // Filter out our own documentation post type
        unset($post_types[Quick_Tools_Documentation::POST_TYPE]);

        // Also include some built-in types that might be useful
        $builtin_types = get_post_types(array(
            '_builtin' => true,
            'public' => true,
        ), 'objects');

        // Only include pages (posts are usually always accessible)
        if (isset($builtin_types['page'])) {
            $post_types['page'] = $builtin_types['page'];
        }

        return $post_types;
    }

    /**
     * Get post type statistics.
     */
    public static function get_post_type_stats($post_type) {
        $counts = wp_count_posts($post_type);
        
        return array(
            'published' => isset($counts->publish) ? $counts->publish : 0,
            'draft' => isset($counts->draft) ? $counts->draft : 0,
            'pending' => isset($counts->pending) ? $counts->pending : 0,
            'private' => isset($counts->private) ? $counts->private : 0,
            'total' => array_sum((array) $counts),
        );
    }

    /**
     * Check if a post type should be shown to current user.
     */
    private function user_can_access_post_type($post_type) {
        $post_type_object = get_post_type_object($post_type);
        
        if (!$post_type_object) {
            return false;
        }

        // Check if user can edit this post type
        return current_user_can($post_type_object->cap->edit_posts);
    }
}