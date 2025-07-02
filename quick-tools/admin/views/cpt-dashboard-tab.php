<?php
/**
 * CPT Dashboard tab content
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Handle form submission
if (isset($_POST['submit']) && wp_verify_nonce($_POST['_wpnonce'], 'quick-tools-cpt-settings')) {
    update_option('quick_tools_settings', $_POST['quick_tools_settings']);
    echo '<div class="notice notice-success is-dismissible"><p>' . __('Settings saved!', 'quick-tools') . '</p></div>';
}

$settings = get_option('quick_tools_settings', array());
$post_types = Quick_Tools_CPT_Dashboard::get_available_post_types();
?>

<div class="qt-tab-panel" id="cpt-dashboard-panel">
    <form method="post" action="">
        <?php wp_nonce_field('quick-tools-cpt-settings'); ?>
        
        <div class="qt-settings-section">
            <h2><?php _e('Custom Post Type Dashboard Widgets', 'quick-tools'); ?></h2>
            <p class="description">
                <?php _e('Add quick-creation widgets to your dashboard for custom post types.', 'quick-tools'); ?>
            </p>

            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row"><?php _e('Enable CPT Widgets', 'quick-tools'); ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" name="quick_tools_settings[show_cpt_widgets]" 
                                           value="1" <?php checked(isset($settings['show_cpt_widgets']) ? $settings['show_cpt_widgets'] : true, true); ?>>
                                    <?php _e('Show custom post type widgets on the dashboard', 'quick-tools'); ?>
                                </label>
                                <p class="description">
                                    <?php _e('When enabled, selected post types will have quick-add widgets on the dashboard.', 'quick-tools'); ?>
                                </p>
                            </fieldset>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('Show Recent Posts', 'quick-tools'); ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" name="quick_tools_settings[show_recent_posts]" 
                                           value="1" <?php checked(isset($settings['show_recent_posts']) ? $settings['show_recent_posts'] : true, true); ?>>
                                    <?php _e('Display recent posts in CPT widgets', 'quick-tools'); ?>
                                </label>
                                <p class="description">
                                    <?php _e('Show a list of recently modified posts for each post type.', 'quick-tools'); ?>
                                </p>
                            </fieldset>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('Recent Posts Limit', 'quick-tools'); ?></th>
                        <td>
                            <input type="number" name="quick_tools_settings[recent_posts_limit]" 
                                   value="<?php echo esc_attr(isset($settings['recent_posts_limit']) ? $settings['recent_posts_limit'] : 3); ?>"
                                   min="1" max="10" class="small-text">
                            <p class="description">
                                <?php _e('Number of recent posts to show in each CPT widget (1-10).', 'quick-tools'); ?>
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="qt-settings-section">
            <h2><?php _e('Select Post Types', 'quick-tools'); ?></h2>
            <p class="description">
                <?php _e('Choose which custom post types should have dashboard widgets. Only post types you have permission to edit will be shown.', 'quick-tools'); ?>
            </p>

            <?php if (empty($post_types)) : ?>
                <div class="qt-no-cpts">
                    <p><strong><?php _e('No custom post types found.', 'quick-tools'); ?></strong></p>
                    <p><?php _e('Custom post types are typically added by themes or plugins. Once you have custom post types available, they will appear here for selection.', 'quick-tools'); ?></p>
                    <p>
                        <a href="<?php echo admin_url('plugins.php'); ?>" class="button button-secondary">
                            <?php _e('Manage Plugins', 'quick-tools'); ?>
                        </a>
                        <a href="<?php echo admin_url('themes.php'); ?>" class="button button-secondary">
                            <?php _e('Manage Themes', 'quick-tools'); ?>
                        </a>
                    </p>
                </div>
            <?php else : ?>
                <div class="qt-cpt-selection-grid">
                    <?php
                    $selected_cpts = isset($settings['selected_cpts']) ? $settings['selected_cpts'] : array();
                    
                    foreach ($post_types as $post_type) {
                        $checked = in_array($post_type->name, $selected_cpts);
                        $stats = Quick_Tools_CPT_Dashboard::get_post_type_stats($post_type->name);
                        ?>
                        <div class="qt-cpt-option">
                            <label class="qt-cpt-card <?php echo $checked ? 'selected' : ''; ?>">
                                <input type="checkbox" 
                                       name="quick_tools_settings[selected_cpts][]" 
                                       value="<?php echo esc_attr($post_type->name); ?>" 
                                       <?php checked($checked, true); ?>>
                                
                                <div class="qt-cpt-card-header">
                                    <h4><?php echo esc_html($post_type->labels->name); ?></h4>
                                    <span class="qt-cpt-slug"><?php echo esc_html($post_type->name); ?></span>
                                </div>
                                
                                <div class="qt-cpt-card-stats">
                                    <div class="qt-stat">
                                        <span class="qt-stat-number"><?php echo number_format_i18n($stats['published']); ?></span>
                                        <span class="qt-stat-label"><?php _e('Published', 'quick-tools'); ?></span>
                                    </div>
                                    
                                    <?php if ($stats['draft'] > 0) : ?>
                                    <div class="qt-stat">
                                        <span class="qt-stat-number"><?php echo number_format_i18n($stats['draft']); ?></span>
                                        <span class="qt-stat-label"><?php _e('Drafts', 'quick-tools'); ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (!empty($post_type->description)) : ?>
                                <div class="qt-cpt-card-description">
                                    <p><?php echo esc_html($post_type->description); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <div class="qt-cpt-card-actions">
                                    <a href="<?php echo admin_url('post-new.php?post_type=' . $post_type->name); ?>" 
                                       class="button button-small" target="_blank">
                                        <?php _e('Add New', 'quick-tools'); ?>
                                    </a>
                                    <a href="<?php echo admin_url('edit.php?post_type=' . $post_type->name); ?>" 
                                       class="button button-small button-secondary" target="_blank">
                                        <?php _e('Manage', 'quick-tools'); ?>
                                    </a>
                                </div>
                            </label>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                
                <p class="description">
                    <?php printf(__('Select the post types you want to have quick-add widgets on your dashboard. Currently showing %d available post types.', 'quick-tools'), count($post_types)); ?>
                </p>
            <?php endif; ?>
        </div>

        <?php if (!empty($post_types)) : ?>
        <div class="qt-settings-section">
            <h2><?php _e('Widget Features', 'quick-tools'); ?></h2>
            <div class="qt-feature-list">
                <div class="qt-feature">
                    <span class="dashicons dashicons-plus-alt2 qt-feature-icon"></span>
                    <div class="qt-feature-content">
                        <h4><?php _e('Quick Creation', 'quick-tools'); ?></h4>
                        <p><?php _e('Large, prominent buttons make it easy to create new posts of any type directly from the dashboard.', 'quick-tools'); ?></p>
                    </div>
                </div>
                
                <div class="qt-feature">
                    <span class="dashicons dashicons-chart-bar qt-feature-icon"></span>
                    <div class="qt-feature-content">
                        <h4><?php _e('Post Statistics', 'quick-tools'); ?></h4>
                        <p><?php _e('See at-a-glance statistics showing published posts, drafts, and other status counts.', 'quick-tools'); ?></p>
                    </div>
                </div>
                
                <div class="qt-feature">
                    <span class="dashicons dashicons-clock qt-feature-icon"></span>
                    <div class="qt-feature-content">
                        <h4><?php _e('Recent Activity', 'quick-tools'); ?></h4>
                        <p><?php _e('View recently modified posts with quick access to edit them directly from the dashboard.', 'quick-tools'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php submit_button(); ?>
    </form>
</div>