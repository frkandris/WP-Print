=== Print Button ===
Contributors: frkandris
Tags: print, printer-friendly, posts, pages
Requires at least: 5.0
Tested up to: 6.7
Stable tag: 1.0.0
Requires PHP: 7.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add a printer-friendly version link to your WordPress posts and pages.

== Description ==

Print Button is a simple, lightweight plugin that adds a "Print" link at the end of each post and page. When clicked, it opens a clean, printer-friendly version of the content in a new tab, free from headers, sidebars, widgets, and footers.

= Features =

* Adds a "Print" link at the end of each post and page
* Opens a clean, printer-friendly version of the content in a new tab
* Removes all unnecessary elements (headers, sidebars, footers, widgets) for clean printing
* Configurable settings to choose which post types display the print button
* Multiple button style options (default, minimal, prominent, or custom CSS classes)

= Use Cases =

* Blogs with long-form content that readers might want to print
* Recipe sites where users want to print instructions
* Educational sites with tutorials or guides
* News sites with articles readers might want to save in physical form
* Documentation websites where printed reference materials may be useful

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/print-button` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. (Optional) Go to Settings > Print Button to configure which post types should display the print button and choose a button style.

== Frequently Asked Questions ==

= Does this plugin require configuration? =

The plugin works out of the box with default settings, but you can configure which post types should display the print button and choose a button style by going to Settings > Print Button in your WordPress admin dashboard.

= Can I customize the appearance of the print button? =

Yes, there are three ways to customize the button:
1. Choose from predefined styles (Default, Minimal, Prominent) in the plugin settings
2. Select "Custom CSS Class" in the settings and provide your own classes
3. Modify the CSS directly in the `assets/css/print-button.css` file

= Will this work with custom post types? =

Yes, the plugin can add the print link to any post type. You can select which post types should display the print button in the plugin settings.

= Is this plugin compatible with page builders? =

Yes, Print Button should work with most page builders as it hooks into WordPress's content filter.

= Can I change the text of the print link? =

Currently, the link text is set to "Print". You can modify this by editing the plugin file or using WordPress's translation capabilities.

== Screenshots ==

1. The print link displayed at the end of a post
2. The printer-friendly version of a post
3. Settings page with post type and button style options

== Changelog ==

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.0 =
Initial release of Print Button plugin with configurable post type settings.

== Privacy Policy ==

Print Button does not collect, store, or share any personal data.
