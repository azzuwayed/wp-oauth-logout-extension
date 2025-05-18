<?php

/**
 * WP OAuth Server - Remote Logout Extension Uninstaller
 *
 * This file runs when the plugin is deleted from the WordPress admin.
 */

// Exit if accessed directly
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Access the WordPress database
global $wpdb;

// 1. Drop the logs table
$logs_table = $wpdb->prefix . 'wo_remote_logout_logs';
$wpdb->query("DROP TABLE IF EXISTS $logs_table");

// 2. Delete all plugin options
delete_option('wo_remote_logout_options');

// 3. Clean up any transients
delete_transient('wo_remote_logout_version_check');

// 4. Remove any scheduled events
$timestamp = wp_next_scheduled('wo_remote_logout_daily_cleanup');
if ($timestamp) {
    wp_unschedule_event($timestamp, 'wo_remote_logout_daily_cleanup');
}

$timestamp = wp_next_scheduled('wo_remote_logout_cleanup');
if ($timestamp) {
    wp_unschedule_event($timestamp, 'wo_remote_logout_cleanup');
}

// 5. Remove any user meta related to this plugin
$wpdb->query("DELETE FROM $wpdb->usermeta WHERE meta_key LIKE 'wo_remote_logout_%'");

// 6. Check for any other tables with our prefix and remove them
$tables = $wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}wo_remote_logout_%'", ARRAY_A);
if (!empty($tables)) {
    foreach ($tables as $table) {
        $table_name = reset($table);
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
    }
}

// Log uninstall action if WP_DEBUG is enabled
if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('WP OAuth Server - Remote Logout Extension: Plugin uninstalled and data cleaned up through uninstall.php.');
}
