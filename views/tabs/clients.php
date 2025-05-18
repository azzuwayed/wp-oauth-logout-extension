<?php

/**
 * OAuth Clients tab view
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get OAuth clients
$clients = isset($_clients) ? $_clients : array();
?>

<div class="wo-clients-header">
    <div class="wo-clients-intro">
        <h2 class="wo-section-title">
            <span class="dashicons dashicons-groups"></span>
            <?php _e('OAuth Clients', 'wp-oauth-remote-logout'); ?>
        </h2>
        <p class="wo-section-description">
            <?php _e('Manage and monitor OAuth clients that can use the remote logout functionality.', 'wp-oauth-remote-logout'); ?>
        </p>
    </div>
</div>

<div class="wo-card wo-full-width">
    <div class="wo-card-header">
        <h3>
            <span class="dashicons dashicons-admin-plugins"></span>
            <?php _e('Registered OAuth Clients', 'wp-oauth-remote-logout'); ?>
        </h3>
    </div>
    <div class="wo-card-content">
        <?php if (empty($clients)) : ?>
            <div class="wo-empty-state">
                <span class="dashicons dashicons-businessman"></span>
                <p><?php _e('No OAuth clients found. Create clients in WP OAuth Server main settings.', 'wp-oauth-remote-logout'); ?></p>
                <a href="<?php echo esc_url(admin_url('admin.php?page=wo_manage_clients')); ?>" class="button button-primary">
                    <span class="dashicons dashicons-plus-alt"></span>
                    <?php _e('Add OAuth Client', 'wp-oauth-remote-logout'); ?>
                </a>
            </div>
        <?php else : ?>
            <p class="wo-table-description"><?php _e('The following OAuth clients can use the remote logout endpoint:', 'wp-oauth-remote-logout'); ?></p>

            <div class="wo-table-responsive">
                <table class="wo-clients-table widefat">
                    <thead>
                        <tr>
                            <th><?php _e('Client ID', 'wp-oauth-remote-logout'); ?></th>
                            <th><?php _e('Client Name', 'wp-oauth-remote-logout'); ?></th>
                            <th><?php _e('Client Secret', 'wp-oauth-remote-logout'); ?></th>
                            <th><?php _e('Redirect URI', 'wp-oauth-remote-logout'); ?></th>
                            <th><?php _e('Status', 'wp-oauth-remote-logout'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clients as $client) : ?>
                            <tr>
                                <td>
                                    <code class="client-id"><?php echo esc_html($client['client_id']); ?></code>
                                </td>
                                <td class="client-name"><?php echo esc_html($client['name']); ?></td>
                                <td>
                                    <div class="client-secret-container">
                                        <code class="client-secret"><?php echo isset($client['client_secret']) ? esc_html(substr($client['client_secret'], 0, 5) . '...') : 'N/A'; ?></code>
                                        <?php if (isset($client['client_secret'])) : ?>
                                            <button type="button" class="button-link toggle-secret"
                                                data-secret="<?php echo esc_attr($client['client_secret']); ?>"
                                                data-showing="false">
                                                <span class="show-text"><?php _e('Show', 'wp-oauth-remote-logout'); ?></span>
                                                <span class="hide-text" style="display:none"><?php _e('Hide', 'wp-oauth-remote-logout'); ?></span>
                                                <span class="dashicons dashicons-visibility"></span>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="redirect-uri">
                                    <?php if (!empty($client['redirect_uri'])) : ?>
                                        <code><?php echo esc_html($client['redirect_uri']); ?></code>
                                    <?php else : ?>
                                        <span class="na"><?php _e('N/A', 'wp-oauth-remote-logout'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (isset($client['status']) && $client['status'] == 'enabled') : ?>
                                        <span class="client-status active">
                                            <span class="dashicons dashicons-yes-alt"></span>
                                            <?php _e('Active', 'wp-oauth-remote-logout'); ?>
                                        </span>
                                    <?php else : ?>
                                        <span class="client-status active">
                                            <span class="dashicons dashicons-yes-alt"></span>
                                            <?php _e('Active', 'wp-oauth-remote-logout'); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="wo-card wo-full-width">
    <div class="wo-card-header">
        <h3>
            <span class="dashicons dashicons-book"></span>
            <?php _e('Client Usage Instructions', 'wp-oauth-remote-logout'); ?>
        </h3>
    </div>
    <div class="wo-card-content">
        <div class="wo-instruction-section">
            <h4><?php _e('Endpoint URL', 'wp-oauth-remote-logout'); ?></h4>
            <p><?php _e('OAuth clients can log users out of WordPress by making a POST request to:', 'wp-oauth-remote-logout'); ?></p>
            <div class="wo-endpoint-display">
                <code><?php echo esc_url(rest_url('wp-oauth/v1/user-logout')); ?></code>
                <button class="copy-endpoint button-link" data-endpoint="<?php echo esc_attr(rest_url('wp-oauth/v1/user-logout')); ?>" data-success-text="<?php esc_attr_e('Copied!', 'wp-oauth-remote-logout'); ?>" title="<?php esc_attr_e('Copy to clipboard', 'wp-oauth-remote-logout'); ?>">
                    <span class="dashicons dashicons-clipboard"></span>
                    <span class="screen-reader-text"><?php _e('Copy endpoint URL', 'wp-oauth-remote-logout'); ?></span>
                </button>
            </div>
        </div>

        <div class="wo-instruction-section">
            <h4><?php _e('Request Parameters', 'wp-oauth-remote-logout'); ?></h4>
            <table class="wo-params-table">
                <tr>
                    <th><code>wp_user_id</code></th>
                    <td>
                        <strong><?php _e('Required', 'wp-oauth-remote-logout'); ?></strong>
                        <p><?php _e('The WordPress user ID to log out', 'wp-oauth-remote-logout'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><code>client_id</code></th>
                    <td>
                        <strong><?php _e('Optional', 'wp-oauth-remote-logout'); ?></strong>
                        <p><?php _e('The client ID performing the logout', 'wp-oauth-remote-logout'); ?></p>
                    </td>
                </tr>
            </table>
        </div>

        <div class="wo-instruction-section">
            <h4><?php _e('Authentication', 'wp-oauth-remote-logout'); ?></h4>
            <p><?php _e('Requests must include a valid OAuth Bearer token in the Authorization header.', 'wp-oauth-remote-logout'); ?></p>
        </div>

        <div class="wo-instruction-section">
            <h4><?php _e('Example (PHP)', 'wp-oauth-remote-logout'); ?></h4>
            <div class="wo-code-sample">
                <div class="wo-code-header">
                    <span><?php _e('PHP', 'wp-oauth-remote-logout'); ?></span>
                    <button class="copy-code button-link" data-code='<?php echo esc_attr('$response = wp_remote_post(
    \'' . rest_url('wp-oauth/v1/user-logout') . '\',
    [
        \'headers\' => [
            \'Authorization\' => \'Bearer \' . $access_token,
            \'Content-Type\' => \'application/json\'
        ],
        \'body\' => json_encode([
            \'wp_user_id\' => 123,
            \'client_id\' => \'your_client_id\'
        ])
    ]
);'); ?>' data-success-text="<?php esc_attr_e('Code copied!', 'wp-oauth-remote-logout'); ?>" title="<?php esc_attr_e('Copy to clipboard', 'wp-oauth-remote-logout'); ?>">
                        <span class="dashicons dashicons-clipboard"></span>
                        <span class="screen-reader-text"><?php _e('Copy code', 'wp-oauth-remote-logout'); ?></span>
                    </button>
                </div>
                <pre><code>$response = wp_remote_post(
    '<?php echo esc_url(rest_url('wp-oauth/v1/user-logout')); ?>',
    [
        'headers' => [
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type' => 'application/json'
        ],
        'body' => json_encode([
            'wp_user_id' => 123,
            'client_id' => 'your_client_id'
        ])
    ]
);</code></pre>
            </div>
        </div>
    </div>
</div>

<style>
    /* Clients Tab Specific Styles */
    .wo-clients-header {
        margin-bottom: 25px;
    }

    .wo-clients-intro {
        background: #fff;
        border-left: 4px solid #2271b1;
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
        padding: 20px;
        margin-bottom: 20px;
    }

    .wo-empty-state {
        text-align: center;
        padding: 40px 20px;
        background: #f9f9f9;
        border-radius: 4px;
    }

    .wo-empty-state .dashicons {
        font-size: 48px;
        width: 48px;
        height: 48px;
        color: #dcdcde;
        margin-bottom: 15px;
    }

    .wo-empty-state p {
        color: #646970;
        font-size: 14px;
        margin: 0 0 20px;
    }

    .wo-empty-state .button {
        display: inline-flex;
        align-items: center;
    }

    .wo-empty-state .button .dashicons {
        font-size: 16px;
        width: 16px;
        height: 16px;
        margin-right: 5px;
        margin-bottom: 0;
        color: inherit;
    }

    .wo-table-description {
        margin-bottom: 15px;
        font-style: italic;
        color: #646970;
    }

    .wo-table-responsive {
        overflow-x: auto;
    }

    .wo-clients-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    .wo-clients-table th {
        background: #f9f9f9;
        padding: 10px;
        text-align: left;
        font-weight: 600;
        border-bottom: 1px solid #e5e5e5;
    }

    .wo-clients-table td {
        padding: 12px 10px;
        border-bottom: 1px solid #f0f0f0;
        vertical-align: middle;
    }

    .wo-clients-table tr:hover {
        background-color: #f9f9f9;
    }

    .client-id,
    .client-secret,
    .redirect-uri code {
        background: #f1f1f1;
        padding: 3px 6px;
        border-radius: 3px;
        font-size: 12px;
        font-family: monospace;
        word-break: break-all;
    }

    .client-name {
        font-weight: 500;
    }

    .client-secret-container {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .toggle-secret {
        display: flex;
        align-items: center;
        color: #2271b1;
        padding: 0;
        background: none;
        border: none;
        cursor: pointer;
        text-decoration: none;
        font-size: 12px;
        transition: color 0.2s;
    }

    .toggle-secret:hover {
        color: #135e96;
    }

    .toggle-secret .dashicons {
        font-size: 16px;
        width: 16px;
        height: 16px;
        margin-left: 3px;
    }

    .client-status {
        display: inline-flex;
        align-items: center;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }

    .client-status.active {
        background-color: #f0f9e6;
        color: #46b450;
    }

    .client-status .dashicons {
        font-size: 14px;
        width: 14px;
        height: 14px;
        margin-right: 3px;
    }

    .na {
        color: #888;
        font-style: italic;
    }

    /* Instructions section */
    .wo-instruction-section {
        margin-bottom: 30px;
    }

    .wo-instruction-section:last-child {
        margin-bottom: 0;
    }

    .wo-instruction-section h4 {
        margin-top: 0;
        margin-bottom: 10px;
        font-size: 15px;
        color: #1d2327;
        padding-bottom: 5px;
        border-bottom: 1px solid #f0f0f0;
    }

    .wo-endpoint-display {
        display: flex;
        align-items: center;
        background: #f1f1f1;
        padding: 8px 12px;
        border-radius: 4px;
        margin: 10px 0;
    }

    .wo-endpoint-display code {
        flex: 1;
        background: none;
        padding: 0;
        font-family: monospace;
        font-size: 14px;
        word-break: break-all;
    }

    .copy-endpoint,
    .copy-code {
        color: #2271b1;
        cursor: pointer;
        background: none;
        border: none;
        padding: 6px;
        margin-left: 10px;
        border-radius: 3px;
        transition: background-color 0.2s, color 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .copy-endpoint:hover,
    .copy-code:hover {
        color: #135e96;
        background-color: rgba(0, 0, 0, 0.05);
    }

    .copy-endpoint .dashicons,
    .copy-code .dashicons {
        font-size: 18px;
        width: 18px;
        height: 18px;
    }

    .copy-feedback {
        position: fixed;
        z-index: 9999;
        left: 50%;
        top: 20px;
        transform: translateX(-50%);
        padding: 8px 16px;
        background-color: #333;
        color: #fff;
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        opacity: 0;
    }

    .wo-params-table {
        width: 100%;
        border-collapse: collapse;
        margin: 10px 0;
    }

    .wo-params-table th {
        text-align: left;
        padding: 10px;
        width: 120px;
        background: #f9f9f9;
        border: 1px solid #e5e5e5;
        vertical-align: top;
    }

    .wo-params-table td {
        padding: 10px;
        border: 1px solid #e5e5e5;
    }

    .wo-params-table td p {
        margin: 5px 0 0;
        color: #646970;
    }

    .wo-code-sample {
        margin-top: 10px;
        background: #f9f9f9;
        border-radius: 4px;
        overflow: hidden;
    }

    .wo-code-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 12px;
        background: #f1f1f1;
        border-bottom: 1px solid #e5e5e5;
        font-size: 13px;
        font-weight: 500;
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
    }

    @media screen and (max-width: 782px) {

        .wo-params-table th,
        .wo-params-table td {
            display: block;
            width: 100%;
        }

        .wo-params-table th {
            border-bottom: none;
        }
    }
</style>

<!-- JavaScript functionality is now in the shared JS file -->