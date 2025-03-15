<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Print
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Currently, this plugin doesn't store any data in the database,
// so no cleanup is necessary. If you add options or custom tables
// in the future, you should clean them up here.
