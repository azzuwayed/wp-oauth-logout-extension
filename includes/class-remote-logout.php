<?php

/**
 * WP OAuth Server - Remote Logout Extension Main Class
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WO_Remote_Logout
{
    /**
     * Plugin instance
     *
     * @var WO_Remote_Logout
     */
    private static $instance = null;

    /**
     * Options key
     *
     * @var string
     */
    private $option_key = 'wo_remote_logout_options';

    /**
     * Logger instance
     *
     * @var WO_Remote_Logout_Logger
     */
    public $logger;

    /**
     * Get plugin instance
     *
     * @return WO_Remote_Logout
     */
    public static function get_instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct()
    {
        $this->define_constants();
        $this->includes();
        $this->init_hooks();

        // Create logger instance
        $this->logger = new WO_Remote_Logout_Logger();
    }

    /**
     * Define plugin constants
     *
     * @return void
     */
    private function define_constants()
    {
        define('WO_REMOTE_LOGOUT_VERSION', '1.3.0');
        define('WO_REMOTE_LOGOUT_PATH', plugin_dir_path(dirname(__FILE__)));
        define('WO_REMOTE_LOGOUT_URL', plugin_dir_url(dirname(__FILE__)));
        define('WO_REMOTE_LOGOUT_BASENAME', plugin_basename(dirname(dirname(__FILE__))) . '/wp-oauth-logout-extension.php');
        define('WO_REMOTE_LOGOUT_LOG_ENABLED', true);
        define('WO_REMOTE_LOGOUT_OPTION_KEY', $this->option_key);
    }

    /**
     * Include required files
     *
     * @return void
     */
    private function includes()
    {
        // Make sure the main WP OAuth Server plugin functions are loaded
        if (!function_exists('wp_oauth_get_authentication_token') && class_exists('WO_Server')) {
            $main_plugin_functions = WP_PLUGIN_DIR . '/wp-oauth-server/includes/functions.php';
            if (file_exists($main_plugin_functions)) {
                require_once $main_plugin_functions;
            }
        }

        // Core classes
        require_once WO_REMOTE_LOGOUT_PATH . 'includes/class-logger.php';
        require_once WO_REMOTE_LOGOUT_PATH . 'includes/class-admin.php';
        require_once WO_REMOTE_LOGOUT_PATH . 'includes/class-api.php';
        require_once WO_REMOTE_LOGOUT_PATH . 'includes/class-tokens.php';

        // Helper functions
        require_once WO_REMOTE_LOGOUT_PATH . 'includes/functions.php';
    }

    /**
     * Initialize hooks
     *
     * @return void
     */
    private function init_hooks()
    {
        // Register activation/deactivation hooks
        register_activation_hook(WO_REMOTE_LOGOUT_BASENAME, array($this, 'activate'));
        register_deactivation_hook(WO_REMOTE_LOGOUT_BASENAME, array($this, 'deactivate'));

        // Register uninstall hook for redundancy (in addition to uninstall.php)
        register_uninstall_hook(WO_REMOTE_LOGOUT_BASENAME, array(__CLASS__, 'clean_up_plugin'));

        // Check if WP OAuth Server plugin is active
        add_action('admin_notices', array($this, 'check_dependencies'));

        // Add plugin action links
        add_filter('plugin_action_links_' . WO_REMOTE_LOGOUT_BASENAME, array($this, 'add_plugin_action_links'));

        // Initialize admin menu and pages
        add_action('admin_menu', array($this, 'register_admin_menu'), 25);

        // Initialize plugin components
        add_action('plugins_loaded', array($this, 'init_components'));

        // Schedule automatic cleanup
        add_action('wo_remote_logout_daily_cleanup', array($this, 'scheduled_cleanup'));

        // Initialize scheduled events
        $this->schedule_events();
    }

    /**
     * Set up scheduled events
     *
     * @return void
     */
    private function schedule_events()
    {
        // Only schedule if auto cleanup is enabled
        $auto_cleanup = $this->get_option('auto_cleanup', 1);

        if ($auto_cleanup) {
            // Schedule the daily cleanup if not already scheduled
            if (!wp_next_scheduled('wo_remote_logout_daily_cleanup')) {
                wp_schedule_event(time(), 'daily', 'wo_remote_logout_daily_cleanup');
            }
        } else {
            // Remove the scheduled event if auto cleanup is disabled
            $timestamp = wp_next_scheduled('wo_remote_logout_daily_cleanup');
            if ($timestamp) {
                wp_unschedule_event($timestamp, 'wo_remote_logout_daily_cleanup');
            }
        }
    }

    /**
     * Scheduled cleanup task
     * Removes old logs based on retention settings
     *
     * @return void
     */
    public function scheduled_cleanup()
    {
        if (!$this->logger) {
            $this->logger = new WO_Remote_Logout_Logger();
        }

        $options = $this->get_option();

        // Log the cleanup start if debug mode is enabled
        if (!empty($options['debug_mode'])) {
            error_log('[WP OAuth Remote Logout] Starting scheduled cleanup');
        }

        // Clean up logs based on retention settings
        $this->logger->cleanup_logs($options);

        // Log the cleanup completion if debug mode is enabled
        if (!empty($options['debug_mode'])) {
            error_log('[WP OAuth Remote Logout] Scheduled cleanup completed');
        }
    }

    /**
     * Initialize plugin components
     *
     * @return void
     */
    public function init_components()
    {
        // Only initialize if WP OAuth Server is active
        if (class_exists('WO_Server')) {
            // Initialize REST API endpoints
            WO_Remote_Logout_API::get_instance();

            // Initialize admin
            if (is_admin()) {
                WO_Remote_Logout_Admin::get_instance();
            }
        }
    }

    /**
     * Plugin activation
     *
     * @return void
     */
    public function activate()
    {
        // Check if WP OAuth Server plugin is activated
        if (!class_exists('WO_Server')) {
            deactivate_plugins(WO_REMOTE_LOGOUT_BASENAME);
            wp_die('This plugin requires the WP OAuth Server plugin to be installed and activated. <a href="https://wp-oauth.com">Get WP OAuth Server</a>');
        }

        // Create the logs table
        $this->logger->maybe_create_logs_table();

        // Set default options
        if (!get_option($this->option_key)) {
            update_option($this->option_key, [
                'logging_enabled' => 1,
                'max_log_entries' => 1000,
                'log_retention_days' => 30,
                'stats_period_days' => 30,
                'notify_admin' => 0,
                'notify_email' => get_option('admin_email'),
                'debug_mode' => 0,
                'auto_cleanup' => 1
            ]);
        }

        // Set up scheduled events
        $this->schedule_events();

        // Log to WordPress error log
        if (WO_REMOTE_LOGOUT_LOG_ENABLED) {
            error_log('[WP OAuth Remote Logout] Plugin activated, version ' . WO_REMOTE_LOGOUT_VERSION);
        }

        // Clear permalink cache
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation
     *
     * @return void
     */
    public function deactivate()
    {
        // Remove scheduled events
        $timestamp = wp_next_scheduled('wo_remote_logout_daily_cleanup');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'wo_remote_logout_daily_cleanup');
        }

        // Log to WordPress error log
        if (WO_REMOTE_LOGOUT_LOG_ENABLED) {
            error_log('[WP OAuth Remote Logout] Plugin deactivated');
        }

        // Clear permalink cache
        flush_rewrite_rules();
    }

    /**
     * Check plugin dependencies
     *
     * @return void
     */
    public function check_dependencies()
    {
        // Only show on relevant pages
        $screen = get_current_screen();
        if (!$screen || !in_array($screen->id, ['plugins', 'toplevel_page_wo_manage_clients', 'oauth-server_page_wo-remote-logout'])) {
            return;
        }

        if (!class_exists('WO_Server')) {
?>
            <div class="notice notice-error">
                <p><strong>WP OAuth Logout Extension</strong> requires the WP OAuth Server plugin to be installed and activated. <a href="https://wp-oauth.com" target="_blank">Get WP OAuth Server</a></p>
            </div>
<?php
        }
    }

    /**
     * Add plugin action links
     *
     * @param array $links Plugin action links
     * @return array
     */
    public function add_plugin_action_links($links)
    {
        // Only add link if WP OAuth Server is active
        if (class_exists('WO_Server')) {
            $settings_link = '<a href="' . admin_url('admin.php?page=wo-remote-logout') . '">Settings</a>';
            array_unshift($links, $settings_link);
        }
        return $links;
    }

    /**
     * Register admin menu
     *
     * @return void
     */
    public function register_admin_menu()
    {
        // Check if WP OAuth Server is active
        if (!class_exists('WO_Server')) {
            return;
        }

        // Add submenu page under WP OAuth Server menu
        add_submenu_page(
            'wo_manage_clients',       // Parent menu slug
            'Remote Logout',           // Page title
            'Remote Logout',           // Menu title
            'manage_options',          // Capability
            'wo-remote-logout',        // Menu slug
            array('WO_Remote_Logout_Admin', 'render_admin_page')  // Callback function
        );
    }

    /**
     * Get plugin options
     *
     * @param string $option Option name
     * @param mixed $default Default value
     * @return mixed
     */
    public function get_option($option = null, $default = null)
    {
        $options = get_option($this->option_key, [
            'logging_enabled' => 1,
            'max_log_entries' => 1000,
            'log_retention_days' => 30,
            'stats_period_days' => 30,
            'notify_admin' => 0,
            'notify_email' => get_option('admin_email'),
            'debug_mode' => 0,
            'auto_cleanup' => 1
        ]);

        if ($option === null) {
            return $options;
        }

        return isset($options[$option]) ? $options[$option] : $default;
    }

    /**
     * Clean up all plugin data - called during uninstall
     * This is a static method that can be called by the uninstall hook
     * even if the plugin instance isn't available
     *
     * @return void
     */
    public static function clean_up_plugin()
    {
        global $wpdb;

        // 1. Drop the logs table
        $logs_table = $wpdb->prefix . 'wo_remote_logout_logs';
        $wpdb->query("DROP TABLE IF EXISTS $logs_table");

        // 2. Delete all plugin options
        delete_option('wo_remote_logout_options');

        // 3. Clean up any transients
        delete_transient('wo_remote_logout_version_check');

        // 4. Remove any scheduled events
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
            error_log('WP OAuth Server - Remote Logout Extension: Plugin uninstalled and data cleaned up.');
        }
    }
}
