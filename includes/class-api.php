<?php

/**
 * WP OAuth Server - Remote Logout Extension API Class
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WO_Remote_Logout_API
{
    /**
     * API instance
     *
     * @var WO_Remote_Logout_API
     */
    private static $instance = null;

    /**
     * Constructor
     */
    private function __construct()
    {
        add_action('rest_api_init', array($this, 'register_endpoints'));
    }

    /**
     * Get API instance
     *
     * @return WO_Remote_Logout_API
     */
    public static function get_instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register REST API endpoints
     *
     * @return void
     */
    public function register_endpoints()
    {
        // Register the logout endpoint
        register_rest_route('wp-oauth/v1', '/user-logout', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_remote_logout'),
            'permission_callback' => array($this, 'verify_remote_logout_permission'),
        ));

        // Register the debug endpoint
        register_rest_route('wp-oauth/v1/debug', '/user-logout', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_debug_logout'),
            'permission_callback' => array($this, 'verify_debug_permission'),
        ));
    }

    /**
     * Verify debug endpoint permission
     *
     * @param WP_REST_Request $request Request object
     * @return bool|WP_Error
     */
    public function verify_debug_permission($request)
    {
        // Check for admin capability
        if (current_user_can('manage_options')) {
            return true;
        }

        // If we're here, the current_user_can check failed
        // Add debugging for REST API cookie auth issues
        $user = wp_get_current_user();

        wo_remote_logout()->logger->log_event(
            'auth_failed',
            'Debug endpoint access denied',
            array(
                'roles' => $user->roles ?? array(),
                'is_logged_in' => is_user_logged_in(),
                'user_id' => $user->ID ?? 0
            ),
            'warning'
        );

        return false;
    }

    /**
     * Verify the request using WP OAuth Server's built-in token validation
     *
     * @param WP_REST_Request $request Request object
     * @return bool|WP_Error
     */
    public function verify_remote_logout_permission($request)
    {
        // Get the bearer token from the request
        $token = $this->get_authentication_token($_SERVER);
        if (!$token) {
            wo_remote_logout()->logger->log_event(
                'auth_failed',
                'No authorization header provided',
                array('headers' => json_encode($_SERVER)),
                'warning'
            );
            return false;
        }

        // Use WP OAuth Server's verification method
        $server = $this->get_oauth_server();
        if (!$server) {
            wo_remote_logout()->logger->log_event(
                'auth_failed',
                'WP OAuth Server not available',
                array(),
                'error'
            );
            return false;
        }

        // Create request and response objects for the verification
        $wp_oauth_server_path = dirname(dirname(dirname(__FILE__))) . '/wp-oauth-server';
        require_once($wp_oauth_server_path . '/library/WPOAuth2/Autoloader.php');
        \WPOAuth2\Autoloader::register();

        $request_obj = new \WPOAuth2\Request($_GET, $_POST, array(), $_COOKIE, $_FILES, $_SERVER);
        $response_obj = new \WPOAuth2\Response();

        // Use the ResourceController to verify the token
        $valid = false;

        try {
            if (method_exists($server, 'verifyResourceRequest')) {
                $valid = $server->verifyResourceRequest($request_obj, $response_obj);
            } else {
                // Fallback - directly check the token in the database
                global $wpdb;
                $table_name = $wpdb->prefix . 'oauth_access_tokens';
                $token_data = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM $table_name WHERE access_token = %s AND expires > %d",
                    $token,
                    time()
                ));

                if ($token_data) {
                    $valid = true;
                }
            }
        } catch (Exception $e) {
            wo_remote_logout()->logger->log_event(
                'auth_failed',
                'Exception during token validation: ' . $e->getMessage(),
                array(),
                'error'
            );
            return false;
        }

        if (!$valid) {
            wo_remote_logout()->logger->log_event(
                'auth_failed',
                'Invalid token provided',
                array('error' => $response_obj->getParameter('error_description')),
                'warning'
            );
        }

        return $valid;
    }

    /**
     * Handle the remote logout request
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function handle_remote_logout($request)
    {
        // Get the WP user ID from the request
        $wp_user_id = $request->get_param('wp_user_id');

        if (!$wp_user_id) {
            wo_remote_logout()->logger->log_event(
                'logout_failed',
                'Missing WordPress user ID parameter',
                array('params' => json_encode($request->get_params())),
                'warning'
            );
            return new WP_Error(
                'missing_param',
                'WordPress user ID is required',
                array('status' => 400)
            );
        }

        // Convert to integer for security
        $wp_user_id = intval($wp_user_id);

        // Verify user exists
        $user = get_user_by('ID', $wp_user_id);
        if (!$user) {
            wo_remote_logout()->logger->log_event(
                'logout_failed',
                "User ID $wp_user_id not found",
                array(),
                'warning'
            );
            return new WP_Error(
                'invalid_user',
                'User not found',
                array('status' => 404)
            );
        }

        // Get the token information to log which client is performing the logout
        $token_info = $this->get_token_info_from_request();

        // Ensure we have a client ID - either from token or request
        $client_id = $token_info['client_id'] ?? null;
        $client_id_from_request = $request->get_param('client_id');

        // If client ID is missing or unknown in token, use the one from request if available
        if ((empty($client_id) || $client_id === 'unknown') && !empty($client_id_from_request)) {
            $client_id = $client_id_from_request;
        }

        // Log out the user by destroying all their sessions
        $sessions = WP_Session_Tokens::get_instance($wp_user_id);
        $session_count = count($sessions->get_all());
        $sessions->destroy_all();

        // Also revoke all OAuth tokens for this user
        $tokens = new WO_Remote_Logout_Tokens();
        $revoked_tokens = $tokens->revoke_user_tokens($wp_user_id);

        // Prepare log data
        $log_data = array(
            'client_id' => $client_id,
            'user_id' => $wp_user_id,
            'user_email' => $user->user_email,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            'sessions_terminated' => $session_count,
            'tokens_revoked' => $revoked_tokens
        );

        // Add to plugin activity log
        wo_remote_logout()->logger->log_event(
            'logout_success',
            "User {$user->user_login} logged out successfully",
            $log_data
        );

        // Trigger action for other plugins
        do_action('wo_user_logout', $wp_user_id, $token_info);

        return rest_ensure_response(array(
            'success' => true,
            'message' => 'User logged out successfully',
            'details' => array(
                'sessions_terminated' => $session_count,
                'tokens_revoked' => $revoked_tokens,
                'user_id' => $wp_user_id,
                'client_id' => $client_id ?? 'unknown'
            )
        ));
    }

    /**
     * Handle debug logout request
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function handle_debug_logout($request)
    {
        // Get the WP user ID from the request
        $wp_user_id = $request->get_param('wp_user_id');

        if (!$wp_user_id) {
            return new WP_Error(
                'missing_param',
                'WordPress user ID is required',
                array('status' => 400)
            );
        }

        // Convert to integer for security
        $wp_user_id = intval($wp_user_id);

        // Verify user exists
        $user = get_user_by('ID', $wp_user_id);
        if (!$user) {
            return new WP_Error(
                'invalid_user',
                'User not found',
                array('status' => 404)
            );
        }

        // Get client ID if provided in request
        $client_id = $request->get_param('client_id');
        if (empty($client_id)) {
            // Default client for debug endpoint
            $client_id = 'debug_api';
        }

        // Log out the user by destroying all their sessions
        $sessions = WP_Session_Tokens::get_instance($wp_user_id);
        $session_count = count($sessions->get_all());
        $sessions->destroy_all();

        // Revoke OAuth tokens
        $tokens = new WO_Remote_Logout_Tokens();
        $tokens_revoked = $tokens->revoke_user_tokens($wp_user_id);

        // Log the debug action
        wo_remote_logout()->logger->log_event(
            'debug_logout',
            "Debug: Admin logged out user {$user->user_login}",
            array(
                'client_id' => $client_id,
                'user_id' => $wp_user_id,
                'sessions_terminated' => $session_count,
                'tokens_revoked' => $tokens_revoked
            )
        );

        return rest_ensure_response(array(
            'success' => true,
            'message' => 'User logged out successfully via debug endpoint',
            'details' => array(
                'sessions_terminated' => $session_count,
                'tokens_revoked' => $tokens_revoked,
                'user_id' => $wp_user_id,
                'username' => $user->user_login,
                'client_id' => $client_id
            )
        ));
    }

    /**
     * Get token and client info from the current request
     *
     * @return array
     */
    private function get_token_info_from_request()
    {
        // Use the WP OAuth Server function to get the token
        $token = $this->get_authentication_token($_SERVER);

        if (!$token) {
            return array('client_id' => 'unknown');
        }

        // Get token info from WP OAuth Server's database
        global $wpdb;
        $table_name = $wpdb->prefix . 'oauth_access_tokens';

        // First check if the table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            return array('client_id' => 'unknown');
        }

        $token_info = $wpdb->get_row($wpdb->prepare(
            "SELECT client_id, user_id FROM $table_name WHERE access_token = %s",
            $token
        ), ARRAY_A);

        if (!$token_info) {
            return array('client_id' => 'unknown');
        }

        return $token_info ?: array('client_id' => 'unknown');
    }

    /**
     * Get the authentication token from server headers
     *
     * @param array $server Server variables
     * @return string|null
     */
    private function get_authentication_token($server)
    {
        // Use the main plugin's function
        if (function_exists('wp_oauth_get_authentication_token')) {
            // Debug info - this comment indicates we're using the main plugin's function
            return wp_oauth_get_authentication_token($server);
        }

        // Fallback implementation in case the main function is not available
        // Check for the authorization header
        $auth_header = isset($server['HTTP_AUTHORIZATION']) ? $server['HTTP_AUTHORIZATION'] : false;

        // Apache may prefix with REDIRECT_
        if (!$auth_header && isset($server['REDIRECT_HTTP_AUTHORIZATION'])) {
            $auth_header = $server['REDIRECT_HTTP_AUTHORIZATION'];
        }

        if (!$auth_header) {
            return null;
        }

        // Check if the header is a bearer token
        if (preg_match('/Bearer\s(\S+)/i', $auth_header, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Get OAuth server instance
     *
     * @return WO_Server|null
     */
    private function get_oauth_server()
    {
        if (!class_exists('WO_Server')) {
            return null;
        }
        return new WO_Server();
    }
}
