<?php
/**
 * Documentation tab content
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Handle form submission for documentation settings only
if (isset($_POST['submit_documentation'])) {
    // Verify nonce
    if (!wp_verify_nonce($_POST['_wpnonce'], 'quick-tools-documentation-settings')) {
        wp_die('Security check failed');
    }
    
    // Get existing settings
    $existing_settings = get_option('quick_tools_settings', array());
    
    // Debug: Log what we're receiving
    error_log('Quick Tools Debug - Documentation Form POST data: ' . print_r($_POST, true));
    error_log('Quick Tools Debug - Existing settings before save: ' . print_r($existing_settings, true));
    
    // Process documentation settings
    $existing_settings['show_documentation_widgets'] = isset($_POST['show_documentation_widgets']) ? 1 : 0;
    $existing_settings['show_documentation_status'] = isset($_POST['show_documentation_status']) ? 1 : 0;
    $existing_settings['documentation_widget_limit'] = isset($_POST['documentation_widget_limit']) ? 
        max(1, min(10, intval($_POST['documentation_widget_limit']))) : 5;
    
    // Debug: Log what we're saving
    error_log('Quick Tools Debug - Settings after processing: ' . print_r($existing_settings, true));
    
    // Save settings
    $save_result = update_option('quick_tools_settings', $existing_settings);
    error_log('Quick Tools Debug - Save result: ' . ($save_result ? 'SUCCESS' : 'FAILED'));
    
    // Verify the save
    $saved_settings = get_option('quick_tools_settings', array());
    error_log('Quick Tools Debug - Settings after save: ' . print_r($saved_settings, true));
    
    echo '<div class="notice notice-success is-dismissible"><p>' . __('Documentation settings saved!', 'quick-tools') . '</p></div>';
}

$settings = get_option('quick_tools_settings', array());

// Debug: Log current settings when page loads
error_log('Quick Tools Debug - Documentation tab loaded with settings: ' . print_r($settings, true));
?>

<div class="qt-tab-panel" id="documentation-panel">
    <form method="post" action="">
        <?php wp_nonce_field('quick-tools-documentation-settings'); ?>
        
        <div class="qt-settings-section">
            <h2><?php _e('Documentation Dashboard Widgets', 'quick-tools'); ?></h2>
            <p class="description">
                <?php _e('Configure how documentation appears on the WordPress dashboard.', 'quick-tools'); ?>
            </p>

            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row"><?php _e('Enable Documentation Widgets', 'quick-tools'); ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" name="show_documentation_widgets" 
                                           value="1" <?php 
                                           $checked_value = isset($settings['show_documentation_widgets']) ? $settings['show_documentation_widgets'] : 1;
                                           if ($checked_value == 1) echo 'checked="checked"'; 
                                           ?>>
                                    <?php _e('Show documentation widgets on the dashboard', 'quick-tools'); ?>
                                </label>
                                <p class="description">
                                    <?php _e('When enabled, documentation will be organized by category in separate dashboard widgets.', 'quick-tools'); ?>
                                </p>
                            </fieldset>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('Items per Widget', 'quick-tools'); ?></th>
                        <td>
                            <input type="number" name="documentation_widget_limit" 
                                   value="<?php echo esc_attr(isset($settings['documentation_widget_limit']) ? $settings['documentation_widget_limit'] : 5); ?>"
                                   min="1" max="10" class="small-text">
                            <p class="description">
                                <?php _e('Maximum number of documentation items to show per category widget (1-10).', 'quick-tools'); ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('Status Indicators', 'quick-tools'); ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" name="show_documentation_status" 
                                           value="1" <?php 
                                           $checked_value = isset($settings['show_documentation_status']) ? $settings['show_documentation_status'] : 1;
                                           if ($checked_value == 1) echo 'checked="checked"'; 
                                           ?>>
                                    <?php _e('Show publication status in widgets', 'quick-tools'); ?>
                                </label>
                                <p class="description">
                                    <?php _e('Display status indicators (Published, Draft, etc.) next to documentation titles.', 'quick-tools'); ?>
                                </p>
                            </fieldset>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="qt-settings-section">
            <h2><?php _e('Documentation Categories', 'quick-tools'); ?></h2>
            <p class="description">
                <?php _e('Your documentation is automatically organized into these categories. Each category gets its own dashboard widget.', 'quick-tools'); ?>
            </p>

            <div class="qt-categories-overview">
                <?php
                $categories = get_terms(array(
                    'taxonomy' => Quick_Tools_Documentation::TAXONOMY,
                    'hide_empty' => false,
                ));

                if (!empty($categories)) {
                    echo '<div class="qt-categories-grid">';
                    foreach ($categories as $category) {
                        $doc_count = wp_count_posts(Quick_Tools_Documentation::POST_TYPE);
                        $category_count = get_posts(array(
                            'post_type' => Quick_Tools_Documentation::POST_TYPE,
                            'post_status' => 'publish',
                            'numberposts' => -1,
                            'tax_query' => array(
                                array(
                                    'taxonomy' => Quick_Tools_Documentation::TAXONOMY,
                                    'field' => 'term_id',
                                    'terms' => $category->term_id,
                                ),
                            ),
                            'fields' => 'ids'
                        ));
                        
                        echo '<div class="qt-category-card">';
                        echo '<h4>' . esc_html($category->name) . '</h4>';
                        echo '<p class="qt-category-count">' . count($category_count) . ' ' . __('items', 'quick-tools') . '</p>';
                        if (!empty($category->description)) {
                            echo '<p class="qt-category-description">' . esc_html($category->description) . '</p>';
                        }
                        echo '<p class="qt-category-actions">';
                        echo '<a href="' . admin_url('post-new.php?post_type=' . Quick_Tools_Documentation::POST_TYPE . '&' . Quick_Tools_Documentation::TAXONOMY . '=' . $category->slug) . '" class="button button-small">' . __('Add New', 'quick-tools') . '</a> ';
                        echo '<a href="' . admin_url('edit.php?post_type=' . Quick_Tools_Documentation::POST_TYPE . '&' . Quick_Tools_Documentation::TAXONOMY . '=' . $category->slug) . '" class="button button-small button-secondary">' . __('View All', 'quick-tools') . '</a>';
                        echo '</p>';
                        echo '</div>';
                    }
                    echo '</div>';
                }
                ?>
            </div>

            <p>
                <a href="<?php echo admin_url('edit-tags.php?taxonomy=' . Quick_Tools_Documentation::TAXONOMY . '&post_type=' . Quick_Tools_Documentation::POST_TYPE); ?>" 
                   class="button button-secondary">
                    <?php _e('Manage Categories', 'quick-tools'); ?>
                </a>
                <a href="<?php echo admin_url('post-new.php?post_type=' . Quick_Tools_Documentation::POST_TYPE); ?>" 
                   class="button button-primary">
                    <?php _e('Add New Documentation', 'quick-tools'); ?>
                </a>
            </p>
        </div>

        <div class="qt-settings-section">
            <h2><?php _e('Documentation Features', 'quick-tools'); ?></h2>
            <div class="qt-feature-list">
                <div class="qt-feature">
                    <span class="dashicons dashicons-search qt-feature-icon"></span>
                    <div class="qt-feature-content">
                        <h4><?php _e('Search Documentation', 'quick-tools'); ?></h4>
                        <p><?php _e('Quickly find documentation with the built-in search functionality available in dashboard widgets.', 'quick-tools'); ?></p>
                    </div>
                </div>
                
                <div class="qt-feature">
                    <span class="dashicons dashicons-admin-users qt-feature-icon"></span>
                    <div class="qt-feature-content">
                        <h4><?php _e('Admin-Only Access', 'quick-tools'); ?></h4>
                        <p><?php _e('Documentation is only visible to users with admin privileges, keeping internal information secure.', 'quick-tools'); ?></p>
                    </div>
                </div>
                
                <div class="qt-feature">
                    <span class="dashicons dashicons-backup qt-feature-icon"></span>
                    <div class="qt-feature-content">
                        <h4><?php _e('Import/Export', 'quick-tools'); ?></h4>
                        <p><?php _e('Easily backup or transfer documentation between sites using the import/export feature.', 'quick-tools'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <?php submit_button(__('Save Documentation Settings', 'quick-tools'), 'primary', 'submit_documentation'); ?>
    </form>
</div>