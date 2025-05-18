<?php

/**
 * Plugin Name: WP OAuth Server - Remote Logout Extension
 * Plugin URI: https://wp-oauth.com
 * Description: Adds a remote logout endpoint to WP OAuth Server with zero configuration
 * Version: 1.3.0
 * Author: Abdullah Alzuwayed
 * Author URI: https://wp-oauth.com
 * Text Domain: wp-oauth-remote-logout
 * Requires OAuth Server: 3.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin version constant
if (!defined('WO_EXTENSION_VERSION')) {
    define('WO_EXTENSION_VERSION', '1.3.0');
}

// Load main plugin class
require_once dirname(__FILE__) . '/includes/class-remote-logout.php';

// Initialize the plugin
function wo_remote_logout_init()
{
    // Get the main plugin instance
    return WO_Remote_Logout::get_instance();
}

// Start the plugin
wo_remote_logout_init();

/**
 * Uninstall process:
 * 
 * When the plugin is deleted through the WordPress admin, the uninstall.php file will:
 * - Drop the plugin's database tables (wo_remote_logout_logs)
 * - Delete all plugin options (wo_remote_logout_options)
 * - Remove any scheduled events 
 * - Clean up any user metadata
 */

/**
 * Register admin scripts and styles
 */
function wo_extension_admin_scripts($hook)
{
    // Only load on our plugin's pages
    if (strpos($hook, 'wp-oauth-remote-logout') === false) {
        return;
    }

    $plugin_url = plugin_dir_url(__FILE__);

    // Enqueue shared CSS
    wp_enqueue_style(
        'wo-admin-styles',
        $plugin_url . 'assets/css/combined.min.css',
        array(),
        WO_EXTENSION_VERSION
    );

    // Enqueue combined JavaScript
    wp_enqueue_script(
        'wo-admin-scripts',
        $plugin_url . 'assets/js/combined.js',
        array('jquery'),
        WO_EXTENSION_VERSION,
        true
    );

    // Localize script for translations and AJAX URLs
    wp_localize_script('wo-admin-scripts', 'woRemoteLogout', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wo_remote_logout_nonce'),
        'messages' => array(
            'clearLogs' => __('Are you sure you want to clear all logs? This cannot be undone.', 'wp-oauth-remote-logout'),
            'repairTable' => __('This will attempt to repair the logs database table. Continue?', 'wp-oauth-remote-logout'),
            'success' => __('Success', 'wp-oauth-remote-logout'),
            'error' => __('Error', 'wp-oauth-remote-logout'),
        )
    ));
}
add_action('admin_enqueue_scripts', 'wo_extension_admin_scripts');
