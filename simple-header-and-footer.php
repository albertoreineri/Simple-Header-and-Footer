<?php

/*
  Plugin Name: Simple Header and Footer
  Plugin URI:
  Description: Easily and basic plugin that allows you to insert code in the header (between head tags) and in the footer (before the end body tag). Ideal for inserting Analytics scripts or other tools easily and quickly.
  Version: 1.0.0
  Requires PHP: 7.4
  Author: Alberto Reineri
  Author URI: https://albertoreineri.github.io
  License: GPLv2 or later
  License URI: https://www.gnu.org/licenses/gpl-2.0.html
  Disclaimer: Use at your own risk. No warranty expressed or implied is provided.
 */

/*
  Copyright 2023 Alberto Reineri (reinerialberto@gmail.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

defined('ABSPATH') || exit;

$shaf_options = get_option('shaf', []);

$shaf_is_mobile = false;
if (isset($_SERVER['HTTP_USER_AGENT']) && isset($shaf_options['mobile_user_agents_parsed'])) {
    $shaf_is_mobile = preg_match('/' . $shaf_options['mobile_user_agents_parsed'] . '/', sanitize_text_field(wp_unslash(strtolower($_SERVER['HTTP_USER_AGENT']))));
}

if (is_admin()) {
    require_once __DIR__ . '/admin/admin.php';
}

register_activation_hook(__FILE__, function () {
    $options = get_option('shaf');
    if (!is_array($options)) {
        $options = [];
    }
    // Compatibility with "already" installed
    if (!empty($options)) {
        $options['enable_php'] = 1;
    }
    // $options = array_merge(['enable_php'=>0, 'after' => '', 'before' => '', 'head' => '', 'body' => '', 'head_home' => '', 'footer' => ''], $options);
    $options = array_merge(['head' => '', 'footer' => ''], $options);
    for ($i = 1; $i <= 5; $i++) {
        $options['snippet_' . $i] = '';
        $options['generic_' . $i] = '';
    }
    $options['updated'] = time(); // Force an update if the old options match (otherwise the autoload is not saved)
    update_option('shaf', $options, true);
});

register_deactivation_hook(__FILE__, function () {
    $options = get_option('shaf');
    if ($options) {
        $options['updated'] = time();
        update_option('shaf', $options, false);
    }
});

add_action('wp_head', 'shaf_wp_head_post', 11);
function shaf_wp_head_post()
{
    shaf_execute_option('head', true);
}


add_action('wp_footer', 'shaf_wp_footer');
function shaf_wp_footer()
{
    shaf_execute_option('footer', true);
}

function shaf_replace($buffer)
{
    global $shaf_options, $post;

    for ($i = 1; $i <= 5; $i++) {
        if (empty($shaf_options['snippet_' . $i]))
            continue;
        $buffer = str_replace('[snippet_' . $i . ']', $shaf_options['snippet_' . $i], $buffer);
    }

    return $buffer;
}

function shaf_execute($buffer)
{
    global $shaf_options, $post;

    if (apply_filters('shaf_php_exec', !empty($shaf_options['enable_php']))) {
        ob_start();
        eval('?>' . $buffer);
        $buffer = ob_get_clean();
    }
    return $buffer;
}

function shaf_execute_option($key, $echo = false)
{
    global $shaf_options, $wpdb, $post;
    if (empty($shaf_options[$key]))
        return '';
    $buffer = shaf_replace($shaf_options[$key]);
    if ($echo)
        echo esc_html(shaf_execute($buffer));
    else
        return shaf_execute($buffer);
}

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'shaf_settings_link');
function shaf_settings_link(array $links)
{
    $url = get_admin_url() . "options-general.php?page=simple-header-and-footer%2Fadmin%2Foptions.php";
    $settings_link = '<a href="' . $url . '">' . __('Settings', 'simple-header-and-footer') . '</a>';
    $links[] = $settings_link;
    return $links;
}
