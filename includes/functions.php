<?php

/**
 * WP OAuth Server - Remote Logout Extension Helper Functions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get the main plugin instance
 *
 * @return WO_Remote_Logout
 */
function wo_remote_logout()
{
    return WO_Remote_Logout::get_instance();
}

/**
 * Format event type for display
 *
 * @param string $event_type Event type
 * @return string
 */
function wo_format_event_type($event_type)
{
    $types = [
        'logout_success' => 'Successful Logout',
        'logout_failed' => 'Failed Logout',
        'auth_failed' => 'Authentication Failed',
        'debug_logout' => 'Debug Logout'
    ];
    return $types[$event_type] ?? $event_type;
}

/**
 * Get user display name
 *
 * @param int $user_id User ID
 * @return string
 */
function wo_get_user_display($user_id)
{
    if (!$user_id) return 'N/A';
    $user = get_userdata($user_id);
    if (!$user) return 'Unknown User';
    return sprintf('%s (%s)', $user->user_login, $user->user_email);
}

/**
 * Get client display name
 *
 * @param string $client_id Client ID
 * @return string
 */
function wo_get_client_display($client_id)
{
    if (!$client_id || $client_id === 'unknown') return 'Unknown';

    // Handle special client IDs
    if ($client_id === 'admin_interface') return 'Admin Interface';
    if ($client_id === 'test_interface') return 'Test Interface';
    if ($client_id === 'debug_api') return 'Debug API';

    global $wpdb;
    $client_name = $wpdb->get_var($wpdb->prepare(
        "SELECT post_title FROM {$wpdb->posts} p 
         JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
         WHERE pm.meta_key = 'client_id' 
         AND pm.meta_value = %s 
         AND p.post_type = 'wo_client' 
         AND p.post_status != 'trash'",
        $client_id
    ));

    return $client_name ?: $client_id;
}

/**
 * Format log details for display
 *
 * @param string $details JSON-encoded details string
 * @return string
 */
function wo_format_log_details($details)
{
    if (empty($details)) return 'No details available';

    $decoded = json_decode($details, true);
    if (!$decoded) return $details;

    // Format the details in a more user-friendly way
    $formatted = '<table class="details-table" style="width:100%; border-collapse:collapse;">';

    foreach ($decoded as $key => $value) {
        // Make the key more readable
        $readable_key = ucwords(str_replace('_', ' ', $key));

        // Format the value based on type
        if (is_array($value)) {
            $value = json_encode($value, JSON_PRETTY_PRINT);
            $formatted_value = '<pre>' . esc_html($value) . '</pre>';
        } elseif ($key === 'client_id') {
            $formatted_value = esc_html(wo_get_client_display($value));
        } elseif ($key === 'user_id' && !empty($value)) {
            $formatted_value = esc_html(wo_get_user_display($value));
        } else {
            $formatted_value = esc_html($value);
        }

        // Add row to table
        $formatted .= sprintf(
            '<tr><th style="text-align:left; padding:5px; border-bottom:1px solid #eee; width:30%%;">%s</th>' .
                '<td style="padding:5px; border-bottom:1px solid #eee;">%s</td></tr>',
            $readable_key,
            $formatted_value
        );
    }

    $formatted .= '</table>';
    return $formatted;
}

// Note: The wp_oauth_get_authentication_token function has been removed from this file
// to avoid conflicts with the same function from the main WP OAuth Server plugin.
// This extension now uses the function from the main plugin.
