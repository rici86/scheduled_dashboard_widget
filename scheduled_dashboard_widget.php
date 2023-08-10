<?php
/*
Plugin Name: Scheduled Posts and Custom Posts Dashboard Widget
Description: Adds a custom dashboard widget to display scheduled posts and custom post types.
*/

function scheduled_dashboard_widget() {
    wp_add_dashboard_widget(
        'scheduled_dashboard_widget',        // Widget ID
        'Scheduled Posts and Custom Posts', // Widget Title
        'scheduled_dashboard_widget_content' // Widget Content Callback
    );
}
add_action('wp_dashboard_setup', 'scheduled_dashboard_widget');

function scheduled_dashboard_widget_content() {
    // Get the selected post types from the user meta
    $selected_post_types = get_user_meta(get_current_user_id(), 'custom_dashboard_post_type_filter', true);

    // Get all registered post types
    $registered_post_types = get_post_types(array('public' => true), 'objects');

    // Output filter form
    ?>
    <div style="margin-bottom: 1rem;">
        <form method="post">
		<div class="scheduled-dashboard-filtering">
            <label>Filter Post Types:</label>		
            <input type="submit" value="Filter">
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
    $args = array(
        'post_type'      => 'any', // Use 'any' as a placeholder for 'post_type', we'll use 'post_type__in' below
        'post_status'    => 'future',
        'orderby'        => 'date',
        'order'          => 'ASC',
        'posts_per_page' => -1,
        'post_type__in'  => $selected_post_types, // Use 'post_type__in' for multiple post types
    );

    $scheduled_posts = new WP_Query($args);

    if ($scheduled_posts->have_posts()) {
        echo '<table class="widefat striped" style="border: none;">';
        echo '<thead><tr><th style="white-space:nowrap;">When</th><th>Title</th><th style="white-space:nowrap;">Post Type</th><th></th></tr></thead>';
        echo '<tbody>';
        while ($scheduled_posts->have_posts()) {
            $scheduled_posts->the_post();
            echo '<tr>';
            echo '<td>' . get_the_date('d/m, H:i') . '</td>';
            echo '<td><a href="' . get_edit_post_link() . '">' . get_the_title() . '</a>';
            $post_categories = get_the_category();
            if (!empty($post_categories)) {
                echo '<br><span class="post-categories">(';
                $category_names = wp_list_pluck($post_categories, 'name');
                echo implode(', ', $category_names);
                echo ')</span>';
            }
            echo '</td>';
            echo '<td>' . esc_html($registered_post_types[get_post_type()]->label) . '</td>';
            echo '<td><a href="' . esc_url(get_preview_post_link(get_the_ID())) . '" target="_blank" class="button">Preview</a></td>';           
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        wp_reset_postdata();
    } else {
        echo 'No scheduled posts found.';
    }

    // Display the footer 
    echo '<div class="scheduled-dashboard-footer">';
    echo '<span>Powered by <a href="https://www.rici86.com" target="_blank">Rici86.com</a></span>';
    echo '</div>';
	
}

// Enqueue the custom CSS stylesheet
function enqueue_custom_dashboard_widget_css() {
    wp_enqueue_style('scheduled_dashboard_widget', plugin_dir_url(__FILE__) . 'scheduled_dashboard_widget.css');
}
add_action('admin_enqueue_scripts', 'enqueue_custom_dashboard_widget_css');


// Update user meta with selected post types filter
function update_post_type_filter() {
    if (isset($_POST['post_type_filter'])) {
        update_user_meta(get_current_user_id(), 'custom_dashboard_post_type_filter', $_POST['post_type_filter']);
    }
}
	
add_action('wp_dashboard_setup', 'scheduled_dashboard_widget');
add_action('admin_init', 'update_post_type_filter');
?>