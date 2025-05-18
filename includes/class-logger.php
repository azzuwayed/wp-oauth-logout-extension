<?php

/**
 * WP OAuth Server - Remote Logout Extension Logger Class
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WO_Remote_Logout_Logger
{
    /**
     * Logger instance
     *
     * @var WO_Remote_Logout_Logger
     */
    private static $instance = null;

    /**
     * Table name
     *
     * @var string
     */
    private $table_name;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'wo_remote_logout_logs';
    }

    /**
     * Get logger instance
     *
     * @return WO_Remote_Logout_Logger
     */
    public static function get_instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Create logs table if it doesn't exist
     *
     * @return void
     */
    public function maybe_create_logs_table()
    {
        global $wpdb;

        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '{$this->table_name}'") == $this->table_name) {
            return;
        }

        // Table doesn't exist, create it
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$this->table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            event_type varchar(50) NOT NULL,
            message text NOT NULL,
            user_id bigint(20) NOT NULL DEFAULT 0,
            client_id varchar(100) DEFAULT NULL,
            ip_address varchar(45) NOT NULL,
            level varchar(20) NOT NULL DEFAULT 'info',
            details longtext DEFAULT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY event_type (event_type),
            KEY user_id (user_id),
            KEY client_id (client_id),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /**
     * Log event
     *
     * @param string $event_type Event type
     * @param string $message Message
     * @param array $details Details
     * @param string $level Log level
     * @return int|false The number of rows inserted, or false on error
     */
    public function log_event($event_type, $message, $details = [], $level = 'info')
    {
        // Get options
        $options = wo_remote_logout()->get_option();

        // Check if logging is enabled
        if (empty($options['logging_enabled'])) {
            return false;
        }

        // Get user ID and client ID from details if present
        $user_id = !empty($details['user_id']) ? intval($details['user_id']) : 0;

        // Get client ID - prioritize client_id in details
        $client_id = null;
        if (!empty($details['client_id'])) {
            $client_id = $details['client_id'];
        } else {
            // Only try to get from token if not already in details
            $token_info = array('client_id' => 'unknown');

            // Check if the token info function exists
            if (function_exists('wo_get_token_info_from_request')) {
                $token_info = wo_get_token_info_from_request();
            } else {
                // Fallback: try to get token from authorization header
                $auth_header = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : '';
                if (!empty($auth_header) && preg_match('/Bearer\s+([a-zA-Z0-9_\-\.]+)/', $auth_header, $matches)) {
                    $token_info = array('client_id' => 'unknown', 'token' => $matches[1]);
                }
            }

            $client_id = $token_info['client_id'] ?? null;
        }

        // If client_id is empty, missing, or 'unknown', check details again
        if (empty($client_id) || $client_id === 'unknown') {
            // For test logout actions, use test_interface as client
            if ($event_type === 'debug_logout' || (isset($details['client_id']) && $details['client_id'] === 'test_interface')) {
                $client_id = 'test_interface';
            } else if (isset($details['client_id']) && !empty($details['client_id'])) {
                $client_id = $details['client_id'];
            }
        }

        // Get IP address
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        // Debug log client ID detection
        if (!empty($options['debug_mode'])) {
            error_log(sprintf(
                '[WP OAuth Remote Logout] Debug - Logging event: %s, Client_id source: %s, Final client_id: %s',
                $event_type,
                isset($details['client_id']) ? 'details' : (isset($token_info) ? 'token' : 'unknown'),
                $client_id ?? 'null'
            ));
        }

        // Ensure the table exists
        $this->maybe_create_logs_table();

        // Insert log entry
        global $wpdb;
        $result = $wpdb->insert(
            $this->table_name,
            [
                'event_type' => $event_type,
                'message' => $message,
                'user_id' => $user_id,
                'client_id' => $client_id,
                'ip_address' => $ip_address,
                'level' => $level,
                'details' => json_encode($details),
                'created_at' => current_time('mysql', true)
            ],
            [
                '%s',
                '%s',
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            ]
        );

        // Cleanup old logs if auto cleanup is enabled
        if (!empty($options['auto_cleanup'])) {
            $this->cleanup_logs($options);
        }

        // Send admin notification if enabled
        if (!empty($options['notify_admin']) && !empty($options['notify_email'])) {
            $this->send_notification($event_type, $message, $user_id, $client_id, $ip_address, $level, $details);
        }

        // Log to WordPress debug log if debug mode is enabled
        if (!empty($options['debug_mode'])) {
            error_log(sprintf(
                '[WP OAuth Remote Logout] Event: %s, Message: %s, User: %d, Client: %s, IP: %s, Level: %s',
                $event_type,
                $message,
                $user_id,
                $client_id ?? 'N/A',
                $ip_address,
                $level
            ));
        }

        return $result;
    }

    /**
     * Cleanup old logs
     *
     * @param array $options Plugin options
     * @return array Statistics about the cleanup
     */
    public function cleanup_logs($options)
    {
        global $wpdb;

        // Initialize stats
        $stats = array(
            'deleted_by_age' => 0,
            'deleted_by_count' => 0,
            'table_size_before' => 0,
            'table_size_after' => 0,
            'execution_time' => 0
        );

        // Start timer
        $start_time = microtime(true);

        // Get table size before cleanup
        $stats['table_size_before'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");

        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '{$this->table_name}'") != $this->table_name) {
            return $stats;
        }

        // Cleanup based on retention period
        if (!empty($options['log_retention_days'])) {
            $retention_date = date('Y-m-d H:i:s', strtotime("-{$options['log_retention_days']} days"));

            // For large tables, delete in smaller batches to prevent timeouts
            $batch_size = 1000;
            $deleted = 0;

            do {
                $result = $wpdb->query($wpdb->prepare(
                    "DELETE FROM {$this->table_name} WHERE created_at < %s LIMIT {$batch_size}",
                    $retention_date
                ));

                if ($result === false) {
                    // Error occurred
                    break;
                }

                $deleted += $result;

                // Add a small delay to prevent server overload
                if ($result > 0) {
                    usleep(10000); // 10ms delay
                }
            } while ($result > 0);

            $stats['deleted_by_age'] = $deleted;
        }

        // Cleanup based on max entries
        if (!empty($options['max_log_entries'])) {
            // Check how many entries we have
            $total_entries = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");

            if ($total_entries > $options['max_log_entries']) {
                // Calculate how many entries to delete
                $to_delete = $total_entries - $options['max_log_entries'];

                if ($to_delete > 0) {
                    // For large deletions, use batches
                    $batch_size = 1000;
                    $deleted = 0;

                    // First, find the cutoff ID
                    $cutoff_id = $wpdb->get_var($wpdb->prepare(
                        "SELECT id FROM {$this->table_name} ORDER BY created_at ASC LIMIT 1 OFFSET %d",
                        $to_delete
                    ));

                    if ($cutoff_id) {
                        // Delete everything below the cutoff ID
                        $result = $wpdb->query($wpdb->prepare(
                            "DELETE FROM {$this->table_name} WHERE id < %d",
                            $cutoff_id
                        ));

                        $stats['deleted_by_count'] = $result !== false ? $result : 0;
                    }
                }
            }
        }

        // Get table size after cleanup
        $stats['table_size_after'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");

        // Calculate execution time
        $stats['execution_time'] = round(microtime(true) - $start_time, 4);

        // Optimize table if we deleted a significant number of records
        if (($stats['deleted_by_age'] + $stats['deleted_by_count']) > 100) {
            $wpdb->query("OPTIMIZE TABLE {$this->table_name}");
        }

        return $stats;
    }

    /**
     * Send notification
     *
     * @param string $event_type Event type
     * @param string $message Message
     * @param int $user_id User ID
     * @param string $client_id Client ID
     * @param string $ip_address IP address
     * @param string $level Log level
     * @param array $details Details
     * @return bool Whether the email was sent successfully
     */
    private function send_notification($event_type, $message, $user_id, $client_id, $ip_address, $level, $details)
    {
        $options = wo_remote_logout()->get_option();
        $subject = sprintf('[%s] Remote Logout Event: %s', get_bloginfo('name'), $event_type);
        $body = sprintf(
            "Event: %s\nMessage: %s\nUser ID: %d\nClient ID: %s\nIP Address: %s\nLevel: %s\nDetails: %s",
            $event_type,
            $message,
            $user_id,
            $client_id ?? 'N/A',
            $ip_address,
            $level,
            json_encode($details, JSON_PRETTY_PRINT)
        );
        return wp_mail($options['notify_email'], $subject, $body);
    }

    /**
     * Get recent logs
     *
     * @param int $limit Limit
     * @param int $offset Offset
     * @return array
     */
    public function get_recent_logs($limit = 10, $offset = 0)
    {
        global $wpdb;

        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '{$this->table_name}'") != $this->table_name) {
            return [];
        }

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} ORDER BY created_at DESC LIMIT %d OFFSET %d",
                $limit,
                $offset
            ),
            ARRAY_A
        );
    }

    /**
     * Get total logs count
     *
     * @return int
     */
    public function get_total_logs_count()
    {
        global $wpdb;

        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '{$this->table_name}'") != $this->table_name) {
            return 0;
        }

        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
    }

    /**
     * Get logout statistics
     *
     * @param int $days Number of days
     * @return array
     */
    public function get_logout_stats($days = 7)
    {
        global $wpdb;

        // Default stats
        $stats = [
            'total_attempts' => 0,
            'successful' => 0,
            'failed' => 0,
            'unique_users' => 0,
            'unique_clients' => 0
        ];

        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '{$this->table_name}'") != $this->table_name) {
            return $stats;
        }

        // Calculate time X days ago
        $time_ago = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        // Get stats
        $stats['total_attempts'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE created_at >= %s",
            $time_ago
        )) ?: 0;

        $stats['successful'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE (event_type = 'logout_success' OR event_type = 'debug_logout') AND created_at >= %s",
            $time_ago
        )) ?: 0;

        $stats['failed'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE event_type = 'logout_failed' AND created_at >= %s",
            $time_ago
        )) ?: 0;

        $stats['unique_users'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT user_id) FROM {$this->table_name} WHERE user_id > 0 AND created_at >= %s",
            $time_ago
        )) ?: 0;

        $stats['unique_clients'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT client_id) FROM {$this->table_name} WHERE client_id IS NOT NULL AND client_id != '' AND created_at >= %s",
            $time_ago
        )) ?: 0;

        return $stats;
    }

    /**
     * Clear all logs
     *
     * @return int|false The number of rows deleted, or false on error
     */
    public function clear_logs()
    {
        global $wpdb;

        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '{$this->table_name}'") != $this->table_name) {
            return false;
        }

        return $wpdb->query("TRUNCATE TABLE {$this->table_name}");
    }

    /**
     * Repair logs table
     *
     * @return bool Whether the table was repaired successfully
     */
    public function repair_logs_table()
    {
        global $wpdb;

        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '{$this->table_name}'") == $this->table_name) {
            // Repair table
            $wpdb->query("REPAIR TABLE {$this->table_name}");
            return true;
        } else {
            // Create table if it doesn't exist
            $this->maybe_create_logs_table();
            return true;
        }
    }

    /**
     * Get client logout statistics
     *
     * @param int $days Number of days
     * @return array
     */
    public function get_client_stats($days = 30)
    {
        global $wpdb;

        // Default stats
        $client_stats = [];

        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '{$this->table_name}'") != $this->table_name) {
            return $client_stats;
        }

        // Calculate time X days ago
        $time_ago = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        // Get client IDs with stats
        $stats = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                client_id,
                COUNT(*) as total_requests,
                SUM(CASE WHEN event_type = 'logout_success' OR event_type = 'debug_logout' THEN 1 ELSE 0 END) as successful,
                SUM(CASE WHEN event_type = 'logout_failed' THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN event_type = 'auth_failed' THEN 1 ELSE 0 END) as auth_failed,
                COUNT(DISTINCT user_id) as unique_users,
                MAX(created_at) as last_activity
            FROM {$this->table_name}
            WHERE created_at >= %s
            GROUP BY client_id
            ORDER BY total_requests DESC",
            $time_ago
        ));

        if (!$stats) {
            return $client_stats;
        }

        // Format the results
        foreach ($stats as $row) {
            if (empty($row->client_id)) {
                continue; // Skip entries with no client ID
            }

            $client_stats[] = [
                'client_id' => $row->client_id,
                'client_name' => wo_get_client_display($row->client_id),
                'total_requests' => (int)$row->total_requests,
                'successful' => (int)$row->successful,
                'failed' => (int)$row->failed,
                'auth_failed' => (int)$row->auth_failed,
                'unique_users' => (int)$row->unique_users,
                'last_activity' => $row->last_activity,
                'success_rate' => $row->total_requests > 0
                    ? round(($row->successful / $row->total_requests) * 100, 1) : 0
            ];
        }

        return $client_stats;
    }
}
