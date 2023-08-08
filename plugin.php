<?php

/*
  Plugin Name: Simple Header and Footer
  Plugin URI:
  Description: Easily and basic plugin that allows you to insert code in the header (between head tags) and in the footer (before the end body tag). Ideal for inserting Analytics scripts or other tools easily and quickly.
  Version: 1.0.0
  Requires PHP: 7.4
  Author: Alberto Reineri
  Author URI: https://albertoreineri.it
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

$shf_options = get_option('shf', []);

$shf_is_mobile = false;
if (isset($_SERVER['HTTP_USER_AGENT']) && isset($shf_options['mobile_user_agents_parsed'])) {
    $shf_is_mobile = preg_match('/' . $shf_options['mobile_user_agents_parsed'] . '/', strtolower($_SERVER['HTTP_USER_AGENT']));
}

if (is_admin()) {
    require_once __DIR__ . '/admin/admin.php';
}

register_activation_hook(__FILE__, function () {
    $options = get_option('shf');
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
    update_option('shf', $options, true);
});

register_deactivation_hook(__FILE__, function () {
    $options = get_option('shf');
    if ($options) {
        $options['updated'] = time();
        update_option('shf', $options, false);
    }
});

add_action('wp_head', 'shf_wp_head_post', 11);
function shf_wp_head_post()
{
    shf_execute_option('head', true);
}


add_action('wp_footer', 'shf_wp_footer');
function shf_wp_footer()
{
    shf_execute_option('footer', true);
}

function shf_replace($buffer)
{
    global $shf_options, $post;

    for ($i = 1; $i <= 5; $i++) {
        if (empty($shf_options['snippet_' . $i]))
            continue;
        $buffer = str_replace('[snippet_' . $i . ']', $shf_options['snippet_' . $i], $buffer);
    }

    return $buffer;
}

function shf_execute($buffer)
{
    global $shf_options, $post;

    if (apply_filters('shf_php_exec', !empty($shf_options['enable_php']))) {
        ob_start();
        eval('?>' . $buffer);
        $buffer = ob_get_clean();
    }
    return $buffer;
}

function shf_execute_option($key, $echo = false)
{
    global $shf_options, $wpdb, $post;
    if (empty($shf_options[$key]))
        return '';
    $buffer = shf_replace($shf_options[$key]);
    if ($echo)
        echo shf_execute($buffer);
    else
        return shf_execute($buffer);
}

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'shf_settings_link');
function shf_settings_link(array $links)
{
    $url = get_admin_url() . "options-general.php?page=simple-header-footer%2Fadmin%2Foptions.php";
    $settings_link = '<a href="' . $url . '">' . __('Settings', 'simple-header-footer') . '</a>';
    $links[] = $settings_link;
    return $links;
}
