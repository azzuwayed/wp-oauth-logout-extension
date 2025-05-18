<?php

/**
 * Activity Logs Tab
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get log data from passed variables
$logs = isset($_logs) ? $_logs : array();
$total_logs = isset($_total_logs) ? $_total_logs : 0;
$total_pages = isset($_total_pages) ? $_total_pages : 1;
$page = isset($_page) ? $_page : 1;
?>

<div class="wo-logs-header">
    <div class="wo-logs-intro">
        <h2 class="wo-section-title">
            <span class="dashicons dashicons-list-view"></span>
            <?php _e('Remote Logout Activity Logs', 'wp-oauth-remote-logout'); ?>
        </h2>
        <p class="wo-section-description">
            <?php _e('Track and monitor all remote logout events in your WordPress site.', 'wp-oauth-remote-logout'); ?>
        </p>
    </div>
</div>

<div class="wo-card wo-full-width">
    <div class="wo-card-header">
        <div class="wo-header-actions">
            <h3>
                <span class="dashicons dashicons-admin-plugins"></span>
                <?php _e('Event Log', 'wp-oauth-remote-logout'); ?>
            </h3>
            <div class="wo-action-buttons">
                <button type="button" id="clear-logs-btn" class="button button-secondary">
                    <span class="dashicons dashicons-trash"></span>
                    <?php _e('Clear All Logs', 'wp-oauth-remote-logout'); ?>
                </button>
                <button type="button" id="repair-table-btn" class="button button-secondary">
                    <span class="dashicons dashicons-database-view"></span>
                    <?php _e('Repair Table', 'wp-oauth-remote-logout'); ?>
                </button>
            </div>
        </div>
    </div>
    <div class="wo-card-content">
        <?php if (empty($logs)): ?>
            <div class="wo-empty-state">
                <span class="dashicons dashicons-marker"></span>
                <p><?php _e('No logs found. Events will appear here once remote logout actions occur.', 'wp-oauth-remote-logout'); ?></p>
            </div>
        <?php else: ?>
            <div class="wo-table-responsive">
                <table class="wo-logs-table widefat">
                    <thead>
                        <tr>
                            <th><?php _e('Time', 'wp-oauth-remote-logout'); ?></th>
                            <th><?php _e('Event', 'wp-oauth-remote-logout'); ?></th>
                            <th><?php _e('User', 'wp-oauth-remote-logout'); ?></th>
                            <th><?php _e('Client', 'wp-oauth-remote-logout'); ?></th>
                            <th><?php _e('IP Address', 'wp-oauth-remote-logout'); ?></th>
                            <th class="log-details-header" style="text-align: right;"><?php _e('Details', 'wp-oauth-remote-logout'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td class="log-time">
                                    <?php
                                    $timestamp = strtotime($log['created_at']);
                                    echo '<span class="log-date">' . esc_html(date('Y-m-d H:i:s', $timestamp)) . '</span>';
                                    echo '<span class="log-ago">' . esc_html(human_time_diff($timestamp, time())) . ' ago</span>';
                                    ?>
                                </td>
                                <td class="log-event">
                                    <?php
                                    $event_class = '';
                                    $event_icon = 'dashicons-info';

                                    switch ($log['event_type']) {
                                        case 'logout_success':
                                            $event_class = 'wo-status-success';
                                            $event_icon = 'dashicons-yes-alt';
                                            break;
                                        case 'logout_failed':
                                            $event_class = 'wo-status-error';
                                            $event_icon = 'dashicons-no-alt';
                                            break;
                                        case 'auth_failed':
                                            $event_class = 'wo-status-warning';
                                            $event_icon = 'dashicons-warning';
                                            break;
                                        case 'debug_logout':
                                            $event_class = 'wo-status-info';
                                            $event_icon = 'dashicons-admin-tools';
                                            break;
                                    }
                                    ?>
                                    <span class="event-badge <?php echo esc_attr($event_class); ?>">
                                        <span class="dashicons <?php echo esc_attr($event_icon); ?>"></span>
                                        <?php echo esc_html(wo_format_event_type($log['event_type'])); ?>
                                    </span>
                                </td>
                                <td class="log-user"><?php echo wp_kses_post(wo_get_user_display($log['user_id'])); ?></td>
                                <td class="log-client"><?php echo esc_html(wo_get_client_display($log['client_id'])); ?></td>
                                <td class="log-ip">
                                    <code><?php echo esc_html($log['ip_address']); ?></code>
                                </td>
                                <td class="log-details-cell">
                                    <button type="button"
                                        class="toggle-details-icon-btn"
                                        data-tooltip="<?php esc_attr_e('View Details', 'wp-oauth-remote-logout'); ?>"
                                        data-target="details-<?php echo esc_attr($log['id']); ?>"
                                        aria-label="<?php esc_attr_e('Toggle log details visibility', 'wp-oauth-remote-logout'); ?>">
                                        <span class="dashicons dashicons-visibility"
                                            title="<?php esc_attr_e('View Details', 'wp-oauth-remote-logout'); ?>">
                                        </span>
                                    </button>
                                </td>
                            </tr>
                            <tr class="details-row" id="details-<?php echo esc_attr($log['id']); ?>" style="display: none;">
                                <td colspan="6" class="details-cell">
                                    <div class="log-details">
                                        <?php echo wp_kses_post(wo_format_log_details($log['details'])); ?>

                                        <!-- Message from log -->
                                        <div class="log-message">
                                            <strong><?php _e('Message:', 'wp-oauth-remote-logout'); ?></strong>
                                            <?php echo esc_html($log['message']); ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="wo-pagination">
                    <div class="wo-pagination-info">
                        <span class="wo-pagination-count"><?php echo esc_html($total_logs); ?> <?php _e('items', 'wp-oauth-remote-logout'); ?></span>
                    </div>
                    <div class="wo-pagination-links">
                        <?php
                        $page_links = paginate_links([
                            'base' => add_query_arg('log_page', '%#%'),
                            'format' => '',
                            'prev_text' => __('&laquo;'),
                            'next_text' => __('&raquo;'),
                            'total' => $total_pages,
                            'current' => $page
                        ]);
                        echo wp_kses_post($page_links);
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<style>
    /* Logs Tab Specific Styles */
    .wo-logs-header {
        margin-bottom: 25px;
    }

    .wo-logs-intro {
        background: #fff;
        border-left: 4px solid #2271b1;
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
        padding: 20px;
        margin-bottom: 20px;
    }

    .wo-header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
    }

    .wo-action-buttons {
        display: flex;
        gap: 10px;
    }

    .wo-action-buttons .button {
        display: flex;
        align-items: center;
    }

    .wo-action-buttons .dashicons {
        margin-right: 4px;
        font-size: 16px;
        height: 16px;
        width: 16px;
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
        margin-bottom: 10px;
    }

    .wo-empty-state p {
        color: #646970;
        font-size: 14px;
        margin: 0;
    }

    .wo-table-responsive {
        overflow-x: auto;
        margin-bottom: 20px;
    }

    .wo-logs-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #e5e5e5;
    }

    .wo-logs-table th {
        background: #f9f9f9;
        padding: 10px;
        text-align: left;
        font-weight: 600;
        border-bottom: 1px solid #e5e5e5;
    }

    .wo-logs-table td {
        padding: 12px 10px;
        border-bottom: 1px solid #f0f0f0;
        vertical-align: middle;
    }

    .wo-logs-table tr:last-child td {
        border-bottom: none;
    }

    .wo-logs-table tr:hover {
        background-color: #f9f9f9;
    }

    .log-time {
        white-space: nowrap;
    }

    .log-date {
        display: block;
        font-weight: 500;
    }

    .log-ago {
        display: block;
        font-size: 12px;
        color: #646970;
    }

    .event-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }

    .event-badge .dashicons {
        font-size: 14px;
        width: 14px;
        height: 14px;
        margin-right: 4px;
    }

    .wo-status-success.event-badge {
        background-color: #f0f9e6;
        color: #46b450;
    }

    .wo-status-error.event-badge {
        background-color: #fee;
        color: #dc3232;
    }

    .wo-status-warning.event-badge {
        background-color: #fef8ee;
        color: #ffb900;
    }

    .wo-status-info.event-badge {
        background-color: #e6f6fb;
        color: #00a0d2;
    }

    .log-ip code {
        background: #f1f1f1;
        padding: 2px 4px;
        border-radius: 3px;
        font-size: 12px;
    }

    .toggle-details {
        display: flex;
        align-items: center;
        background-color: #f0f0f1;
        border: 1px solid #dcdcde;
        border-radius: 3px;
        padding: 4px 8px;
        transition: background-color 0.2s, color 0.2s, border-color 0.2s;
    }

    .toggle-details:hover {
        background-color: #dcdcde;
        color: #135e96;
        border-color: #bbb;
    }

    .toggle-details .dashicons {
        font-size: 16px;
        width: 16px;
        height: 16px;
        transition: transform 0.2s;
        margin-left: 5px;
    }

    .toggle-details.active {
        background-color: #e6f6fb;
        border-color: #00a0d2;
        color: #00a0d2;
    }

    .toggle-details.active .dashicons {
        transform: rotate(180deg);
    }

    .log-details {
        margin-top: 12px;
        padding: 15px;
        background: #f9f9f9;
        border: 1px solid #e5e5e5;
        border-radius: 4px;
        font-size: 13px;
    }

    .log-details pre {
        margin: 10px 0 0;
        padding: 10px;
        background: #f1f1f1;
        border: 1px solid #e5e5e5;
        border-radius: 3px;
        overflow-x: auto;
        font-family: monospace;
        font-size: 12px;
        max-height: 200px;
    }

    .log-message {
        margin-top: 15px;
        padding-top: 10px;
        border-top: 1px solid #e5e5e5;
        font-style: italic;
    }

    .wo-pagination {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-top: 1px solid #f0f0f0;
    }

    .wo-pagination-info {
        color: #646970;
    }

    .wo-pagination-links .page-numbers {
        display: inline-block;
        min-width: 28px;
        height: 28px;
        margin: 0 3px;
        padding: 0 5px;
        text-align: center;
        line-height: 28px;
        background: #f0f0f1;
        color: #2c3338;
        text-decoration: none;
        cursor: pointer;
        border: 1px solid #dcdcde;
        border-radius: 3px;
    }

    .wo-pagination-links .page-numbers.current {
        background: #2271b1;
        color: #fff;
        border-color: #2271b1;
    }

    .wo-pagination-links .page-numbers:hover:not(.current) {
        background: #dcdcde;
    }

    @media screen and (max-width: 782px) {
        .wo-header-actions {
            flex-direction: column;
            align-items: flex-start;
        }

        .wo-action-buttons {
            margin-top: 10px;
            width: 100%;
        }

        .event-badge {
            padding: 2px 6px;
            font-size: 11px;
        }

        .wo-pagination {
            flex-direction: column;
            gap: 10px;
        }
    }
</style>

<!-- JavaScript event handlers are now in the shared JS file -->

<!-- Add JavaScript to handle the toggle details icon -->
<script>
    jQuery(document).ready(function($) {
        // Toggle details visibility when eye icon is clicked
        $('.toggle-details-icon-btn').on('click', function() {
            const targetId = $(this).data('target');
            $('#' + targetId).toggle();

            // Toggle the icon appearance
            const icon = $(this).find('.dashicons');
            if (icon.hasClass('dashicons-visibility')) {
                icon.removeClass('dashicons-visibility').addClass('dashicons-hidden');
                $(this).attr('data-tooltip', '<?php esc_attr_e('Hide Details', 'wp-oauth-remote-logout'); ?>');
            } else {
                icon.removeClass('dashicons-hidden').addClass('dashicons-visibility');
                $(this).attr('data-tooltip', '<?php esc_attr_e('View Details', 'wp-oauth-remote-logout'); ?>');
            }

            return false;
        });
    });
</script>