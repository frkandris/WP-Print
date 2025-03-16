<?php
/**
 * Plugin Name: Print Button
 * Description: Adds a "Print" link to the end of each post, which opens the post in a printer-friendly format.
 * Version: 1.0.0
 * Author: Andras P. Toth
 * Author URI: https://profiles.wordpress.org/frkandris/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: print-button
 * Domain Path: /languages
 */

/*
Print Button is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Print Button is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Print Button. If not, see http://www.gnu.org/licenses/gpl-2.0.txt.
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
    load_plugin_textdomain('print-button', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'print_button_load_textdomain');

/**
 * Register settings, add options page, and set up settings sections and fields.
 */
function print_button_settings_init() {
    // Register setting with a simple string type
    register_setting(
        'print_button_options',
        'print_button_settings',
        'print_button_sanitize_settings'
    );

    // Add settings section
    add_settings_section(
        'print_button_section_post_types',
        __('Post Types', 'print-button'),
        'print_button_section_post_types_callback',
        'print_button_options'
    );

    // Add settings section for button style
    add_settings_section(
        'print_button_section_style',
        __('Button Style', 'print-button'),
        'print_button_section_style_callback',
        'print_button_options'
    );

    // Get available post types
    $post_types = get_post_types(array('public' => true), 'objects');
    
    // Add settings fields for each post type
    foreach ($post_types as $post_type) {
        add_settings_field(
            'print_button_field_' . $post_type->name,
            $post_type->label,
            'print_button_field_post_type_callback',
            'print_button_options',
            'print_button_section_post_types',
            array('post_type' => $post_type->name)
        );
    }

    // Add button style field
    add_settings_field(
        'print_button_field_style',
        __('Select Style', 'print-button'),
        'print_button_field_style_callback',
        'print_button_options',
        'print_button_section_style'
    );

    // Add custom CSS class field
    add_settings_field(
        'print_button_field_custom_class',
        __('Custom CSS Class', 'print-button'),
        'print_button_field_custom_class_callback',
        'print_button_options',
        'print_button_section_style',
        array('class' => 'print-button-custom-class-field')
    );
}
add_action('admin_init', 'print_button_settings_init');

/**
 * Add options page to the admin menu.
 */
function print_button_add_options_page() {
    add_options_page(
        __('Print Button Settings', 'print-button'),
        __('Print Button', 'print-button'),
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
    echo '<p>' . esc_html__('Choose which post types should display the print button.', 'print-button') . '</p>';
}

/**
 * Callback function for the post type field.
 *
 * @param array $args The field arguments.
 */
function print_button_field_post_type_callback($args) {
    // Get current settings
    $options = get_option('print_button_settings');
    $post_type = $args['post_type'];
    
    // Check if this post type is enabled
    $checked = '';
    if (isset($options['post_types'][$post_type]) && $options['post_types'][$post_type] == 1) {
        $checked = 'checked="checked"';
    }
    
    echo '<input type="checkbox" id="print_button_' . esc_attr($post_type) . '" name="print_button_settings[post_types][' . esc_attr($post_type) . ']" value="1" ' . esc_attr($checked) . ' />';
    echo '<label for="print_button_' . esc_attr($post_type) . '">' . esc_html__('Enable', 'print-button') . '</label>';
}

/**
 * Callback function for the button style settings section.
 */
function print_button_section_style_callback() {
    echo '<p>' . esc_html__('Choose a style for the print button or set your own custom CSS classes.', 'print-button') . '</p>';
}

/**
 * Callback function for the button style field.
 */
function print_button_field_style_callback() {
    // Get current settings
    $options = get_option('print_button_settings');
    $style = isset($options['button_style']) ? $options['button_style'] : 'default';
    
    // Style options
    $styles = array(
        'default' => __('Default', 'print-button'),
        'minimal' => __('Minimal', 'print-button'),
        'prominent' => __('Prominent', 'print-button'),
        'custom' => __('Custom CSS Class', 'print-button')
    );
    
    // Display style previews
    echo '<div class="print-button-style-options">';
    
    foreach ($styles as $key => $label) {
        $checked = ($style === $key) ? 'checked="checked"' : '';
        
        echo '<div class="print-button-style-option">';
        echo '<label>';
        echo '<input type="radio" name="print_button_settings[button_style]" value="' . esc_attr($key) . '" ' . esc_attr($checked) . '>';
        echo '<span>' . esc_html($label) . '</span>';
        
        // Show style preview
        if ($key !== 'custom') {
            echo '<div class="print-button-preview">';
            echo '<a href="#" class="print-button-link print-button-style-' . esc_attr($key) . '">' . esc_html__('Print', 'print-button') . '</a>';
            echo '</div>';
        }
        
        echo '</label>';
        echo '</div>';
    }
    
    echo '</div>';
    
    // Add inline styles for preview
    echo '<style>
        .print-button-style-options {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 15px;
        }
        .print-button-style-option {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            background: #f9f9f9;
            min-width: 150px;
        }
        .print-button-preview {
            margin-top: 10px;
            padding: 10px;
            background: #fff;
            border: 1px dashed #ccc;
        }
        .print-button-style-default {
            display: inline-block;
            padding: 8px 16px;
            background-color: #f7f7f7;
            color: #333;
            text-decoration: none;
            border-radius: 4px;
            border: 1px solid #ddd;
            font-size: 14px;
        }
        .print-button-style-minimal {
            display: inline-block;
            padding: 5px 10px;
            color: #0073aa;
            text-decoration: none;
            font-size: 14px;
            border: none;
            background: transparent;
        }
        .print-button-style-prominent {
            display: inline-block;
            padding: 10px 18px;
            background-color: #0073aa;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            font-size: 14px;
            font-weight: bold;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .print-button-custom-class-field {
            display: none;
        }
        input[value="custom"]:checked ~ .print-button-custom-class-field {
            display: block;
        }
    </style>';
    
    // Add script to toggle custom class field visibility
    echo '<script>
        jQuery(document).ready(function($) {
            var updateCustomFieldVisibility = function() {
                if ($("input[name=\'print_button_settings[button_style]\']:checked").val() === "custom") {
                    $(".print-button-custom-class-field").show();
                } else {
                    $(".print-button-custom-class-field").hide();
                }
            };
            
            // Initial state
            updateCustomFieldVisibility();
            
            // On change
            $("input[name=\'print_button_settings[button_style]\']").change(updateCustomFieldVisibility);
        });
    </script>';
}

/**
 * Callback function for the custom CSS class field.
 */
function print_button_field_custom_class_callback() {
    // Get current settings
    $options = get_option('print_button_settings');
    $custom_class = isset($options['custom_class']) ? $options['custom_class'] : '';
    
    echo '<input type="text" name="print_button_settings[custom_class]" value="' . esc_attr($custom_class) . '" class="regular-text">';
    echo '<p class="description">' . esc_html__('Enter custom CSS classes separated by spaces.', 'print-button') . '</p>';
}

/**
 * Callback function for the options page.
 */
function print_button_options_page_callback() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'print-button'));
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

        <script type="text/javascript">
            jQuery(document).ready(function($) {
                function toggleCustomClassField() {
                    var customClassField = $('#print_button_field_custom_class').parents('tr');
                    if ($('input[name="print_button_settings[button_style]"]:checked').val() === 'custom') {
                        customClassField.show();
                    } else {
                        customClassField.hide();
                    }
                }
                
                // Initial state
                toggleCustomClassField();
                
                // On change
                $('input[name="print_button_settings[button_style]"]').on('change', function() {
                    toggleCustomClassField();
                });
            });
        </script>
    </div>
    <?php
}

/**
 * Sanitize settings.
 *
 * @param mixed $input The input settings.
 * @return array The sanitized settings.
 */
function print_button_sanitize_settings($input) {
    $output = array();
    
    // Sanitize post types
    if (isset($input['post_types']) && is_array($input['post_types'])) {
        $output['post_types'] = array();
        $post_types = get_post_types(array('public' => true), 'names');
        
        foreach ($post_types as $post_type) {
            // Only include valid post types and ensure values are either 0 or 1
            if (isset($input['post_types'][$post_type])) {
                $output['post_types'][$post_type] = (int) !!$input['post_types'][$post_type];
            } else {
                $output['post_types'][$post_type] = 0;
            }
        }
    } else {
        // Default to post and page if no valid input
        $output['post_types'] = array(
            'post' => 1,
            'page' => 1
        );
    }
    
    // Sanitize button style
    if (isset($input['button_style']) && in_array($input['button_style'], array('default', 'minimal', 'prominent', 'custom'))) {
        $output['button_style'] = $input['button_style'];
    } else {
        $output['button_style'] = 'default';
    }
    
    // Sanitize custom CSS class
    if (isset($input['custom_class'])) {
        $output['custom_class'] = sanitize_text_field($input['custom_class']);
    } else {
        $output['custom_class'] = '';
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
    // Don't add print button if we're already on the print page
    if (isset($_GET['print'])) {
        $is_print_page = false;
        $print_value = sanitize_text_field(wp_unslash($_GET['print']));
        
        // Check if this is a print page
        if ($print_value === 'true') {
            // Verify nonce if we're on a print page
            if (!isset($_GET['print_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['print_nonce'])), 'print_' . get_the_ID())) {
                // If no valid nonce, we still don't want to show the print button on what appears to be a print page
                // But we're not going to load the print template either (that's handled in print_button_template_redirect)
            }
            $is_print_page = true;
        }
        
        if ($is_print_page) {
            return $content;
        }
    }
    
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
        
        // Get button style settings
        $options = get_option('print_button_settings', array());
        $button_style = isset($options['button_style']) ? $options['button_style'] : 'default';
        $custom_class = '';
        
        // Handle button styling based on selected option
        if ($button_style === 'custom' && isset($options['custom_class']) && !empty($options['custom_class'])) {
            // Use custom classes and don't apply predefined styles
            $button_class = 'print-button-link ' . esc_attr($options['custom_class']);
        } else {
            // Use predefined styles
            $button_class = 'print-button-link print-button-style-' . esc_attr($button_style);
        }
        
        // Add the button to the content
        $content .= '<div class="print-button-link-container">';
        $content .= '<a href="' . esc_url($print_url) . '" class="' . $button_class . '" target="_blank">' . esc_html__('Print', 'print-button') . '</a>';
        $content .= '</div>';
    }
    return $content;
}
add_filter('the_content', 'print_button_add_print_link');

/**
 * Load the print template if the print parameter is set to true.
 */
function print_button_template_redirect() {
    // Check if this is a print page
    if (isset($_GET['print']) && sanitize_text_field(wp_unslash($_GET['print'])) === 'true') {
        // Verify nonce for security
        if (!isset($_GET['print_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['print_nonce'])), 'print_' . get_the_ID())) {
            wp_die(esc_html__('Security check failed. Unable to print this page.', 'print-button'));
        }
        
        // Load the print template
        include(plugin_dir_path(__FILE__) . 'templates/print-template.php');
        exit();
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
        ),
        'button_style' => 'default',
        'custom_class' => ''
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

/**
 * Add settings link to the plugins page.
 *
 * @param array $links Plugin action links.
 * @return array Modified plugin action links.
 */
function print_button_add_settings_link($links) {
    $settings_link = '<a href="' . admin_url('options-general.php?page=print_button_options') . '">' . esc_html__('Settings', 'print-button') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'print_button_add_settings_link');
