<?php

/**
 * WP OAuth Server - Remote Logout Extension Tokens Class
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WO_Remote_Logout_Tokens
{
    /**
     * Tokens instance
     *
     * @var WO_Remote_Logout_Tokens
     */
    private static $instance = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Nothing to do here
    }

    /**
     * Get tokens instance
     *
     * @return WO_Remote_Logout_Tokens
     */
    public static function get_instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Revoke all OAuth tokens for a specific user
     * 
     * @param int $user_id The WordPress user ID
     * @return int Number of tokens revoked
     */
    public function revoke_user_tokens($user_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'oauth_access_tokens';

        // First check if the table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            if (wo_remote_logout()->get_option('debug_mode')) {
                error_log('[WP OAuth Remote Logout] Table ' . $table_name . ' does not exist');
            }
            return 0;
        }

        // Get current tokens for this user
        $tokens = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE user_id = %s",
            $user_id
        ), ARRAY_A);

        $token_count = count($tokens);

        if ($token_count > 0) {
            // Delete all tokens for the user
            $result = $wpdb->delete(
                $table_name,
                ['user_id' => $user_id],
                ['%s']
            );

            if ($result === false) {
                if (wo_remote_logout()->get_option('debug_mode')) {
                    error_log('[WP OAuth Remote Logout] Error deleting OAuth tokens: ' . $wpdb->last_error);
                }
                return 0;
            }

            return $token_count;
        }

        return 0;
    }

    /**
     * Get OAuth tokens for a specific user
     * 
     * @param int $user_id The WordPress user ID
     * @return array Array of tokens
     */
    public function get_user_tokens($user_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'oauth_access_tokens';

        // First check if the table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            return [];
        }

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE user_id = %s",
            $user_id
        ), ARRAY_A);
    }

    /**
     * Check if a token is valid
     * 
     * @param string $token The token to check
     * @return bool True if valid, false otherwise
     */
    public function is_token_valid($token)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'oauth_access_tokens';

        // First check if the table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            return false;
        }

        $token_data = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE access_token = %s AND expires > %d",
            $token,
            time()
        ));

        return $token_data !== null;
    }

    /**
     * Get token info
     * 
     * @param string $token The token to get info for
     * @return array|null Token info or null if not found
     */
    public function get_token_info($token)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'oauth_access_tokens';

        // First check if the table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            return null;
        }

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE access_token = %s",
            $token
        ), ARRAY_A);
    }

    /**
     * Revoke a specific token
     * 
     * @param string $token The token to revoke
     * @return bool True if token was revoked, false otherwise
     */
    public function revoke_token($token)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'oauth_access_tokens';

        // First check if the table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            return false;
        }

        // Delete token
        $result = $wpdb->delete(
            $table_name,
            ['access_token' => $token],
            ['%s']
        );

        return $result !== false;
    }
}
