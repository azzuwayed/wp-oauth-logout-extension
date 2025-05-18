<?php

/**
 * Test tab view
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get users for dropdown
$users = function_exists('get_users') ? get_users(['fields' => ['ID', 'user_login', 'user_email']]) : array();

// Handle status messages from direct logout
$logout_status = isset($_GET['logout_status']) ? sanitize_text_field($_GET['logout_status']) : '';
$logout_user = isset($_GET['user']) ? sanitize_text_field($_GET['user']) : '';
$logout_count = isset($_GET['count']) ? intval($_GET['count']) : 0;
$tokens_revoked = isset($_GET['tokens']) ? intval($_GET['tokens']) : 0;

?>

<div class="wo-test-header">
    <div class="wo-test-intro">
        <h2 class="wo-section-title">
            <span class="dashicons dashicons-admin-tools"></span>
            <?php _e('Debug & Testing Tools', 'wp-oauth-remote-logout'); ?>
        </h2>
        <p class="wo-section-description">
            <?php _e('Utilities to test and troubleshoot the remote logout functionality.', 'wp-oauth-remote-logout'); ?>
        </p>
    </div>
</div>

<?php if ($logout_status === 'success'): ?>
    <div class="wo-notice success">
        <span class="wo-notice-icon">
            <span class="dashicons dashicons-yes-alt"></span>
        </span>
        <div class="wo-notice-content">
            <p>
                <?php echo sprintf(
                    __('Successfully terminated %d sessions and revoked %d OAuth tokens for user %s', 'wp-oauth-remote-logout'),
                    $logout_count,
                    $tokens_revoked,
                    '<strong>' . esc_html($logout_user) . '</strong>'
                ); ?>
            </p>
        </div>
    </div>
<?php endif; ?>

<div class="wo-card wo-full-width">
    <div class="wo-card-header">
        <h3>
            <span class="dashicons dashicons-admin-users"></span>
            <?php _e('Test Direct Logout', 'wp-oauth-remote-logout'); ?>
        </h3>
    </div>
    <div class="wo-card-content">
        <p class="wo-card-description">
            <?php _e('Use this tool to test the remote logout functionality directly from the admin panel.', 'wp-oauth-remote-logout'); ?>
        </p>

        <form method="post" action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" class="wo-test-form">
            <input type="hidden" name="action" value="wo_direct_logout">
            <?php wp_nonce_field('wo_direct_logout_nonce', 'wo_direct_logout_nonce'); ?>

            <div class="wo-form-row">
                <label for="wo_test_user" class="wo-form-label">
                    <?php _e('User to Log Out', 'wp-oauth-remote-logout'); ?>
                </label>
                <div class="wo-form-input">
                    <select name="wo_test_user" id="wo_test_user" class="wo-select" required>
                        <option value=""><?php _e('-- Select User --', 'wp-oauth-remote-logout'); ?></option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo esc_attr($user->ID); ?>">
                                <?php echo esc_html($user->user_login); ?> (<?php echo esc_html($user->user_email); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="wo-form-description">
                        <?php _e('This will terminate all active sessions for the selected user.', 'wp-oauth-remote-logout'); ?>
                    </p>
                </div>
            </div>

            <div class="wo-form-submit">
                <button type="submit" name="wo_test_submit" class="button button-primary">
                    <span class="dashicons dashicons-exit"></span>
                    <?php _e('Test Remote Logout', 'wp-oauth-remote-logout'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="wo-card wo-full-width">
    <div class="wo-card-header">
        <h3>
            <span class="dashicons dashicons-code-standards"></span>
            <?php _e('Test Debug Endpoint', 'wp-oauth-remote-logout'); ?>
        </h3>
    </div>
    <div class="wo-card-content">
        <p class="wo-card-description">
            <?php _e('Test the debug endpoint which allows direct logout without requiring OAuth tokens. Only administrators can use this endpoint.', 'wp-oauth-remote-logout'); ?>
        </p>

        <div class="wo-test-form">
            <div class="wo-form-row">
                <label for="debug-user" class="wo-form-label">
                    <?php _e('User to Log Out', 'wp-oauth-remote-logout'); ?>
                </label>
                <div class="wo-form-input">
                    <select id="debug-user" class="wo-select">
                        <option value=""><?php _e('-- Select User --', 'wp-oauth-remote-logout'); ?></option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo esc_attr($user->ID); ?>">
                                <?php echo esc_html($user->user_login); ?> (<?php echo esc_html($user->user_email); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="wo-form-row">
                <label for="debug-client" class="wo-form-label">
                    <?php _e('Client ID (Optional)', 'wp-oauth-remote-logout'); ?>
                </label>
                <div class="wo-form-input">
                    <input type="text" id="debug-client" class="wo-text-input" placeholder="debug_api">
                    <p class="wo-form-description">
                        <?php _e('Used for tracking purposes in logs. Default: debug_api', 'wp-oauth-remote-logout'); ?>
                    </p>
                </div>
            </div>

            <div class="wo-form-submit">
                <button type="button" id="test-debug-endpoint" class="button button-primary">
                    <span class="dashicons dashicons-search"></span>
                    <?php _e('Test Debug Endpoint', 'wp-oauth-remote-logout'); ?>
                </button>
            </div>

            <div id="debug-results" class="wo-results-container" style="display: none;">
                <div class="wo-results-header">
                    <h4>
                        <span class="dashicons dashicons-editor-code"></span>
                        <?php _e('Debug Endpoint Results', 'wp-oauth-remote-logout'); ?>
                    </h4>
                </div>
                <pre id="debug-response" class="wo-response-code"></pre>
            </div>
        </div>
    </div>
</div>

<div class="wo-card wo-full-width">
    <div class="wo-card-header">
        <h3>
            <span class="dashicons dashicons-rest-api"></span>
            <?php _e('Test API Endpoint Directly', 'wp-oauth-remote-logout'); ?>
        </h3>
    </div>
    <div class="wo-card-content">
        <p class="wo-card-description">
            <?php _e('Test the API endpoint used by client applications. This requires a valid OAuth Bearer token.', 'wp-oauth-remote-logout'); ?>
        </p>

        <div class="wo-test-form">
            <div class="wo-form-row">
                <label for="api-token" class="wo-form-label">
                    <?php _e('OAuth Bearer Token', 'wp-oauth-remote-logout'); ?>
                </label>
                <div class="wo-form-input">
                    <input type="text" id="api-token" class="wo-text-input" placeholder="Enter a valid OAuth token">
                    <p class="wo-form-description">
                        <?php _e('Required. A valid OAuth 2.0 access token for authentication.', 'wp-oauth-remote-logout'); ?>
                    </p>
                </div>
            </div>

            <div class="wo-form-row">
                <label for="api-user" class="wo-form-label">
                    <?php _e('User ID to Log Out', 'wp-oauth-remote-logout'); ?>
                </label>
                <div class="wo-form-input">
                    <select id="api-user" class="wo-select">
                        <option value=""><?php _e('-- Select User --', 'wp-oauth-remote-logout'); ?></option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo esc_attr($user->ID); ?>">
                                <?php echo esc_html($user->user_login); ?> (<?php echo esc_html($user->user_email); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="wo-form-row">
                <label for="api-client" class="wo-form-label">
                    <?php _e('Client ID (Optional)', 'wp-oauth-remote-logout'); ?>
                </label>
                <div class="wo-form-input">
                    <input type="text" id="api-client" class="wo-text-input" placeholder="Your client_id">
                    <p class="wo-form-description">
                        <?php _e('Identifies the client application making the request.', 'wp-oauth-remote-logout'); ?>
                    </p>
                </div>
            </div>

            <div class="wo-form-submit">
                <button type="button" id="test-api-endpoint" class="button button-primary">
                    <span class="dashicons dashicons-visibility"></span>
                    <?php _e('Test API Endpoint', 'wp-oauth-remote-logout'); ?>
                </button>
            </div>

            <div id="api-results" class="wo-results-container" style="display: none;">
                <div class="wo-results-header">
                    <h4>
                        <span class="dashicons dashicons-editor-code"></span>
                        <?php _e('API Endpoint Results', 'wp-oauth-remote-logout'); ?>
                    </h4>
                </div>
                <pre id="api-response" class="wo-response-code"></pre>
            </div>
        </div>
    </div>
</div>

<div class="wo-card wo-full-width">
    <div class="wo-card-header">
        <h3>
            <span class="dashicons dashicons-info-outline"></span>
            <?php _e('Debug Information', 'wp-oauth-remote-logout'); ?>
        </h3>
    </div>
    <div class="wo-card-content">
        <div class="wo-debug-grid">
            <div class="wo-debug-item">
                <div class="wo-debug-label">
                    <?php _e('REST API Endpoint', 'wp-oauth-remote-logout'); ?>
                </div>
                <div class="wo-debug-value">
                    <code class="wo-endpoint-code"><?php echo esc_url(rest_url('wp-oauth/v1/user-logout')); ?></code>
                    <button class="copy-endpoint button-link" data-endpoint="<?php echo esc_attr(rest_url('wp-oauth/v1/user-logout')); ?>" title="<?php esc_attr_e('Copy to clipboard', 'wp-oauth-remote-logout'); ?>">
                        <span class="dashicons dashicons-clipboard"></span>
                        <span class="screen-reader-text"><?php _e('Copy endpoint URL', 'wp-oauth-remote-logout'); ?></span>
                    </button>
                </div>
            </div>

            <div class="wo-debug-item">
                <div class="wo-debug-label">
                    <?php _e('Debug Endpoint (Admin Only)', 'wp-oauth-remote-logout'); ?>
                </div>
                <div class="wo-debug-value">
                    <code class="wo-endpoint-code"><?php echo esc_url(rest_url('wp-oauth/v1/debug/user-logout')); ?></code>
                    <button class="copy-endpoint button-link" data-endpoint="<?php echo esc_attr(rest_url('wp-oauth/v1/debug/user-logout')); ?>" title="<?php esc_attr_e('Copy to clipboard', 'wp-oauth-remote-logout'); ?>">
                        <span class="dashicons dashicons-clipboard"></span>
                        <span class="screen-reader-text"><?php _e('Copy endpoint URL', 'wp-oauth-remote-logout'); ?></span>
                    </button>
                </div>
            </div>

            <div class="wo-debug-item">
                <div class="wo-debug-label">
                    <?php _e('Current User Permissions', 'wp-oauth-remote-logout'); ?>
                </div>
                <div class="wo-debug-value">
                    <?php if (current_user_can('manage_options')) : ?>
                        <span class="wo-status-badge success">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <?php _e('Administrator access granted', 'wp-oauth-remote-logout'); ?>
                        </span>
                        <p class="wo-status-description">
                            <?php _e('You have administrator privileges and can access the debug endpoint.', 'wp-oauth-remote-logout'); ?>
                        </p>
                    <?php else : ?>
                        <span class="wo-status-badge error">
                            <span class="dashicons dashicons-no-alt"></span>
                            <?php _e('No administrator access', 'wp-oauth-remote-logout'); ?>
                        </span>
                        <p class="wo-status-description">
                            <?php _e('You do not have administrator privileges.', 'wp-oauth-remote-logout'); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="wo-debug-item">
                <div class="wo-debug-label">
                    <?php _e('REST API Status', 'wp-oauth-remote-logout'); ?>
                </div>
                <div class="wo-debug-value">
                    <?php $rest_available = (bool) has_action('rest_api_init'); ?>
                    <?php if ($rest_available) : ?>
                        <span class="wo-status-badge success">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <?php _e('REST API Available', 'wp-oauth-remote-logout'); ?>
                        </span>
                        <p class="wo-status-description">
                            <?php _e('WordPress REST API is available and properly configured.', 'wp-oauth-remote-logout'); ?>
                        </p>
                    <?php else : ?>
                        <span class="wo-status-badge error">
                            <span class="dashicons dashicons-warning"></span>
                            <?php _e('REST API Unavailable', 'wp-oauth-remote-logout'); ?>
                        </span>
                        <p class="wo-status-description">
                            <?php _e('WordPress REST API is not available. This plugin requires the REST API.', 'wp-oauth-remote-logout'); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Test Tab Specific Styles */
    .wo-test-header {
        margin-bottom: 25px;
    }

    .wo-test-intro {
        background: #fff;
        border-left: 4px solid #2271b1;
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
        padding: 20px;
        margin-bottom: 20px;
    }

    .wo-notice {
        display: flex;
        padding: 15px 20px;
        margin-bottom: 25px;
        border-radius: 4px;
        background: #f9f9f9;
        border: 1px solid #e5e5e5;
    }

    .wo-notice.success {
        background-color: #f0f9e6;
        border-color: #46b450;
    }

    .wo-notice.error {
        background-color: #fee;
        border-color: #dc3232;
    }

    .wo-notice-icon {
        margin-right: 15px;
        display: flex;
        align-items: center;
    }

    .wo-notice-icon .dashicons {
        font-size: 24px;
        width: 24px;
        height: 24px;
    }

    .wo-notice.success .wo-notice-icon .dashicons {
        color: #46b450;
    }

    .wo-notice.error .wo-notice-icon .dashicons {
        color: #dc3232;
    }

    .wo-notice-content {
        flex: 1;
    }

    .wo-notice-content p {
        margin: 0;
    }

    .wo-card-description {
        margin-top: 0;
        margin-bottom: 20px;
        color: #646970;
    }

    /* Form Styling */
    .wo-test-form {
        max-width: 800px;
    }

    .wo-form-row {
        margin-bottom: 20px;
        display: flex;
        flex-wrap: wrap;
    }

    .wo-form-label {
        flex: 0 0 200px;
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
    }

    .wo-form-input {
        flex: 1;
        min-width: 300px;
    }

    .wo-form-description {
        margin-top: 5px;
        font-size: 13px;
        color: #646970;
    }

    .wo-select,
    .wo-text-input {
        width: 100%;
        padding: 6px 12px;
        height: 36px;
        max-width: 400px;
        border: 1px solid #dcdcde;
        border-radius: 4px;
    }

    .wo-form-submit {
        margin-top: 25px;
        padding-top: 15px;
        border-top: 1px solid #f0f0f0;
    }

    .wo-form-submit .button {
        display: flex;
        align-items: center;
        height: auto;
        padding: 6px 15px;
    }

    .wo-form-submit .button .dashicons {
        margin-right: 5px;
        font-size: 16px;
        width: 16px;
        height: 16px;
    }

    /* Results Container */
    .wo-results-container {
        margin-top: 25px;
        border: 1px solid #e5e5e5;
        border-radius: 4px;
        overflow: hidden;
    }

    .wo-results-header {
        background: #f9f9f9;
        padding: 10px 15px;
        border-bottom: 1px solid #e5e5e5;
    }

    .wo-results-header h4 {
        margin: 0;
        display: flex;
        align-items: center;
        font-size: 14px;
    }

    .wo-results-header .dashicons {
        margin-right: 8px;
        color: #2271b1;
    }

    .wo-response-code {
        margin: 0;
        padding: 15px;
        background: #f1f1f1;
        overflow-x: auto;
        font-family: monospace;
        font-size: 13px;
        line-height: 1.5;
        max-height: 300px;
    }

    /* Debug Info Section */
    .wo-debug-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
        gap: 20px;
    }

    .wo-debug-item {
        padding: 15px;
        background: #f9f9f9;
        border: 1px solid #e5e5e5;
        border-radius: 4px;
    }

    .wo-debug-label {
        font-weight: 500;
        margin-bottom: 10px;
        color: #1d2327;
    }

    .wo-debug-value {
        position: relative;
    }

    .wo-endpoint-code {
        display: block;
        padding: 8px 12px;
        background: #f1f1f1;
        border-radius: 4px;
        font-family: monospace;
        font-size: 13px;
        word-break: break-all;
        padding-right: 40px;
        transition: background-color 0.2s;
    }

    .copy-endpoint {
        position: absolute;
        right: 8px;
        top: 8px;
        color: #2271b1;
        cursor: pointer;
        background: none;
        border: none;
        padding: 5px;
        border-radius: 3px;
        transition: background-color 0.2s, color 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .copy-endpoint:hover {
        color: #135e96;
        background-color: rgba(0, 0, 0, 0.1);
    }

    .copy-endpoint:hover .dashicons {
        color: #135e96;
    }

    .copy-endpoint:focus {
        box-shadow: 0 0 0 1px #2271b1;
        outline: none;
    }

    .copy-endpoint .dashicons {
        font-size: 16px;
        width: 16px;
        height: 16px;
    }

    .wo-debug-value {
        position: relative;
    }

    .wo-debug-value:hover .wo-endpoint-code {
        background-color: #f7f7f7;
    }

    .wo-status-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 13px;
        font-weight: 500;
    }

    .wo-status-badge.success {
        background-color: #f0f9e6;
        color: #46b450;
    }

    .wo-status-badge.error {
        background-color: #fee;
        color: #dc3232;
    }

    .wo-status-badge .dashicons {
        font-size: 14px;
        width: 14px;
        height: 14px;
        margin-right: 5px;
    }

    .wo-status-description {
        margin-top: 8px;
        margin-bottom: 0;
        font-size: 13px;
        color: #646970;
    }

    @media screen and (max-width: 782px) {
        .wo-form-row {
            flex-direction: column;
        }

        .wo-form-label {
            flex: none;
            margin-bottom: 5px;
        }

        .wo-form-input {
            flex: none;
            min-width: 0;
        }

        .wo-debug-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    jQuery(document).ready(function($) {
        // Toggle animation class for button effects
        $('<style>@keyframes rotation { from { transform: rotate(0deg); } to { transform: rotate(359deg); } }</style>').appendTo('head');

        // Helper function to show processing state
        function showProcessing(button, text) {
            button.prop('disabled', true)
                .html('<span class="dashicons dashicons-update-alt" style="animation: rotation 2s infinite linear; margin-right: 5px;"></span>' + text);
        }

        // Helper function to reset button
        function resetButton(button, icon, text) {
            button.prop('disabled', false)
                .html('<span class="dashicons ' + icon + '" style="margin-right: 5px;"></span>' + text);
        }

        // Test debug endpoint
        $('#test-debug-endpoint').on('click', function() {
            var userId = $('#debug-user').val();
            var clientId = $('#debug-client').val();
            var $button = $(this);

            if (!userId) {
                alert('<?php _e('Please select a user to log out', 'wp-oauth-remote-logout'); ?>');
                return;
            }

            $('#debug-results').hide();
            showProcessing($button, '<?php _e('Processing...', 'wp-oauth-remote-logout'); ?>');

            $.ajax({
                url: '<?php echo esc_url(rest_url('wp-oauth/v1/debug/user-logout')); ?>',
                method: 'POST',
                data: JSON.stringify({
                    wp_user_id: userId,
                    client_id: clientId || 'debug_api'
                }),
                contentType: 'application/json',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce('wp_rest'); ?>');
                },
                success: function(response) {
                    $('#debug-response').text(JSON.stringify(response, null, 2));
                    $('#debug-results').slideDown(300);
                },
                error: function(xhr) {
                    var errorMsg = xhr.responseJSON ? JSON.stringify(xhr.responseJSON, null, 2) : xhr.responseText;
                    $('#debug-response').text(errorMsg);
                    $('#debug-results').slideDown(300);
                },
                complete: function() {
                    resetButton($button, 'dashicons-search', '<?php _e('Test Debug Endpoint', 'wp-oauth-remote-logout'); ?>');
                }
            });
        });

        // Test API endpoint
        $('#test-api-endpoint').on('click', function() {
            var token = $('#api-token').val();
            var userId = $('#api-user').val();
            var clientId = $('#api-client').val();
            var $button = $(this);

            if (!token) {
                alert('<?php _e('Please enter an OAuth Bearer token', 'wp-oauth-remote-logout'); ?>');
                return;
            }

            if (!userId) {
                alert('<?php _e('Please select a user to log out', 'wp-oauth-remote-logout'); ?>');
                return;
            }

            $('#api-results').hide();
            showProcessing($button, '<?php _e('Processing...', 'wp-oauth-remote-logout'); ?>');

            var data = {
                wp_user_id: userId
            };

            if (clientId) {
                data.client_id = clientId;
            }

            $.ajax({
                url: '<?php echo esc_url(rest_url('wp-oauth/v1/user-logout')); ?>',
                method: 'POST',
                data: JSON.stringify(data),
                contentType: 'application/json',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('Authorization', 'Bearer ' + token);
                },
                success: function(response) {
                    $('#api-response').text(JSON.stringify(response, null, 2));
                    $('#api-results').slideDown(300);
                },
                error: function(xhr) {
                    var errorMsg = xhr.responseJSON ? JSON.stringify(xhr.responseJSON, null, 2) : xhr.responseText;
                    $('#api-response').text(errorMsg);
                    $('#api-results').slideDown(300);
                },
                complete: function() {
                    resetButton($button, 'dashicons-visibility', '<?php _e('Test API Endpoint', 'wp-oauth-remote-logout'); ?>');
                }
            });
        });

        // Copy endpoint URL functionality
        $('.copy-endpoint').on('click', function() {
            var $button = $(this);
            var text = $(this).data('endpoint');
            var $temp = $("<textarea>");
            $("body").append($temp);
            $temp.val(text).select();
            document.execCommand("copy");
            $temp.remove();

            // Show visual feedback
            var originalIcon = $button.find('.dashicons').attr('class');
            $button.find('.dashicons').removeClass().addClass('dashicons dashicons-yes-alt');
            $button.css('color', '#46b450');

            // Create and show tooltip
            var $feedback = $('<div class="copy-tooltip">Copied!</div>');
            $('body').append($feedback);

            var buttonOffset = $button.offset();
            $feedback.css({
                position: 'absolute',
                zIndex: 9999,
                left: buttonOffset.left - 20,
                top: buttonOffset.top - 30,
                padding: '4px 8px',
                backgroundColor: '#333',
                color: '#fff',
                fontSize: '12px',
                borderRadius: '3px',
                opacity: 0
            }).animate({
                opacity: 1,
                top: buttonOffset.top - 35
            }, 200).delay(1500).animate({
                opacity: 0,
                top: buttonOffset.top - 30
            }, 200, function() {
                $(this).remove();

                // Reset button
                $button.css('color', '');
                $button.find('.dashicons').removeClass().addClass(originalIcon);
            });
        });
    });
</script>

<!-- Add a style for the copy tooltip animation -->
<style>
    .copy-tooltip {
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        pointer-events: none;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(5px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>