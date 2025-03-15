<?php
/**
 * Plugin Name: Print Button
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
 * Register settings, add options page, and set up settings sections and fields.
 */
function wp_print_settings_init() {
    // Register settings
    register_setting('wp_print_options', 'wp_print_settings');

    // Add settings section
    add_settings_section(
        'wp_print_section_post_types',
        __('Post Types Settings', 'wp-print'),
        'wp_print_section_post_types_callback',
        'wp_print_options'
    );

    // Add settings field
    add_settings_field(
        'wp_print_field_post_types',
        __('Enable Print Button on:', 'wp-print'),
        'wp_print_field_post_types_callback',
        'wp_print_options',
        'wp_print_section_post_types'
    );
}
add_action('admin_init', 'wp_print_settings_init');

/**
 * Add options page to the admin menu.
 */
function wp_print_add_options_page() {
    add_options_page(
        __('Print Button Settings', 'wp-print'),
        __('Print Button', 'wp-print'),
        'manage_options',
        'wp_print_options',
        'wp_print_options_page_callback'
    );
}
add_action('admin_menu', 'wp_print_add_options_page');

/**
 * Callback function for the post types settings section.
 */
function wp_print_section_post_types_callback() {
    echo '<p>' . __('Select which post types should display the print button.', 'wp-print') . '</p>';
}

/**
 * Callback function for the post types settings field.
 */
function wp_print_field_post_types_callback() {
    $options = get_option('wp_print_settings', array());
    $post_types = get_post_types(array('public' => true), 'objects');
    
    // Set default values for post and page if settings are empty
    if (empty($options['post_types'])) {
        $options['post_types'] = array('post' => 1, 'page' => 1);
    }
    
    foreach ($post_types as $post_type) {
        $checked = isset($options['post_types'][$post_type->name]) ? $options['post_types'][$post_type->name] : 0;
        echo '<label>';
        echo '<input type="checkbox" name="wp_print_settings[post_types][' . esc_attr($post_type->name) . ']" value="1" ' . checked(1, $checked, false) . '>';
        echo ' ' . esc_html($post_type->label);
        echo '</label><br>';
    }
}

/**
 * Callback function for the options page.
 */
function wp_print_options_page_callback() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('wp_print_options');
            do_settings_sections('wp_print_options');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

/**
 * Get the enabled post types from settings.
 *
 * @return array Array of enabled post types.
 */
function wp_print_get_enabled_post_types() {
    $options = get_option('wp_print_settings', array());
    
    // Default to post and page if no settings are saved
    if (empty($options['post_types'])) {
        return array('post', 'page');
    }
    
    // Filter to only include enabled post types
    return array_keys(array_filter($options['post_types']));
}

/**
 * Add the print link to the end of post content.
 *
 * @param string $content The post content.
 * @return string The modified post content with print link appended.
 */
function wp_print_add_print_link($content) {
    // Get current post type
    $post_type = get_post_type();
    
    // Get enabled post types
    $enabled_post_types = wp_print_get_enabled_post_types();
    
    // Only add to enabled post types and when viewing a single post/page
    if (is_singular() && in_array($post_type, $enabled_post_types) && in_the_loop() && is_main_query()) {
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
    // Set default settings on activation
    $default_settings = array(
        'post_types' => array(
            'post' => 1,
            'page' => 1
        )
    );
    
    // Only add if the setting doesn't already exist
    if (!get_option('wp_print_settings')) {
        add_option('wp_print_settings', $default_settings);
    }
    
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
