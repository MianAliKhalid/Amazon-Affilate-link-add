<?php
/*
Plugin Name: Affiliate Links
Description: A plugin to add affiliate links with images and display them in posts. Made with ♥️ by Mian Ali Khalid
Version: 1.3.3
Author: Mian Ali Khalid
*/

// Register Custom Post Type
function create_affiliate_link_cpt() {
    $labels = array(
        'name' => _x('Affiliate Links', 'Post Type General Name', 'textdomain'),
        'singular_name' => _x('Affiliate Link', 'Post Type Singular Name', 'textdomain'),
        'menu_name' => __('Affiliate Links', 'textdomain'),
        'all_items' => __('All Affiliate Links', 'textdomain'),
        'add_new_item' => __('Add New Affiliate Link', 'textdomain'),
        'add_new' => __('Add New', 'textdomain'),
        'edit_item' => __('Edit Affiliate Link', 'textdomain'),
        'update_item' => __('Update Affiliate Link', 'textdomain'),
        'view_item' => __('View Affiliate Link', 'textdomain'),
        'view_items' => __('View Affiliate Links', 'textdomain'),
        'search_items' => __('Search Affiliate Link', 'textdomain'),
    );

    $args = array(
        'label' => __('Affiliate Link', 'textdomain'),
        'description' => __('Custom Post Type for Affiliate Links', 'textdomain'),
        'labels' => $labels,
        'supports' => array('title', 'thumbnail'),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
        'can export' => true,
        'has archive' => false,
        'exclude from search' => true,
        'publicly queryable' => false,
        'capability_type' => 'post',
    );
    register_post_type('affiliate_link', $args);
}
add_action('init', 'create_affiliate_link_cpt', 0);

// Add Meta Box for Affiliate URL and Image
function affiliate_link_meta_box() {
    add_meta_box(
        'affiliate_link_meta_box',
        __('Affiliate Link Details', 'textdomain'),
        'affiliate_link_meta_box_callback',
        'affiliate_link',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'affiliate_link_meta_box');

function affiliate_link_meta_box_callback($post) {
    wp_nonce_field('save_affiliate_link_meta_box_data', 'affiliate_link_meta_box_nonce');
    $url = get_post_meta($post->ID, '_affiliate_link_url', true);
    $image_id = get_post_meta($post->ID, '_affiliate_link_image_id', true);
    $image_url = wp_get_attachment_url($image_id);
    ?>
    <p>
        <label for="affiliate_link_url"><?php _e('Affiliate Link URL', 'textdomain'); ?></label>
        <input type="url" id="affiliate_link_url" name="affiliate_link_url" value="<?php echo esc_attr($url); ?>" size="25" />
    </p>
    <p>
        <label for="affiliate_link_image"><?php _e('Affiliate Link Image', 'textdomain'); ?></label>
        <input type="hidden" id="affiliate_link_image_id" name="affiliate_link_image_id" value="<?php echo esc_attr($image_id); ?>" />
        <input type="button" id="upload_image_button" class="button" value="<?php _e('Upload Image', 'textdomain'); ?>" />
        <div id="affiliate_link_image_preview">
            <?php if ($image_url) : ?>
                <img src="<?php echo esc_url($image_url); ?>" style="max-width: 100%;" />
            <?php endif; ?>
        </div>
    </p>
    <?php
}

function save_affiliate_link_meta_box_data($post_id) {
    if (!isset($_POST['affiliate_link_meta_box_nonce'])) {
        return;
    }
    if (!wp_verify_nonce($_POST['affiliate_link_meta_box_nonce'], 'save_affiliate_link_meta_box_data')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    if (isset($_POST['affiliate_link_url'])) {
        $affiliate_link_url = sanitize_text_field($_POST['affiliate_link_url']);
        update_post_meta($post_id, '_affiliate_link_url', $affiliate_link_url);
    }
    if (isset($_POST['affiliate_link_image_id'])) {
        $affiliate_link_image_id = sanitize_text_field($_POST['affiliate_link_image_id']);
        update_post_meta($post_id, '_affiliate_link_image_id', $affiliate_link_image_id);
    }
}
add_action('save_post', 'save_affiliate_link_meta_box_data');

// Enqueue scripts for media uploader
function affiliate_link_admin_scripts() {
    wp_enqueue_media();
    wp_enqueue_script('affiliate-link-admin', plugins_url('affiliate-link-admin.js', __FILE__), array('jquery'), null, true);
}
add_action('admin_enqueue_scripts', 'affiliate_link_admin_scripts');

// Shortcode to Display Affiliate Links
function affiliate_link_shortcode($atts) {
    $atts = shortcode_atts(array(
        'id' => '',
    ), $atts, 'affiliate_link');

    $post_id = $atts['id'];
    $post = get_post($post_id);

    if (!$post) {
        return '';
    }

    $url = get_post_meta($post_id, '_affiliate_link_url', true);
    $image_id = get_post_meta($post_id, '_affiliate_link_image_id', true);
    $image_url = wp_get_attachment_url($image_id);
    $title = get_the_title($post_id);

    ob_start();
    ?>
    <div class="affiliate-link-card">
        <a href="<?php echo esc_url($url); ?>" target="_blank" rel="nofollow noopener noreferrer">
            <?php if ($image_url) : ?>
                <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($title); ?>" class="affiliate-link-image" />
            <?php endif; ?>
        </a>
        <div class="affiliate-link-content">
            <h3><?php echo esc_html($title); ?></h3>
            <a href="<?php echo esc_url($url); ?>" target="_blank" rel="nofollow noopener noreferrer" class="affiliate-link-button">Buy Now</a>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('affiliate_link', 'affiliate_link_shortcode');

// Add Meta Box to Post Editor Sidebar
function add_affiliate_link_sidebar_meta_box() {
    add_meta_box(
        'affiliate_link_sidebar_meta_box',
        __('Select Affiliate Link', 'textdomain'),
        'affiliate_link_sidebar_meta_box_callback',
        'post',
        'side'
    );
}
add_action('add_meta_boxes', 'add_affiliate_link_sidebar_meta_box');

function affiliate_link_sidebar_meta_box_callback($post) {
    wp_nonce_field('save_affiliate_link_sidebar_meta_box_data', 'affiliate_link_sidebar_meta_box_nonce');
    $selected_affiliate_link = get_post_meta($post->ID, '_selected_affiliate_link', true);

    $affiliate_links = get_posts(array(
        'post_type' => 'affiliate_link',
        'posts_per_page' => -1
    ));

    echo '<label for="affiliate_link_select">';
    _e('Select Affiliate Link', 'textdomain');
    echo '</label> ';
    echo '<select id="affiliate_link_select" name="affiliate_link_select">';
    echo '<option value="">None</option>';
    foreach ($affiliate_links as $affiliate_link) {
        echo '<option value="' . esc_attr($affiliate_link->ID) . '" ' . selected($selected_affiliate_link, $affiliate_link->ID, false) . '>' . esc_html($affiliate_link->post_title) . '</option>';
    }
    echo '</select>';
    echo '<button type="button" id="copy_shortcode_button" class="button">Copy Shortcode</button>';
}

function save_affiliate_link_sidebar_meta_box_data($post_id) {
    if (!isset($_POST['affiliate_link_sidebar_meta_box_nonce'])) {
        return;
    }
    if (!wp_verify_nonce($_POST['affiliate_link_sidebar_meta_box_nonce'], 'save_affiliate_link_sidebar_meta_box_data')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    if (isset($_POST['affiliate_link_select'])) {
        $selected_affiliate_link = sanitize_text_field($_POST['affiliate_link_select']);
        update_post_meta($post_id, '_selected_affiliate_link', $selected_affiliate_link);
    }
}
add_action('save_post', 'save_affiliate_link_sidebar_meta_box_data');

// Display Selected Affiliate Link in Post
function display_selected_affiliate_link($content) {
    if (is_single()) {
        global $post;
        $selected_affiliate_link = get_post_meta($post->ID, '_selected_affiliate_link', true);
        if ($selected_affiliate_link) {
            $content .= do_shortcode('[affiliate_link id="' . $selected_affiliate_link . '"]');
        }
    }
    return $content;
}
add_filter('the_content', 'display_selected_affiliate_link');

// Add Quick Button for Creating Shortcode
function add_affiliate_link_button($context) {
    $img = plugins_url('icon.png', __FILE__);
    $context .= '<a class="button thickbox" href="#TB_inline?width=400&height=400&inlineId=select-affiliate-link"><img src="' . $img . '" /> Add Affiliate Link</a>';
    return $context;
}
add_filter('media_buttons_context', 'add_affiliate_link_button');

function add_affiliate_link_thickbox() {
    ?>
    <div id="select-affiliate-link" style="display:none;">
        <h2>Select Affiliate Link</h2>
        <input type="text" id="affiliate-link-search" placeholder="Search Affiliate Links" />
        <select id="affiliate-link-dropdown">
            <?php
            $posts = get_posts(array('post_type' => 'affiliate_link', 'posts_per_page' => -1));
            foreach ($posts as $post) {
                echo '<option value="' . esc_attr($post->ID) . '">' . esc_html($post->post_title) . '</option>';
            }
            ?>
        </select>
        <button id="insert-affiliate-link">Insert</button>
    </div>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#affiliate-link-search').on('keyup', function() {
                var search = $(this).val().toLowerCase();
                $('#affiliate-link-dropdown option').each(function() {
                    var text = $(this).text().toLowerCase();
                    $(this).toggle(text.indexOf(search) !== -1);
                });
            });

            $('#insert-affiliate-link').click(function() {
                var id = $('#affiliate-link-dropdown').val();
                window.send_to_editor('[affiliate_link id="' + id + '"]');
                tb_remove();
            });

            $('#copy_shortcode_button').click(function() {
                var id = $('#affiliate_link_select').val();
                if (id) {
                    var shortcode = '[affiliate_link id="' + id + '"]';
                    navigator.clipboard.writeText(shortcode).then(function() {
                        alert('Shortcode copied to clipboard: ' + shortcode);
                    }, function(err) {
                        alert('Failed to copy shortcode: ', err);
                    });
                } else {
                    alert('Please select an affiliate link.');
                }
            });
        });
    </script>
    <?php
}
add_action('admin_footer', 'add_affiliate_link_thickbox');

// Enqueue styles for the frontend
function affiliate_link_frontend_styles() {
    wp_enqueue_style('affiliate-link-styles', plugins_url('affiliate-link-styles.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'affiliate_link_frontend_styles');

// Enqueue styles for the editor
function affiliate_link_editor_styles() {
    wp_enqueue_style('affiliate-link-editor-styles', plugins_url('affiliate-link-editor-styles.css', __FILE__));
}
add_action('enqueue_block_editor_assets', 'affiliate_link_editor_styles');

// Register Block
function affiliate_link_block_init() {
    wp_register_script(
        'affiliate-link-block',
        plugins_url('affiliate-link-block.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-data')
    );

    register_block_type('affiliate-link/block', array(
        'editor_script' => 'affiliate-link-block',
        'render_callback' => 'affiliate_link_render_callback',
    ));
}
add_action('init', 'affiliate_link_block_init');

function affiliate_link_render_callback($attributes) {
    $post_id = $attributes['id'];
    $post = get_post($post_id);

    if (!$post) {
        return '';
    }

    $url = get_post_meta($post_id, '_affiliate_link_url', true);
    $image_id = get_post_meta($post_id, '_affiliate_link_image_id', true);
    $image_url = wp_get_attachment_url($image_id);
    $title = get_the_title($post_id);

    ob_start();
    ?>
    <div class="affiliate-link-card">
        <a href="<?php echo esc_url($url); ?>" target="_blank" rel="nofollow noopener noreferrer">
            <?php if ($image_url) : ?>
                <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($title); ?>" class="affiliate-link-image" />
            <?php endif; ?>
        </a>
        <div class="affiliate-link-content">
            <h3><?php echo esc_html($title); ?></h3>
            <a href="<?php echo esc_url($url); ?>" target="_blank" rel="nofollow noopener noreferrer" class="affiliate-link-button">Buy Now</a>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
?>
