<?php
/**
 * Plugin Name: Print Button
 * Plugin URI: https://wordpress.org/plugins/print-button/
 * Description: Adds a "Print" link to the end of each post, which opens the post in a printer-friendly format.
 * Version: 1.0.0
 * Author: Andras P. Toth
 * Author URI: https://profiles.wordpress.org/frkandris/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: Print Button
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 */
define('PRINT_BUTTON_VERSION', '1.0.0');

/**
 * Load plugin textdomain.
 */
function print_button_load_textdomain() {
    load_plugin_textdomain('Print Button', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'print_button_load_textdomain');

/**
 * Register settings, add options page, and set up settings sections and fields.
 */
function print_button_settings_init() {
    // Register settings with explicit sanitization
    register_setting(
        'print_button_options',
        'print_button_settings',
        array(
            'type' => 'array',
            'description' => 'Print Button plugin settings',
            'sanitize_callback' => 'print_button_sanitize_settings',
            'default' => array(
                'post_types' => array(
                    'post' => 1,
                    'page' => 1
                )
            )
        )
    );

    // Add settings section
    add_settings_section(
        'print_button_section_post_types',
        __('Post Types Settings', 'Print Button'),
        'print_button_section_post_types_callback',
        'print_button_options'
    );

    // Add settings field
    add_settings_field(
        'print_button_field_post_types',
        __('Enable Print Button on:', 'Print Button'),
        'print_button_field_post_types_callback',
        'print_button_options',
        'print_button_section_post_types'
    );
}
add_action('admin_init', 'print_button_settings_init');

/**
 * Add options page to the admin menu.
 */
function print_button_add_options_page() {
    add_options_page(
        __('Print Button Settings', 'Print Button'),
        __('Print Button', 'Print Button'),
        'manage_options',
        'print_button_options',
        'print_button_options_page_callback'
    );
}
add_action('admin_menu', 'print_button_add_options_page');

/**
 * Callback function for the post types settings section.
 */
function print_button_section_post_types_callback() {
    echo '<p>' . esc_html__('Select which post types should display the print button.', 'Print Button') . '</p>';
}

/**
 * Callback function for the post types settings field.
 */
function print_button_field_post_types_callback() {
    $options = get_option('print_button_settings', array());
    $post_types = get_post_types(array('public' => true), 'objects');
    
    // Set default values for post and page if settings are empty
    if (empty($options['post_types'])) {
        $options['post_types'] = array('post' => 1, 'page' => 1);
    }
    
    foreach ($post_types as $post_type) {
        $checked = isset($options['post_types'][$post_type->name]) ? $options['post_types'][$post_type->name] : 0;
        echo '<label>';
        echo '<input type="checkbox" name="print_button_settings[post_types][' . esc_attr($post_type->name) . ']" value="1" ' . checked(1, $checked, false) . '>';
        echo ' ' . esc_html($post_type->label);
        echo '</label><br>';
    }
}

/**
 * Callback function for the options page.
 */
function print_button_options_page_callback() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('print_button_options');
            do_settings_sections('print_button_options');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

/**
 * Sanitize settings.
 *
 * @param array $input The input settings.
 * @return array The sanitized settings.
 */
function print_button_sanitize_settings($input) {
    $output = array();
    
    // Sanitize post types
    if (isset($input['post_types'])) {
        $post_types = get_post_types(array('public' => true), 'objects');
        foreach ($post_types as $post_type) {
            if (isset($input['post_types'][$post_type->name])) {
                $output['post_types'][$post_type->name] = (int) $input['post_types'][$post_type->name];
            }
        }
    }
    
    return $output;
}

/**
 * Get the enabled post types from settings.
 *
 * @return array Array of enabled post types.
 */
function print_button_get_enabled_post_types() {
    $options = get_option('print_button_settings', array());
    
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
function print_button_add_print_link($content) {
    // Get current post type
    $post_type = get_post_type();
    
    // Get enabled post types
    $enabled_post_types = print_button_get_enabled_post_types();
    
    // Only add to enabled post types and when viewing a single post/page
    if (is_singular() && in_array($post_type, $enabled_post_types) && in_the_loop() && is_main_query()) {
        $print_url = add_query_arg(
            array(
                'print' => 'true',
                'print_nonce' => wp_create_nonce('print_' . get_the_ID())
            ), 
            get_permalink()
        );
        $content .= '<div class="print-button-link-container">';
        $content .= '<a href="' . esc_url($print_url) . '" class="print-button-link" target="_blank">' . esc_html__('Print', 'Print Button') . '</a>';
        $content .= '</div>';
    }
    return $content;
}
add_filter('the_content', 'print_button_add_print_link');

/**
 * Load the print template when the print parameter is present.
 */
function print_button_template_redirect() {
    // Check if we're on a singular page and the print parameter is set
    if (is_singular() && isset($_GET['print']) && sanitize_text_field(wp_unslash($_GET['print'])) === 'true') {
        // Verify the nonce for security
        if (isset($_GET['print_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['print_nonce'])), 'print_' . get_the_ID())) {
            include(plugin_dir_path(__FILE__) . 'templates/print-template.php');
            exit;
        } else {
            // If nonce verification fails, redirect back to the post
            wp_safe_redirect(get_permalink());
            exit;
        }
    }
}
add_action('template_redirect', 'print_button_template_redirect');

/**
 * Enqueue styles for the print link and print template.
 */
function print_button_enqueue_styles() {
    wp_enqueue_style(
        'print-button-styles',
        plugin_dir_url(__FILE__) . 'assets/css/print-button.css',
        array(),
        PRINT_BUTTON_VERSION,
        'all'
    );
}
add_action('wp_enqueue_scripts', 'print_button_enqueue_styles');

/**
 * Register activation hook.
 */
function print_button_activate() {
    // Set default settings on activation
    $default_settings = array(
        'post_types' => array(
            'post' => 1,
            'page' => 1
        )
    );
    
    // Only add if the setting doesn't already exist
    if (!get_option('print_button_settings')) {
        add_option('print_button_settings', $default_settings);
    }
    
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'print_button_activate');

/**
 * Register deactivation hook.
 */
function print_button_deactivate() {
    // Deactivation code if needed
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'print_button_deactivate');
