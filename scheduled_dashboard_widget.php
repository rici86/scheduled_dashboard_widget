<?php
/*
 * Plugin Name: Scheduled Posts and Custom Posts Dashboard Widget
 * Version: 1.0.0
 * Description: Adds a custom dashboard widget to display scheduled posts and custom post types.
 * Author: <a href="https://www.rici86.com">Rici86</a>
 * Text Domain: scheduled-dashboard-widget
 * Domain Path: /languages
 */

// Load the plugin's text domain for translations
function scheduled_dashboard_widget_load_textdomain() {
    load_plugin_textdomain('scheduled-dashboard-widget', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'scheduled_dashboard_widget_load_textdomain');

// Enqueue the custom CSS stylesheet and JS
function enqueue_custom_dashboard_widget_scripts() {
    wp_enqueue_style('scheduled_dashboard_widget', plugin_dir_url(__FILE__) . 'scheduled_dashboard_widget.css?ver=6.4.2');
    wp_enqueue_scripts('schedule_change', plugin_dir_url(__FILE__) . 'schedule_change.js',array(),'1.0.0', true);
}
add_action('admin_enqueue_scripts', 'enqueue_custom_dashboard_widget_scripts');

// Dashboard widget 
function scheduled_dashboard_widget() {
    wp_add_dashboard_widget(
        'scheduled_dashboard_widget',        // Widget ID
        __('Scheduled Posts and Custom Posts', 'scheduled-dashboard-widget'), // Translatable Widget Title
        'scheduled_dashboard_widget_content' // Widget Content Callback
    );
}
add_action('wp_dashboard_setup', 'scheduled_dashboard_widget');

function scheduled_dashboard_widget_content() {
    // Check if the filter form has been submitted
    if (isset($_POST['post_type_filter'])) {
        $selected_post_types = $_POST['post_type_filter'];
        update_user_meta(get_current_user_id(), 'custom_dashboard_post_type_filter', $selected_post_types);
    }

    // Get the selected post types from the user meta
    $selected_post_types = get_user_meta(get_current_user_id(), 'custom_dashboard_post_type_filter', true);

    // Get all registered post types
    $registered_post_types = get_post_types(array('public' => true), 'objects');

    // Output filter form
    ?>
    <div style="margin-bottom: 1rem;">
        <form method="post">
            <div class="scheduled-dashboard-filtering">
                <label><?php _e('Filter Post Types:', 'scheduled-dashboard-widget'); ?></label>
                <input type="submit" value="<?php _e('Filter', 'scheduled-dashboard-widget'); ?>">
            </div>
            <div class="scheduled-dashboard-check-container">
                <?php foreach ($registered_post_types as $post_type) : ?>
                    <label>
                        <input type="checkbox" name="post_type_filter[]" value="<?php echo esc_attr($post_type->name); ?>" <?php checked(in_array($post_type->name, $selected_post_types), true); ?>>
                        <?php echo esc_html($post_type->label); ?>
                    </label>
                    <br>
                <?php endforeach; ?>
            </div>
        </form>
    </div>
    <?php

    $selected_post_types = ($selected_post_types !== 'all') ? $selected_post_types : wp_list_pluck($registered_post_types, 'name');
    // Use the selected post types as default filter
    $args = array(
        'post_type'      => ($selected_post_types !== 'all') ? $selected_post_types : wp_list_pluck($registered_post_types, 'name'),
        'post_status'    => 'future',
        'orderby'        => 'date',
        'order'          => 'ASC',
        'posts_per_page' => -1,
        'post_type__in'  => $selected_post_types, // Use 'post_type__in' for multiple post types
    );

    $scheduled_posts = new WP_Query($args);

    if ($scheduled_posts->have_posts()) {
        echo '<table class="widefat striped" style="border: none;">';
        echo '<thead><tr><th style="white-space:nowrap;">' . __('When', 'scheduled-dashboard-widget') . '</th><th>' . __('Title', 'scheduled-dashboard-widget') . '</th><th style="white-space:nowrap;">' . __('Type', 'scheduled-dashboard-widget') . '</th><th></th></tr></thead>';
        echo '<tbody>';
        while ($scheduled_posts->have_posts()) {
            $scheduled_posts->the_post();
            echo '<tr>';
            echo '<td style="white-space: nowrap; font-size:12px;">' . get_the_date('D d/m, H:i') . '</td>';
            echo '<td><a href="' . get_edit_post_link() . '"><strong>' . get_the_title() . '</strong></a>';
            // Get post categories
            $post_categories = get_the_category();
            if (!empty($post_categories)) {
                echo '<br><span class="post-categories scheduled-cat" style="font-size:0.75rem;">(';
                $category_names = wp_list_pluck($post_categories, 'name');
                echo implode(', ', $category_names);
                echo ')</span>';
            }            
            echo '</td>';
            echo '<td style="font-size:12px;">' . esc_html($registered_post_types[get_post_type()]->label) . '</td>';
            echo '<td><a href="' . esc_url(get_preview_post_link(get_the_ID())) . '" target="_blank" class="button">' . __('Preview', 'scheduled-dashboard-widget') . '</a></td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        wp_reset_postdata();
    } else {
        echo __('No scheduled posts found.', 'scheduled-dashboard-widget');
    }

    // Display the widget footer 
    echo '<div class="scheduled-dashboard-footer">';
    echo '<span>' . __('Widget by Rici86', 'scheduled-dashboard-widget') . '</span>';
    echo '</div>';
}
add_action('wp_dashboard_setup', 'scheduled_dashboard_widget');

// SCHEDULER PAGE 
// function load_plugin_files() {
//     require_once plugin_dir_path(__FILE__) . 'scheduler.php';
// }
// add_action('plugins_loaded', 'load_plugin_files');