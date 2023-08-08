<?php
defined('ABSPATH') || exit;

add_action('admin_init', function () {
    global $shf_options;

    if (isset($shf_options['page_add_tags'])) {
        register_taxonomy_for_object_type('post_tag', 'page');
    }

    if (isset($shf_options['page_add_categories'])) {
        register_taxonomy_for_object_type('category', 'page');
    }
});

add_action('admin_menu', function() {
    add_options_page('Simple Header & Footer', 'Simple Header & Footer', 'manage_options', 'simple-header-footer/admin/options.php');
});

if (isset($_GET['page']) && strpos($_GET['page'], 'simple-header-footer/') === 0) {
    header('X-XSS-Protection: 0');
    add_action('admin_enqueue_scripts', function () {
        wp_enqueue_style('shf', plugins_url('simple-header-footer') . '/admin/admin.css', [], time());
        wp_enqueue_code_editor(['type' => 'php']);
    });
}

function shf_save_post($post_id) {
    if (!isset($_POST['shf'])) return;

    if (isset($_POST['post_type']) && 'page' == $_POST['post_type']) {
        if (!current_user_can('edit_page', $post_id))
            return;
    } else {
        if (!current_user_can('edit_post', $post_id))
            return;
    }

    if (!wp_verify_nonce($_POST['shf'], plugin_basename(__FILE__)))
        return;
}