<?php

/**
 * Statistics Tab
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get client statistics data
$client_stats = isset($_client_stats) ? $_client_stats : array();
$stats = isset($_stats) ? $_stats : array(
    'total_attempts' => 0,
    'successful' => 0,
    'failed' => 0
);

// Get statistics period
$stats_period = isset($_options['stats_period_days']) ? intval($_options['stats_period_days']) : 30;

// Calculate overall success rate
$success_rate = ($stats['total_attempts'] > 0)
    ? round(($stats['successful'] / $stats['total_attempts']) * 100, 1)
    : 0;
?>

<div class="wo-stats-header">
    <div class="wo-stats-intro">
        <h2 class="wo-section-title">
            <span class="dashicons dashicons-chart-bar"></span>
            <?php _e('Remote Logout Statistics', 'wp-oauth-remote-logout'); ?>
        </h2>
        <p class="wo-section-description">
            <?php _e('Analyze usage patterns and performance of remote logout functionality across your site.', 'wp-oauth-remote-logout'); ?>
        </p>
    </div>
</div>

<div class="wo-card wo-full-width">
    <div class="wo-card-header">
        <h3>
            <span class="dashicons dashicons-chart-line"></span>
            <?php _e('Activity Summary', 'wp-oauth-remote-logout'); ?> (<?php _e('Last', 'wp-oauth-remote-logout'); ?> <?php echo $stats_period; ?> <?php _e('Days', 'wp-oauth-remote-logout'); ?>)
        </h3>
    </div>
    <div class="wo-card-content">
        <div class="wo-stats-summary">
            <div class="wo-stat-card">
                <div class="wo-stat-icon">
                    <span class="dashicons dashicons-update"></span>
                </div>
                <div class="wo-stat-content">
                    <span class="wo-stat-number"><?php echo number_format($stats['total_attempts']); ?></span>
                    <span class="wo-stat-label"><?php _e('Total Requests', 'wp-oauth-remote-logout'); ?></span>
                </div>
            </div>

            <div class="wo-stat-card">
                <div class="wo-stat-icon success">
                    <span class="dashicons dashicons-yes-alt"></span>
                </div>
                <div class="wo-stat-content">
                    <span class="wo-stat-number success"><?php echo number_format($stats['successful']); ?></span>
                    <span class="wo-stat-label"><?php _e('Successful Logouts', 'wp-oauth-remote-logout'); ?></span>
                </div>
            </div>

            <div class="wo-stat-card">
                <div class="wo-stat-icon wo-error">
                    <span class="dashicons dashicons-no-alt"></span>
                </div>
                <div class="wo-stat-content">
                    <span class="wo-stat-number error"><?php echo number_format($stats['failed']); ?></span>
                    <span class="wo-stat-label"><?php _e('Failed Logouts', 'wp-oauth-remote-logout'); ?></span>
                </div>
            </div>

            <div class="wo-stat-card">
                <div class="wo-stat-icon <?php echo $success_rate >= 90 ? 'success' : ($success_rate >= 70 ? 'warning' : 'wo-error'); ?>">
                    <span class="dashicons dashicons-chart-pie"></span>
                </div>
                <div class="wo-stat-content">
                    <span class="wo-stat-number <?php echo $success_rate >= 90 ? 'success' : ($success_rate >= 70 ? 'warning' : 'error'); ?>">
                        <?php echo $success_rate; ?>%
                    </span>
                    <span class="wo-stat-label"><?php _e('Success Rate', 'wp-oauth-remote-logout'); ?></span>
                    <div class="success-rate-container">
                        <div class="success-rate-bar">
                            <div class="success-rate-fill <?php echo $success_rate >= 90 ? 'high' : ($success_rate >= 70 ? 'medium' : 'low'); ?>"
                                style="width: <?php echo max(1, $success_rate); ?>%;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="wo-card wo-full-width">
    <div class="wo-card-header">
        <h3>
            <span class="dashicons dashicons-admin-users"></span>
            <?php _e('Client Activity Analysis', 'wp-oauth-remote-logout'); ?>
        </h3>
    </div>
    <div class="wo-card-content">
        <?php if (empty($client_stats)) : ?>
            <div class="wo-empty-state">
                <span class="dashicons dashicons-chart-area"></span>
                <p><?php _e('No client activity data found for the past', 'wp-oauth-remote-logout'); ?> <?php echo $stats_period; ?> <?php _e('days.', 'wp-oauth-remote-logout'); ?></p>
                <p class="wo-subtext"><?php _e('Statistics will appear here once remote logout actions occur.', 'wp-oauth-remote-logout'); ?></p>
            </div>
        <?php else : ?>
            <div class="wo-table-responsive">
                <table class="wo-stats-table widefat">
                    <thead>
                        <tr>
                            <th><?php _e('Client', 'wp-oauth-remote-logout'); ?></th>
                            <th class="wo-center"><?php _e('Total', 'wp-oauth-remote-logout'); ?></th>
                            <th class="wo-center success-column"><?php _e('Success', 'wp-oauth-remote-logout'); ?></th>
                            <th class="wo-center failed-column"><?php _e('Failed', 'wp-oauth-remote-logout'); ?></th>
                            <th class="wo-center"><?php _e('Auth Failed', 'wp-oauth-remote-logout'); ?></th>
                            <th><?php _e('Success Rate', 'wp-oauth-remote-logout'); ?></th>
                            <th class="wo-center"><?php _e('Users', 'wp-oauth-remote-logout'); ?></th>
                            <th><?php _e('Last Activity', 'wp-oauth-remote-logout'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($client_stats as $client) : ?>
                            <tr>
                                <td class="client-name">
                                    <div class="client-info">
                                        <span class="client-icon"><span class="dashicons dashicons-admin-site-alt"></span></span>
                                        <div class="client-details">
                                            <strong><?php echo esc_html($client['client_name']); ?></strong>
                                            <span class="client-id"><?php echo esc_html($client['client_id']); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="wo-center wo-number"><?php echo number_format($client['total_requests']); ?></td>
                                <td class="wo-center wo-number success-column">
                                    <?php echo number_format($client['successful']); ?>
                                </td>
                                <td class="wo-center wo-number failed-column">
                                    <?php echo number_format($client['failed']); ?>
                                </td>
                                <td class="wo-center wo-number auth-failed-column">
                                    <?php echo number_format($client['auth_failed']); ?>
                                </td>
                                <td>
                                    <div class="wo-table-success-rate">
                                        <div class="success-rate-bar">
                                            <div class="success-rate-fill <?php echo $client['success_rate'] >= 90 ? 'high' : ($client['success_rate'] >= 70 ? 'medium' : 'low'); ?>"
                                                style="width: <?php echo max(1, $client['success_rate']); ?>%;">
                                            </div>
                                            <span class="success-rate-text"><?php echo $client['success_rate']; ?>%</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="wo-center wo-number"><?php echo number_format($client['unique_users']); ?></td>
                                <td class="last-activity">
                                    <?php
                                    $timestamp = strtotime($client['last_activity']);
                                    echo '<span class="activity-date">' . esc_html(date('Y-m-d H:i', $timestamp)) . '</span>';
                                    echo '<span class="activity-ago">' . esc_html(human_time_diff($timestamp, time())) . ' ago</span>';
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="wo-stats-legend">
                <div class="wo-legend-item high">
                    <span class="wo-legend-color"></span>
                    <span class="wo-legend-text"><?php _e('High (90%+)', 'wp-oauth-remote-logout'); ?></span>
                </div>
                <div class="wo-legend-item medium">
                    <span class="wo-legend-color"></span>
                    <span class="wo-legend-text"><?php _e('Medium (70-89%)', 'wp-oauth-remote-logout'); ?></span>
                </div>
                <div class="wo-legend-item low">
                    <span class="wo-legend-color"></span>
                    <span class="wo-legend-text"><?php _e('Low (<70%)', 'wp-oauth-remote-logout'); ?></span>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    /* Stats Tab Specific Styles */
    .wo-stats-header {
        margin-bottom: 25px;
    }

    .wo-stats-intro {
        background: #fff;
        border-left: 4px solid #2271b1;
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
        padding: 20px;
        margin-bottom: 20px;
    }

    .wo-stats-summary {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
    }

    .wo-stat-card {
        display: flex;
        background: #f9f9f9;
        border: 1px solid #e5e5e5;
        border-radius: 4px;
        padding: 20px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .wo-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.05);
    }

    .wo-stat-icon {
        background: #f0f0f1;
        border-radius: 50%;
        width: 64px;
        height: 64px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 20px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }

    .wo-stat-icon .dashicons {
        font-size: 28px;
        width: 28px;
        height: 28px;
        color: #3c434a;
    }

    .wo-stat-icon.success {
        background-color: #f0f9e6;
    }

    .wo-stat-icon.success .dashicons {
        color: #46b450;
    }

    .wo-stat-icon.wo-error {
        background-color: #fee;
    }

    .wo-stat-icon.wo-error .dashicons {
        color: #dc3232;
    }

    .wo-stat-icon.warning {
        background-color: #fef8ee;
    }

    .wo-stat-icon.warning .dashicons {
        color: #ffb900;
    }

    .wo-stat-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .wo-stat-number {
        font-size: 28px;
        font-weight: 600;
        line-height: 1.2;
        color: #1d2327;
    }

    .wo-stat-number.success {
        color: #46b450;
    }

    .wo-stat-number.error {
        color: #dc3232;
    }

    .wo-stat-number.warning {
        color: #ffb900;
    }

    .wo-stat-label {
        font-size: 14px;
        color: #646970;
        margin-top: 5px;
    }

    .success-rate-container {
        margin-top: 10px;
    }

    .success-rate-bar {
        position: relative;
        height: 12px;
        background-color: #f0f0f1;
        border-radius: 6px;
        overflow: hidden;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .success-rate-fill {
        height: 100%;
        background-color: #ccc;
        transition: width 0.5s ease;
    }

    .success-rate-fill.high {
        background-color: #46b450;
        background-image: linear-gradient(to right, #46b450, #57c462);
    }

    .success-rate-fill.medium {
        background-color: #ffb900;
        background-image: linear-gradient(to right, #ffb900, #ffc726);
    }

    .success-rate-fill.low {
        background-color: #dc3232;
        background-image: linear-gradient(to right, #dc3232, #e54545);
    }

    .success-rate-text {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 12px;
        font-weight: 600;
        color: #fff;
        text-shadow: 0 0 3px rgba(0, 0, 0, 0.6);
    }

    /* Client Stats Table */
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

    .wo-empty-state .wo-subtext {
        font-size: 13px;
        color: #8c8f94;
        margin-top: 8px;
    }

    .wo-table-responsive {
        overflow-x: auto;
        margin-bottom: 20px;
    }

    .wo-stats-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #e5e5e5;
    }

    .wo-stats-table th {
        background: #f9f9f9;
        padding: 10px;
        text-align: left;
        font-weight: 600;
        border-bottom: 1px solid #e5e5e5;
    }

    .wo-stats-table td {
        padding: 12px 10px;
        border-bottom: 1px solid #f0f0f0;
        vertical-align: middle;
    }

    .wo-stats-table tr:hover {
        background-color: #f9f9f9;
    }

    .wo-center {
        text-align: center;
    }

    .wo-number {
        font-weight: 500;
    }

    .client-info {
        display: flex;
        align-items: center;
    }

    .client-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        background: #f0f0f1;
        border-radius: 50%;
        margin-right: 10px;
    }

    .client-icon .dashicons {
        font-size: 16px;
        width: 16px;
        height: 16px;
        color: #3c434a;
    }

    .client-details {
        display: flex;
        flex-direction: column;
    }

    .client-id {
        font-size: 11px;
        color: #646970;
        margin-top: 2px;
    }

    .success-column {
        color: #46b450;
    }

    .failed-column,
    .auth-failed-column {
        color: #dc3232;
    }

    .wo-table-success-rate {
        width: 100%;
        max-width: 200px;
    }

    .wo-table-success-rate .success-rate-bar {
        height: 16px;
        border-radius: 8px;
    }

    .wo-table-success-rate .success-rate-text {
        font-size: 13px;
        font-weight: 600;
    }

    .last-activity {
        white-space: nowrap;
    }

    .activity-date {
        display: block;
        font-weight: 500;
    }

    .activity-ago {
        display: block;
        font-size: 12px;
        color: #646970;
    }

    /* Legend */
    .wo-stats-legend {
        display: flex;
        gap: 15px;
        margin-top: 15px;
        justify-content: flex-end;
    }

    .wo-legend-item {
        display: flex;
        align-items: center;
        font-size: 12px;
        color: #646970;
    }

    .wo-legend-color {
        width: 14px;
        height: 14px;
        border-radius: 2px;
        margin-right: 5px;
    }

    .wo-legend-item.high .wo-legend-color {
        background-color: #46b450;
    }

    .wo-legend-item.medium .wo-legend-color {
        background-color: #ffb900;
    }

    .wo-legend-item.low .wo-legend-color {
        background-color: #dc3232;
    }

    @media screen and (max-width: 782px) {
        .wo-stats-summary {
            grid-template-columns: repeat(auto-fill, minmax(100%, 1fr));
        }

        .wo-table-success-rate {
            max-width: 100px;
        }

        .success-rate-text {
            font-size: 10px;
        }

        .wo-stats-legend {
            flex-wrap: wrap;
        }
    }
</style>