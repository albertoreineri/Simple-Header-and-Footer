<?php

defined('ABSPATH') || exit;

if (!current_user_can('administrator')) {
    die();
}

load_plugin_textdomain('simple-header-footer', false, 'simple-header-footer/languages');


require_once __DIR__ . '/controls.php';

$dismissed = get_option('shaf_dismissed', []);

if (isset($_REQUEST['dismiss']) && check_admin_referer('dismiss')) {
    $dismissed[$_REQUEST['dismiss']] = 1;
    update_option('shaf_dismissed', $dismissed);
    wp_redirect('?page=simple-header-footer%2Fadmin%2Foptions.php');
    exit();
}

if (isset($_POST['save'])) {
    $options = shaf_request('options');
    update_option('shaf', $options);
} else {
    $options = get_option('shaf');
}
?>

<script>
    jQuery(function() {
        jQuery("textarea.shaf-cm").each(function() {
            wp.codeEditor.initialize(this);
        });
    });
</script>

<div class="wrap">
    <h2>Simple Header and Footer</h2>
    <form method="post" action="">
        <div class="shaf-card">
            <h3><?php esc_html_e('HEADER', 'simple-header-footer') ?></h3>
            <p><?php esc_html_e('Right after <head> tag', 'simple-header-footer') ?></p>
            <div class="container">
                <?php shaf_base_textarea_cm('head'); ?>
            </div>
        </div>
        <div class="shaf-card">
            <h3><?php esc_html_e('FOOTER', 'simple-header-footer') ?></h3>
            <p><?php esc_html_e('Before </body> tag', 'simple-header-footer') ?></p>
            <div class="container">
                <?php shaf_base_textarea_cm('footer'); ?>
            </div>
        </div>

        <p class="submit"><input type="submit" class="button-primary shaf-submit" name="save" value="<?php esc_attr_e('Save', 'simple-header-footer'); ?>"></p>
    </form>
</div>