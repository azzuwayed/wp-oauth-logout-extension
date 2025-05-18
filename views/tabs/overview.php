<?php

/**
 * Overview Tab
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wo-overview-header">
    <div class="wo-overview-intro">
        <h2 class="wo-section-title">
            <span class="dashicons dashicons-dashboard"></span>
            <?php _e('Remote Logout Dashboard', 'wp-oauth-remote-logout'); ?>
        </h2>
        <p class="wo-section-description">
            <?php _e('Overview of your WordPress remote logout functionality and recent activity.', 'wp-oauth-remote-logout'); ?>
        </p>
    </div>
</div>

<div class="wo-cards-container">
    <div class="wo-card">
        <div class="wo-card-header">
            <h3>
                <span class="dashicons dashicons-admin-site-alt3"></span>
                <?php _e('Endpoint Status', 'wp-oauth-remote-logout'); ?>
            </h3>
        </div>
        <div class="wo-card-content">
            <?php if (class_exists('WO_Server')): ?>
                <div class="wo-status-indicator active">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <div class="wo-status-text">
                        <strong><?php _e('Remote logout endpoint is active', 'wp-oauth-remote-logout'); ?></strong>
                        <code class="wo-endpoint-url"><?php echo esc_html(rest_url('wp-oauth/v1/user-logout')); ?></code>
                    </div>
                </div>
            <?php else: ?>
                <div class="wo-status-indicator inactive">
                    <span class="dashicons dashicons-warning"></span>
                    <div class="wo-status-text">
                        <strong><?php _e('Remote logout endpoint is inactive', 'wp-oauth-remote-logout'); ?></strong>
                        <span class="wo-status-description"><?php _e('WP OAuth Server plugin must be activated.', 'wp-oauth-remote-logout'); ?></span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="wo-card">
        <div class="wo-card-header">
            <h3>
                <span class="dashicons dashicons-chart-area"></span>
                <?php _e('Activity Statistics', 'wp-oauth-remote-logout'); ?> (<?php _e('Last 7 Days', 'wp-oauth-remote-logout'); ?>)
            </h3>
        </div>
        <div class="wo-card-content">
            <div class="wo-stats-grid">
                <div class="wo-stat-item">
                    <div class="wo-stat-label"><?php _e('Total Attempts', 'wp-oauth-remote-logout'); ?></div>
                    <div class="wo-stat-value"><?php echo esc_html($stats['total_attempts']); ?></div>
                </div>
                <div class="wo-stat-item">
                    <div class="wo-stat-label"><?php _e('Successful', 'wp-oauth-remote-logout'); ?></div>
                    <div class="wo-stat-value wo-status-success"><?php echo esc_html($stats['successful']); ?></div>
                </div>
                <div class="wo-stat-item">
                    <div class="wo-stat-label"><?php _e('Failed', 'wp-oauth-remote-logout'); ?></div>
                    <div class="wo-stat-value wo-status-error"><?php echo esc_html($stats['failed']); ?></div>
                </div>
                <div class="wo-stat-item">
                    <div class="wo-stat-label"><?php _e('Unique Users', 'wp-oauth-remote-logout'); ?></div>
                    <div class="wo-stat-value"><?php echo esc_html($stats['unique_users']); ?></div>
                </div>
                <div class="wo-stat-item">
                    <div class="wo-stat-label"><?php _e('Unique Clients', 'wp-oauth-remote-logout'); ?></div>
                    <div class="wo-stat-value"><?php echo esc_html($stats['unique_clients']); ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="wo-card wo-full-width">
    <div class="wo-card-header">
        <h3>
            <span class="dashicons dashicons-info-outline"></span>
            <?php _e('How It Works', 'wp-oauth-remote-logout'); ?>
        </h3>
    </div>
    <div class="wo-card-content">
        <p><?php _e('When a client application wants to log a user out of WordPress completely:', 'wp-oauth-remote-logout'); ?></p>

        <ol class="wo-steps">
            <li>
                <strong><?php _e('Request', 'wp-oauth-remote-logout'); ?>:</strong>
                <?php _e('The client makes a POST request to', 'wp-oauth-remote-logout'); ?>
                <code><?php echo esc_html(rest_url('wp-oauth/v1/user-logout')); ?></code>
            </li>
            <li>
                <strong><?php _e('Authentication', 'wp-oauth-remote-logout'); ?>:</strong>
                <?php _e('The request must include a valid OAuth access token in the Authorization header', 'wp-oauth-remote-logout'); ?>
            </li>
            <li>
                <strong><?php _e('User ID', 'wp-oauth-remote-logout'); ?>:</strong>
                <?php _e('The request must include the WordPress user ID in the request body', 'wp-oauth-remote-logout'); ?>
            </li>
            <li>
                <strong><?php _e('Processing', 'wp-oauth-remote-logout'); ?>:</strong>
                <?php _e('The plugin verifies the token and destroys all user sessions and tokens', 'wp-oauth-remote-logout'); ?>
            </li>
        </ol>

        <div class="wo-code-sample">
            <h4><?php _e('Example Request', 'wp-oauth-remote-logout'); ?></h4>
            <pre><code>POST <?php echo esc_html(rest_url('wp-oauth/v1/user-logout')); ?>
Authorization: Bearer ACCESS_TOKEN
Content-Type: application/json

{
  "wp_user_id": "123"
}</code></pre>
        </div>
    </div>
</div>

<style>
    /* Overview Tab Styles */
    .wo-overview-header {
        margin-bottom: 25px;
    }

    .wo-overview-intro {
        background: #fff;
        border-left: 4px solid #2271b1;
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
        padding: 20px;
        margin-bottom: 20px;
    }

    .wo-cards-container {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 20px;
    }

    .wo-card {
        background: #fff;
        border: 1px solid #e5e5e5;
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
        border-radius: 4px;
        flex: 1;
        min-width: 300px;
        overflow: hidden;
        transition: box-shadow 0.2s ease-in-out;
    }

    .wo-card:hover {
        box-shadow: 0 3px 6px rgba(0, 0, 0, 0.08);
    }

    .wo-full-width {
        flex-basis: 100%;
    }

    .wo-card-header {
        padding: 15px 20px;
        border-bottom: 1px solid #f0f0f0;
        background: #f9f9f9;
    }

    .wo-card-header h3 {
        margin: 0;
        font-size: 16px;
        display: flex;
        align-items: center;
    }

    .wo-card-header .dashicons {
        margin-right: 8px;
        color: #2271b1;
    }

    .wo-card-content {
        padding: 20px;
    }

    .wo-status-indicator {
        display: flex;
        align-items: center;
        padding: 15px;
        border-radius: 4px;
        background: #f9f9f9;
    }

    .wo-status-indicator.active {
        background: #f0f9e6;
        border-left: 4px solid #46b450;
    }

    .wo-status-indicator.inactive {
        background: #fef8ee;
        border-left: 4px solid #ffb900;
    }

    .wo-status-indicator .dashicons {
        font-size: 24px;
        width: 24px;
        height: 24px;
        margin-right: 15px;
    }

    .wo-status-indicator.active .dashicons {
        color: #46b450;
    }

    .wo-status-indicator.inactive .dashicons {
        color: #ffb900;
    }

    .wo-status-text {
        flex: 1;
    }

    .wo-status-text strong {
        display: block;
        margin-bottom: 5px;
    }

    .wo-endpoint-url {
        display: block;
        margin-top: 5px;
        padding: 10px;
        background: #f1f1f1;
        border-radius: 3px;
        font-family: monospace;
        word-break: break-all;
        position: relative;
    }

    .wo-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 15px;
    }

    .wo-stat-item {
        padding: 15px;
        background: #f9f9f9;
        border-radius: 4px;
        text-align: center;
        transition: transform 0.2s ease, background-color 0.2s ease;
        border: 1px solid #f0f0f0;
    }

    .wo-stat-item:hover {
        transform: translateY(-2px);
        background-color: #f7f7f7;
    }

    .wo-stat-label {
        font-size: 13px;
        color: #646970;
        margin-bottom: 8px;
    }

    .wo-stat-value {
        font-size: 26px;
        font-weight: 600;
    }

    .wo-stat-value.wo-status-success {
        color: #46b450;
    }

    .wo-stat-value.wo-status-error {
        color: #dc3232;
    }

    .wo-steps {
        margin: 20px 0;
        padding-left: 20px;
    }

    .wo-steps li {
        margin-bottom: 15px;
        padding-left: 10px;
        position: relative;
    }

    .wo-steps li::before {
        content: '';
        position: absolute;
        top: 8px;
        left: -15px;
        width: 8px;
        height: 8px;
        background-color: #2271b1;
        border-radius: 50%;
    }

    .wo-steps li strong {
        color: #2271b1;
    }

    .wo-code-sample {
        margin-top: 20px;
        background: #f9f9f9;
        border-radius: 4px;
        overflow: hidden;
        border: 1px solid #e5e5e5;
    }

    .wo-code-sample h4 {
        margin: 0;
        padding: 10px 15px;
        background: #f1f1f1;
        border-bottom: 1px solid #e5e5e5;
        font-size: 14px;
    }

    .wo-code-sample pre {
        margin: 0;
        padding: 15px;
        overflow-x: auto;
    }

    .wo-code-sample code {
        display: block;
        white-space: pre;
        font-family: monospace;
        font-size: 13px;
        line-height: 1.6;
    }

    @media screen and (max-width: 782px) {
        .wo-cards-container {
            flex-direction: column;
        }

        .wo-card {
            min-width: 100%;
        }

        .wo-stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media screen and (max-width: 480px) {
        .wo-stats-grid {
            grid-template-columns: 1fr;
        }

        .wo-status-indicator {
            flex-direction: column;
            text-align: center;
        }

        .wo-status-indicator .dashicons {
            margin-right: 0;
            margin-bottom: 10px;
        }

        .wo-steps li {
            padding-left: 0;
        }
    }
</style>