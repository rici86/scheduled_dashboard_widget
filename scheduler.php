function add_scheduled_posts_page() {
    add_menu_page(
        'Scheduled Posts and Custom Posts',
        'Scheduler',
        'edit_posts',
        'scheduler',
        'render_scheduled_posts_page',
        'dashicons-clock', 
        45 
    );
}
add_action('admin_menu', 'add_scheduled_posts_page', '');

function render_scheduled_posts_page() {
    echo '<div class="wrap">';
    echo '<h1 class="wp-heading-inline">' . __('Scheduled Posts and Custom Posts', 'scheduler') . '</h1>';
    render_scheduled_posts_page_content(); // Add your page content here
    echo '</div>';
}

function render_scheduled_posts_page_content() {
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
                <label><?php _e('Filter Post Types:', 'scheduler'); ?></label>
                <input type="submit" value="<?php _e('Filter', 'scheduler'); ?>">
            </div>
            <div class="scheduled-dashboard-check-container">
                <?php foreach ($registered_post_types as $post_type) : ?>
                    <label>
                        <input type="checkbox" name="post_type_filter[]" value="<?php echo esc_attr($post_type->name); ?>" <?php checked(in_array($post_type->name, $selected_post_types), true); ?> >
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
        echo '<table class="wp-list-table widefat fixed striped table-view-list">';
        echo '<thead><tr><th style="white-space:nowrap;">' . __('When', 'scheduler') . '</th><th>' . __('Title', 'scheduler') . '</th><th style="white-space:nowrap;">' . __('Post Type', 'scheduler') . '</th><th></th></tr></thead>';
        echo '<tbody>';
        while ($scheduled_posts->have_posts()) {
            $scheduled_posts->the_post();
            echo '<tr>';
            echo '<td style="white-space: nowrap;">' . get_the_date('l d M Y, H:i') . '</td>';
            echo '<td><a href="' . get_edit_post_link() . '">' . get_the_title() . '</a>';
            // Get post categories
            $post_categories = get_the_category();
            if (!empty($post_categories)) {
                echo '<br><span class="post-categories scheduled-cat">(';
                $category_names = wp_list_pluck($post_categories, 'name');
                echo implode(', ', $category_names);
                echo ')</span>';
            }
            echo '</td>';
            echo '<td>' . esc_html($registered_post_types[get_post_type()]->label) . '</td>';
            echo '<td><a href="' . esc_url(get_preview_post_link(get_the_ID())) . '" target="_blank" class="button">' . __('Preview', 'scheduler') . '</a></td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        wp_reset_postdata();
    } else {
        echo __('No scheduled posts found.', 'scheduler');
    }
}