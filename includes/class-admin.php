<?php

/**
 * WP OAuth Server - Remote Logout Extension Admin Class
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WO_Remote_Logout_Admin
{
    /**
     * Admin instance
     *
     * @var WO_Remote_Logout_Admin
     */
    private static $instance = null;

    /**
     * Constructor
     */
    private function __construct()
    {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_wo_clear_logs', array($this, 'ajax_clear_logs'));
        add_action('wp_ajax_wo_repair_table', array($this, 'ajax_repair_table'));
        add_action('wp_ajax_wo_save_settings', array($this, 'ajax_save_settings'));
        add_action('wp_ajax_wo_direct_logout', array($this, 'ajax_direct_logout'));
    }

    /**
     * Get admin instance
     *
     * @return WO_Remote_Logout_Admin
     */
    public static function get_instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Enqueue admin scripts and styles
     *
     * @param string $hook_suffix Admin page hook suffix
     * @return void
     */
    public function enqueue_scripts($hook_suffix)
    {
        // Only load on plugin pages
        if (strpos($hook_suffix, 'wo-remote-logout') === false) {
            return;
        }

        // Enqueue CSS
        wp_enqueue_style(
            'wo-remote-logout-admin',
            WO_REMOTE_LOGOUT_URL . 'assets/css/admin.css',
            array(),
            WO_REMOTE_LOGOUT_VERSION
        );

        // Enqueue JS
        wp_enqueue_script(
            'wo-remote-logout-admin',
            WO_REMOTE_LOGOUT_URL . 'assets/js/admin.js',
            array('jquery'),
            WO_REMOTE_LOGOUT_VERSION,
            true
        );

        // Enqueue tabs fix script - no jQuery dependency
        wp_enqueue_script(
            'wo-remote-logout-tabs-fix',
            WO_REMOTE_LOGOUT_URL . 'assets/js/tabs-fix.js',
            array(),
            WO_REMOTE_LOGOUT_VERSION,
            false // Load in head for immediate execution
        );

        // Add localized script data
        wp_localize_script('wo-remote-logout-admin', 'woRemoteLogout', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wo-remote-logout-admin'),
            'messages' => array(
                'clearLogs' => __('Are you sure you want to clear all logs? This action cannot be undone.', 'wp-oauth-remote-logout'),
                'repairTable' => __('Are you sure you want to repair the logs table?', 'wp-oauth-remote-logout'),
                'success' => __('Success', 'wp-oauth-remote-logout'),
                'error' => __('Error', 'wp-oauth-remote-logout')
            )
        ));
    }

    /**
     * AJAX: Clear logs
     *
     * @return void
     */
    public function ajax_clear_logs()
    {
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }

        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wo-remote-logout-admin')) {
            wp_send_json_error('Invalid nonce');
        }

        // Clear logs
        $result = wo_remote_logout()->logger->clear_logs();

        if ($result !== false) {
            wp_send_json_success('Logs cleared successfully');
        } else {
            wp_send_json_error('Failed to clear logs');
        }
    }

    /**
     * AJAX: Repair table
     *
     * @return void
     */
    public function ajax_repair_table()
    {
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }

        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wo-remote-logout-admin')) {
            wp_send_json_error('Invalid nonce');
        }

        // Repair table
        $result = wo_remote_logout()->logger->repair_logs_table();

        if ($result) {
            wp_send_json_success('Log table repaired successfully');
        } else {
            wp_send_json_error('Failed to repair log table');
        }
    }

    /**
     * AJAX: Save settings
     *
     * @return void
     */
    public function ajax_save_settings()
    {
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }

        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wo-remote-logout-admin')) {
            wp_send_json_error('Invalid nonce');
        }

        // Get settings
        $options = array(
            'logging_enabled' => isset($_POST['wo_logging_enabled']) ? 1 : 0,
            'max_log_entries' => isset($_POST['wo_max_log_entries']) ? intval($_POST['wo_max_log_entries']) : 1000,
            'log_retention_days' => isset($_POST['wo_log_retention_days']) ? intval($_POST['wo_log_retention_days']) : 30,
            'stats_period_days' => isset($_POST['wo_stats_period_days']) ? intval($_POST['wo_stats_period_days']) : 30,
            'notify_admin' => isset($_POST['wo_notify_admin']) ? 1 : 0,
            'notify_email' => isset($_POST['wo_notify_email']) ? sanitize_email($_POST['wo_notify_email']) : get_option('admin_email'),
            'debug_mode' => isset($_POST['wo_debug_mode']) ? 1 : 0,
            'auto_cleanup' => isset($_POST['wo_auto_cleanup']) ? 1 : 0
        );

        // Validate numeric fields
        $options['max_log_entries'] = max(100, min(10000, $options['max_log_entries']));
        $options['log_retention_days'] = max(1, min(365, $options['log_retention_days']));
        $options['stats_period_days'] = max(1, min(365, $options['stats_period_days']));

        // If email is invalid, use admin email
        if (!is_email($options['notify_email'])) {
            $options['notify_email'] = get_option('admin_email');
        }

        // Save settings
        update_option(WO_REMOTE_LOGOUT_OPTION_KEY, $options);

        wp_send_json_success('Settings saved successfully');
    }

    /**
     * AJAX: Direct logout
     *
     * @return void
     */
    public function ajax_direct_logout()
    {
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die('Permission denied');
        }

        // Verify nonce
        if (!isset($_POST['wo_direct_logout_nonce']) || !wp_verify_nonce($_POST['wo_direct_logout_nonce'], 'wo_direct_logout_nonce')) {
            wp_die('Security check failed');
        }

        // Get user ID
        $user_id = isset($_POST['wo_test_user']) ? intval($_POST['wo_test_user']) : 0;
        if (!$user_id) {
            wp_die('User ID is required');
        }

        // Get user info
        $user = get_user_by('ID', $user_id);
        if (!$user) {
            wp_die('Invalid user');
        }

        // Log out the user by destroying all their sessions
        $sessions = WP_Session_Tokens::get_instance($user_id);
        $session_count = count($sessions->get_all());
        $sessions->destroy_all();

        // Revoke OAuth tokens
        $tokens = new WO_Remote_Logout_Tokens();
        $tokens_revoked = $tokens->revoke_user_tokens($user_id);

        // Log the event
        wo_remote_logout()->logger->log_event(
            'debug_logout',
            sprintf('Admin manually logged out user %s (ID: %d)', $user->user_login, $user_id),
            array(
                'client_id' => 'admin_interface',
                'user_id' => $user_id,
                'sessions_terminated' => $session_count,
                'tokens_revoked' => $tokens_revoked
            )
        );

        // Redirect back to the test tab with success message
        wp_redirect(
            add_query_arg(
                array(
                    'page' => 'wo-remote-logout',
                    'tab' => 'test',
                    'logout_status' => 'success',
                    'user' => $user->user_login,
                    'count' => $session_count,
                    'tokens' => $tokens_revoked
                ),
                admin_url('admin.php')
            )
        );
        exit;
    }

    /**
     * Render admin page
     *
     * @return void
     */
    public static function render_admin_page()
    {
        // Check permissions
        if (!current_user_can('manage_options')) {
            return;
        }

        // Get options
        $options = wo_remote_logout()->get_option();

        // Get OAuth clients
        $clients = self::get_oauth_clients();

        // Get recent logs with pagination
        $page = isset($_GET['log_page']) ? max(1, intval($_GET['log_page'])) : 1;
        $per_page = 20;
        $offset = ($page - 1) * $per_page;
        $logs = wo_remote_logout()->logger->get_recent_logs($per_page, $offset);
        $total_logs = wo_remote_logout()->logger->get_total_logs_count();
        $total_pages = ceil($total_logs / $per_page);

        // Get statistical period from settings (default to 30 days)
        $stats_period = isset($options['stats_period_days']) ? intval($options['stats_period_days']) : 30;

        // Get logout statistics
        $stats = wo_remote_logout()->logger->get_logout_stats($stats_period);

        // Get client statistics for the stats tab
        $client_stats = wo_remote_logout()->logger->get_client_stats($stats_period);

        // Get current tab
        $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'overview';

        // Define tabs
        $tabs = apply_filters('wo_remote_logout_tabs', array(
            'overview' => 'Overview',
            'clients' => 'OAuth Clients',
            'logs' => 'Activity Logs',
            'stats' => 'Statistics',
            'settings' => 'Settings',
            'test' => 'Debug Tools'
        ));

        // Make admin object available for views
        $admin = self::get_instance();

        // Include admin view
        include_once WO_REMOTE_LOGOUT_PATH . 'views/admin-page.php';
    }

    /**
     * Get OAuth clients
     *
     * @return array
     */
    private static function get_oauth_clients()
    {
        global $wpdb;

        // Check if WP OAuth Server is installed
        if (!class_exists('WO_Server')) {
            return array();
        }

        // Get clients from WP OAuth Server using direct database queries
        $clients_query = "SELECT p.ID, p.post_title 
                         FROM {$wpdb->posts} p
                         WHERE p.post_type = 'wo_client' 
                         AND p.post_status != 'trash'
                         ORDER BY p.post_title ASC";

        $client_posts = $wpdb->get_results($clients_query);

        if (empty($client_posts)) {
            return array();
        }

        $clients = array();
        foreach ($client_posts as $client) {
            // Get client metadata using direct SQL
            $client_id_meta = $wpdb->get_var($wpdb->prepare(
                "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = 'client_id'",
                $client->ID
            ));

            $redirect_uri_meta = $wpdb->get_var($wpdb->prepare(
                "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = 'redirect_uri'",
                $client->ID
            ));

            // Get client secret for display
            $client_secret_meta = $wpdb->get_var($wpdb->prepare(
                "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = 'client_secret'",
                $client->ID
            ));

            $clients[] = array(
                'client_id' => $client_id_meta,
                'name' => $client->post_title,
                'redirect_uri' => $redirect_uri_meta,
                'client_secret' => $client_secret_meta,
                'status' => 'enabled', // Default to enabled
            );
        }

        if (empty($clients)) {
            return array();
        }

        // Get logout statistics for each client
        $logs_table = $wpdb->prefix . 'wo_remote_logout_logs';
        if ($wpdb->get_var("SHOW TABLES LIKE '$logs_table'") != $logs_table) {
            // Table doesn't exist yet, just return the clients without stats
            foreach ($clients as &$client) {
                $client['logout_count'] = 0;
                $client['last_used'] = null;
            }
            return $clients;
        }

        // Get stats for each client
        foreach ($clients as &$client) {
            if (empty($client['client_id'])) {
                $client['logout_count'] = 0;
                $client['last_used'] = null;
                continue;
            }

            // Get logout count - count both successful and failed attempts
            $logout_count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $logs_table 
                 WHERE client_id = %s 
                 AND event_type IN ('logout_success', 'logout_failed', 'debug_logout')",
                $client['client_id']
            ));

            // Get last used time from any event type
            $last_used = $wpdb->get_var($wpdb->prepare(
                "SELECT created_at FROM $logs_table 
                 WHERE client_id = %s 
                 ORDER BY created_at DESC LIMIT 1",
                $client['client_id']
            ));

            $client['logout_count'] = intval($logout_count);
            $client['last_used'] = $last_used;
        }

        return $clients;
    }
}
