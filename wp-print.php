<?php
/**
 * Plugin Name: WP Print
 * Plugin URI: https://example.com/wp-print
 * Description: Adds a "Print" link to the end of each post, which opens the post in a printer-friendly format.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: wp-print
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 */
define('WP_PRINT_VERSION', '1.0.0');

/**
 * Load plugin textdomain.
 */
function wp_print_load_textdomain() {
    load_plugin_textdomain('wp-print', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'wp_print_load_textdomain');

/**
 * Add the print link to the end of post content.
 *
 * @param string $content The post content.
 * @return string The modified post content with print link appended.
 */
function wp_print_add_print_link($content) {
    // Only add to single posts/pages
    if (is_singular() && in_the_loop() && is_main_query()) {
        $print_url = add_query_arg('print', 'true', get_permalink());
        $content .= '<div class="wp-print-link-container">';
        $content .= '<a href="' . esc_url($print_url) . '" class="wp-print-link" target="_blank">' . esc_html__('Print', 'wp-print') . '</a>';
        $content .= '</div>';
    }
    return $content;
}
add_filter('the_content', 'wp_print_add_print_link');

/**
 * Load the print template when the print parameter is present.
 */
function wp_print_template_redirect() {
    if (is_singular() && isset($_GET['print']) && $_GET['print'] === 'true') {
        include(plugin_dir_path(__FILE__) . 'templates/print-template.php');
        exit;
    }
}
add_action('template_redirect', 'wp_print_template_redirect');

/**
 * Enqueue styles for the print link and print template.
 */
function wp_print_enqueue_styles() {
    wp_enqueue_style(
        'wp-print-styles',
        plugin_dir_url(__FILE__) . 'assets/css/wp-print.css',
        array(),
        WP_PRINT_VERSION,
        'all'
    );
}
add_action('wp_enqueue_scripts', 'wp_print_enqueue_styles');

/**
 * Register activation hook.
 */
function wp_print_activate() {
    // Activation code if needed
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'wp_print_activate');

/**
 * Register deactivation hook.
 */
function wp_print_deactivate() {
    // Deactivation code if needed
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'wp_print_deactivate');
