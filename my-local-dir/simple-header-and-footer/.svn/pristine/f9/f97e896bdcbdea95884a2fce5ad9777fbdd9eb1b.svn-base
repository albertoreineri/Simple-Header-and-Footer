<?php
defined('ABSPATH') || exit;

function shaf_request($name, $default = null)
{
    if (!isset($_REQUEST[$name])) {
        return $default;
    }
    return stripslashes_deep(sanitize_text_field(wp_unslash($_REQUEST[$name])));
}

function shaf_base_textarea_cm($name, $type = '', $tips = '')
{
    global $options;

    if (!empty($type)) {
        $type = '-' . $type;
    }

    if (!isset($options[$name])) {
        $options[$name] = '';
    }

    if (is_array($options[$name])) {
        $options[$name] = implode("\n", $options[$name]);
    }

    echo '<textarea class="shaf-cm' . esc_attr($type) . '" name="options[' . esc_attr($name) . ']" onfocus="shaf_cm_on(this)">';
    echo esc_html($options[$name]);
    echo '</textarea>';
}
