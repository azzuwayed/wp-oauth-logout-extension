<?php

/**
 * Admin settings tab view - Enhanced UI
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get options with defaults
$options = isset($_options) ? $_options : array(
    'logging_enabled' => 1,
    'max_log_entries' => 1000,
    'log_retention_days' => 30,
    'stats_period_days' => 30,
    'notify_admin' => 0,
    'notify_email' => get_option('admin_email', ''),
    'debug_mode' => 0,
    'auto_cleanup' => 1
);

// Settings sections and fields
$settings_sections = array(
    'logging' => array(
        'title' => __('Logging Settings', 'wp-oauth-remote-logout'),
        'icon' => 'dashicons-list-view',
        'description' => __('Configure how remote logout events are logged and stored.', 'wp-oauth-remote-logout'),
        'fields' => array(
            'logging_enabled' => array(
                'id' => 'wo_logging_enabled',
                'label' => __('Enable Logging', 'wp-oauth-remote-logout'),
                'type' => 'toggle',
                'description' => __('Log remote logout events for debugging and security monitoring.', 'wp-oauth-remote-logout'),
                'value' => $options['logging_enabled'],
            ),
            'max_log_entries' => array(
                'id' => 'wo_max_log_entries',
                'label' => __('Maximum Log Entries', 'wp-oauth-remote-logout'),
                'type' => 'number',
                'description' => __('Maximum number of log entries to keep.', 'wp-oauth-remote-logout'),
                'value' => $options['max_log_entries'],
                'min' => 100,
                'max' => 10000,
                'step' => 100,
                'tooltip' => __('Sets the maximum limit of stored log entries. When this limit is reached, older logs will be removed.', 'wp-oauth-remote-logout'),
            ),
            'log_retention_days' => array(
                'id' => 'wo_log_retention_days',
                'label' => __('Log Retention Period', 'wp-oauth-remote-logout'),
                'type' => 'number',
                'description' => __('Number of days to keep logs.', 'wp-oauth-remote-logout'),
                'value' => $options['log_retention_days'],
                'min' => 1,
                'max' => 365,
                'step' => 1,
                'tooltip' => __('Logs older than this number of days will be automatically removed.', 'wp-oauth-remote-logout'),
            ),
            'auto_cleanup' => array(
                'id' => 'wo_auto_cleanup',
                'label' => __('Auto Cleanup', 'wp-oauth-remote-logout'),
                'type' => 'toggle',
                'description' => __('Automatically clean up logs based on retention settings.', 'wp-oauth-remote-logout'),
                'value' => $options['auto_cleanup'],
                'tooltip' => __('When enabled, old logs will be automatically removed based on your retention settings.', 'wp-oauth-remote-logout'),
            ),
        ),
    ),
    'statistics' => array(
        'title' => __('Statistics Settings', 'wp-oauth-remote-logout'),
        'icon' => 'dashicons-chart-bar',
        'description' => __('Configure statistics display options.', 'wp-oauth-remote-logout'),
        'fields' => array(
            'stats_period_days' => array(
                'id' => 'wo_stats_period_days',
                'label' => __('Statistics Period', 'wp-oauth-remote-logout'),
                'type' => 'number',
                'description' => __('Number of days to include in statistics displays.', 'wp-oauth-remote-logout'),
                'value' => $options['stats_period_days'],
                'min' => 1,
                'max' => 365,
                'step' => 1,
                'tooltip' => __('This setting controls how many days of data are included in the statistics displays.', 'wp-oauth-remote-logout'),
            ),
        ),
    ),
    'notifications' => array(
        'title' => __('Notification Settings', 'wp-oauth-remote-logout'),
        'icon' => 'dashicons-email',
        'description' => __('Configure email notifications for logout events.', 'wp-oauth-remote-logout'),
        'fields' => array(
            'notify_admin' => array(
                'id' => 'wo_notify_admin',
                'label' => __('Email Notifications', 'wp-oauth-remote-logout'),
                'type' => 'toggle',
                'description' => __('Send email notifications for important logout events.', 'wp-oauth-remote-logout'),
                'value' => $options['notify_admin'],
                'tooltip' => __('When enabled, the administrator will receive email notifications for certain logout events.', 'wp-oauth-remote-logout'),
            ),
            'notify_email' => array(
                'id' => 'wo_notify_email',
                'label' => __('Notification Email', 'wp-oauth-remote-logout'),
                'type' => 'email',
                'description' => __('Email address to receive notifications.', 'wp-oauth-remote-logout'),
                'value' => $options['notify_email'],
                'placeholder' => get_option('admin_email'),
                'depends_on' => 'wo_notify_admin',
            ),
        ),
    ),
    'advanced' => array(
        'title' => __('Advanced Settings', 'wp-oauth-remote-logout'),
        'icon' => 'dashicons-admin-tools',
        'description' => __('Advanced configuration options for troubleshooting.', 'wp-oauth-remote-logout'),
        'fields' => array(
            'debug_mode' => array(
                'id' => 'wo_debug_mode',
                'label' => __('Debug Mode', 'wp-oauth-remote-logout'),
                'type' => 'toggle',
                'description' => __('Enable additional debug logging.', 'wp-oauth-remote-logout'),
                'value' => $options['debug_mode'],
                'tooltip' => __('When enabled, additional debug information will be written to the WordPress debug log. Use only when troubleshooting issues.', 'wp-oauth-remote-logout'),
            ),
        ),
    ),
);

// Get potential saved status from URL
$settings_updated = isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true';

// Custom function to render a field based on its type
function render_settings_field($field)
{
    $required = isset($field['required']) && $field['required'] ? 'required' : '';
    $depends_on = isset($field['depends_on']) ? 'data-depends-on="' . esc_attr($field['depends_on']) . '"' : '';
    $depends_class = isset($field['depends_on']) ? 'dependent-field' : '';
    $tooltip = isset($field['tooltip']) ? $field['tooltip'] : '';
    $tooltip_html = !empty($tooltip) ? '<span class="dashicons dashicons-info-outline tooltip-icon" title="' . esc_attr($tooltip) . '"></span>' : '';

    switch ($field['type']) {
        case 'toggle':
?>
            <div class="wo-toggle-field">
                <label class="wo-toggle">
                    <input type="checkbox" name="<?php echo esc_attr($field['id']); ?>" id="<?php echo esc_attr($field['id']); ?>"
                        value="1" <?php checked(1, $field['value']); ?> <?php echo $required; ?> <?php echo $depends_on; ?>
                        class="<?php echo $depends_class; ?>">
                    <span class="wo-toggle-slider"></span>
                </label>
                <span class="wo-toggle-label"><?php echo esc_html($field['description']); ?></span>
                <?php echo $tooltip_html; ?>
            </div>
        <?php
            break;

        case 'checkbox':
        ?>
            <div class="wo-checkbox-field">
                <input type="checkbox" name="<?php echo esc_attr($field['id']); ?>" id="<?php echo esc_attr($field['id']); ?>"
                    value="1" <?php checked(1, $field['value']); ?> <?php echo $required; ?> <?php echo $depends_on; ?>
                    class="<?php echo $depends_class; ?>">
                <label for="<?php echo esc_attr($field['id']); ?>"><?php echo esc_html($field['description']); ?></label>
                <?php echo $tooltip_html; ?>
            </div>
        <?php
            break;

        case 'number':
        ?>
            <div class="wo-number-field">
                <input type="number" name="<?php echo esc_attr($field['id']); ?>" id="<?php echo esc_attr($field['id']); ?>"
                    value="<?php echo esc_attr($field['value']); ?>" <?php echo $required; ?> <?php echo $depends_on; ?>
                    min="<?php echo isset($field['min']) ? esc_attr($field['min']) : ''; ?>"
                    max="<?php echo isset($field['max']) ? esc_attr($field['max']) : ''; ?>"
                    step="<?php echo isset($field['step']) ? esc_attr($field['step']) : '1'; ?>"
                    class="regular-text <?php echo $depends_class; ?>">
                <p class="wo-field-description"><?php echo esc_html($field['description']); ?><?php echo $tooltip_html; ?></p>
            </div>
        <?php
            break;

        case 'email':
        ?>
            <div class="wo-email-field">
                <input type="email" name="<?php echo esc_attr($field['id']); ?>" id="<?php echo esc_attr($field['id']); ?>"
                    value="<?php echo esc_attr($field['value']); ?>" <?php echo $required; ?> <?php echo $depends_on; ?>
                    placeholder="<?php echo isset($field['placeholder']) ? esc_attr($field['placeholder']) : ''; ?>"
                    class="regular-text <?php echo $depends_class; ?>">
                <p class="wo-field-description"><?php echo esc_html($field['description']); ?><?php echo $tooltip_html; ?></p>
            </div>
        <?php
            break;

        case 'text':
        default:
        ?>
            <div class="wo-text-field">
                <input type="text" name="<?php echo esc_attr($field['id']); ?>" id="<?php echo esc_attr($field['id']); ?>"
                    value="<?php echo esc_attr($field['value']); ?>" <?php echo $required; ?> <?php echo $depends_on; ?>
                    placeholder="<?php echo isset($field['placeholder']) ? esc_attr($field['placeholder']) : ''; ?>"
                    class="regular-text <?php echo $depends_class; ?>">
                <p class="wo-field-description"><?php echo esc_html($field['description']); ?><?php echo $tooltip_html; ?></p>
            </div>
<?php
            break;
    }
}
?>

<?php if ($settings_updated): ?>
    <div class="notice notice-success is-dismissible">
        <p><?php _e('Settings saved successfully.', 'wp-oauth-remote-logout'); ?></p>
    </div>
<?php endif; ?>

<div class="wo-settings-header">
    <div class="wo-settings-intro">
        <h2 class="wo-settings-title"><?php _e('Remote Logout Settings', 'wp-oauth-remote-logout'); ?></h2>
        <p class="wo-settings-description">
            <?php _e('Configure how the remote logout extension works. These settings control logging, statistics, and notifications.', 'wp-oauth-remote-logout'); ?>
        </p>
    </div>
</div>

<div class="wo-settings-container">
    <div class="wo-settings-sections">
        <!-- Side navigation -->
        <div class="wo-settings-nav">
            <ul class="wo-settings-tabs">
                <?php foreach ($settings_sections as $section_id => $section): ?>
                    <li>
                        <a href="#section-<?php echo esc_attr($section_id); ?>" class="wo-section-link <?php echo ($section_id === 'logging') ? 'active' : ''; ?>">
                            <span class="dashicons <?php echo esc_attr($section['icon']); ?>"></span>
                            <?php echo esc_html($section['title']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="wo-settings-save-container">
                <button type="submit" form="wo-settings-form" class="button button-primary button-large" id="wo-save-settings">
                    <?php _e('Save Settings', 'wp-oauth-remote-logout'); ?>
                </button>
            </div>
        </div>

        <!-- Main settings content -->
        <div class="wo-settings-main">
            <form method="post" id="wo-settings-form" class="wo-settings-form">
                <?php wp_nonce_field('wo-remote-logout-admin', 'nonce'); ?>
                <input type="hidden" name="action" value="wo_save_settings">

                <?php foreach ($settings_sections as $section_id => $section): ?>
                    <div class="wo-settings-section <?php echo ($section_id === 'logging') ? 'active' : ''; ?>" id="section-<?php echo esc_attr($section_id); ?>">
                        <div class="wo-section-header">
                            <h3 class="wo-section-title">
                                <span class="dashicons <?php echo esc_attr($section['icon']); ?>"></span>
                                <?php echo esc_html($section['title']); ?>
                            </h3>
                            <?php if (!empty($section['description'])): ?>
                                <p class="wo-section-description">
                                    <?php echo esc_html($section['description']); ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <div class="wo-section-fields">
                            <?php foreach ($section['fields'] as $field_id => $field): ?>
                                <div class="wo-field-row <?php echo isset($field['depends_on']) ? 'dependent-container' : ''; ?>">
                                    <div class="wo-field-label">
                                        <label for="<?php echo esc_attr($field['id']); ?>">
                                            <?php echo esc_html($field['label']); ?>
                                        </label>
                                    </div>
                                    <div class="wo-field-input">
                                        <?php render_settings_field($field); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div id="wo-save-status" class="wo-save-status"></div>
            </form>
        </div>
    </div>
</div>

<div class="wo-maintenance-section">
    <div class="wo-maintenance-header">
        <h2>
            <span class="dashicons dashicons-admin-generic"></span>
            <?php _e('Maintenance', 'wp-oauth-remote-logout'); ?>
        </h2>
        <p class="wo-maintenance-description">
            <?php _e('These tools help you manage log data and troubleshoot issues with the logging system.', 'wp-oauth-remote-logout'); ?>
        </p>
    </div>

    <div class="wo-maintenance-tools">
        <div class="wo-maintenance-tool">
            <div class="wo-tool-icon">
                <span class="dashicons dashicons-trash"></span>
            </div>
            <div class="wo-tool-content">
                <h3><?php _e('Clear Logs', 'wp-oauth-remote-logout'); ?></h3>
                <p>
                    <?php _e('Permanently delete all stored log entries. This action cannot be undone.', 'wp-oauth-remote-logout'); ?>
                </p>
                <form method="post" class="wo-clear-logs-form">
                    <?php wp_nonce_field('wo-remote-logout-admin', 'nonce'); ?>
                    <input type="hidden" name="action" value="wo_clear_logs">
                    <button type="submit" class="button wo-clear-logs-button" data-confirm="<?php esc_attr_e('Are you sure you want to clear all logs? This action cannot be undone.', 'wp-oauth-remote-logout'); ?>">
                        <?php _e('Clear All Logs', 'wp-oauth-remote-logout'); ?>
                    </button>
                </form>
            </div>
        </div>

        <div class="wo-maintenance-tool">
            <div class="wo-tool-icon">
                <span class="dashicons dashicons-database-view"></span>
            </div>
            <div class="wo-tool-content">
                <h3><?php _e('Repair Log Table', 'wp-oauth-remote-logout'); ?></h3>
                <p>
                    <?php _e('Attempt to repair the log database table if it\'s corrupted or has issues.', 'wp-oauth-remote-logout'); ?>
                </p>
                <form method="post" class="wo-repair-table-form">
                    <?php wp_nonce_field('wo-remote-logout-admin', 'nonce'); ?>
                    <input type="hidden" name="action" value="wo_repair_table">
                    <button type="submit" class="button wo-repair-table-button">
                        <?php _e('Repair Log Table', 'wp-oauth-remote-logout'); ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    /* Enhanced Settings UI Styles */
    .wo-settings-header {
        margin-bottom: 25px;
    }

    .wo-settings-intro {
        background: #fff;
        border-left: 4px solid #2271b1;
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
        padding: 20px;
        margin-bottom: 20px;
    }

    .wo-settings-title {
        margin-top: 0;
        margin-bottom: 10px;
        font-size: 24px;
        font-weight: 500;
        color: #1d2327;
    }

    .wo-settings-description {
        font-size: 14px;
        margin-top: 0;
        margin-bottom: 5px;
        color: #646970;
        line-height: 1.6;
    }

    .wo-settings-container {
        display: flex;
        margin-bottom: 30px;
    }

    .wo-settings-sections {
        display: flex;
        width: 100%;
        gap: 20px;
        background: #fff;
        border: 1px solid #e5e5e5;
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
    }

    .wo-settings-nav {
        width: 220px;
        border-right: 1px solid #f0f0f1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        background: #f9f9f9;
    }

    .wo-settings-tabs {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .wo-settings-tabs li {
        margin: 0;
    }

    .wo-section-link {
        display: block;
        padding: 15px 12px;
        text-decoration: none;
        color: #333;
        border-left: 4px solid transparent;
        transition: all 0.2s ease;
    }

    .wo-section-link:hover {
        background: #f0f0f1;
        color: #2271b1;
    }

    .wo-section-link.active {
        border-left-color: #2271b1;
        background: #fff;
        color: #2271b1;
        font-weight: 500;
    }

    .wo-section-link .dashicons {
        margin-right: 8px;
        vertical-align: text-bottom;
        opacity: 0.8;
    }

    .wo-settings-main {
        flex: 1;
        padding: 20px;
    }

    .wo-settings-section {
        display: none;
    }

    .wo-settings-section.active {
        display: block;
    }

    .wo-section-header {
        margin-bottom: 20px;
    }

    .wo-section-title {
        margin-top: 0;
        padding-bottom: 12px;
        margin-bottom: 12px;
        border-bottom: 1px solid #eee;
        font-size: 18px;
        display: flex;
        align-items: center;
    }

    .wo-section-title .dashicons {
        margin-right: 8px;
        color: #2271b1;
    }

    .wo-section-description {
        margin-top: 0;
        margin-bottom: 15px;
        font-style: italic;
        color: #646970;
        font-size: 13px;
    }

    .wo-section-fields {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .wo-field-row {
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding-bottom: 15px;
        border-bottom: 1px solid #f5f5f5;
    }

    .wo-field-row:last-child {
        border-bottom: none;
    }

    .wo-field-label label {
        display: block;
        font-weight: 500;
        margin-bottom: 3px;
    }

    .wo-field-input {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .wo-field-description {
        font-size: 13px;
        color: #646970;
        margin: 5px 0 0;
    }

    /* Toggle switch */
    .wo-toggle-field {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .wo-toggle {
        position: relative;
        display: inline-block;
        width: 40px;
        height: 22px;
    }

    .wo-toggle input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .wo-toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 22px;
    }

    .wo-toggle-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 2px;
        bottom: 2px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    input:checked+.wo-toggle-slider {
        background-color: #2271b1;
    }

    input:focus+.wo-toggle-slider {
        box-shadow: 0 0 1px #2271b1;
    }

    input:checked+.wo-toggle-slider:before {
        transform: translateX(18px);
    }

    .wo-toggle-label {
        font-size: 13px;
        color: #50575e;
    }

    /* Save button container */
    .wo-settings-save-container {
        padding: 15px;
        border-top: 1px solid #f0f0f1;
        margin-top: auto;
    }

    /* Maintenance section */
    .wo-maintenance-section {
        background: #fff;
        border: 1px solid #e5e5e5;
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
        padding: 20px;
        margin-top: 30px;
    }

    .wo-maintenance-header {
        margin-bottom: 20px;
    }

    .wo-maintenance-header h2 {
        display: flex;
        align-items: center;
        margin-top: 0;
        font-size: 18px;
    }

    .wo-maintenance-header h2 .dashicons {
        margin-right: 8px;
        color: #2271b1;
    }

    .wo-maintenance-tools {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }

    .wo-maintenance-tool {
        flex: 1;
        min-width: 250px;
        display: flex;
        gap: 15px;
        background: #f9f9f9;
        border: 1px solid #e5e5e5;
        border-radius: 4px;
        padding: 20px;
    }

    .wo-tool-icon {
        display: flex;
        justify-content: center;
        align-items: flex-start;
        padding-top: 5px;
    }

    .wo-tool-icon .dashicons {
        font-size: 24px;
        color: #2271b1;
    }

    .wo-tool-content {
        flex: 1;
    }

    .wo-tool-content h3 {
        margin-top: 0;
        margin-bottom: 10px;
        font-size: 16px;
        color: #1d2327;
    }

    .wo-tool-content p {
        margin-top: 0;
        margin-bottom: 15px;
        font-size: 13px;
        color: #646970;
    }

    /* Tooltip and info icons */
    .tooltip-icon {
        color: #72aee6;
        vertical-align: middle;
        cursor: help;
        margin-left: 5px;
    }

    /* Dependent field logic */
    .dependent-field {
        opacity: 0.5;
        pointer-events: none;
    }

    .dependent-field.active {
        opacity: 1;
        pointer-events: auto;
    }

    /* Responsive layout */
    @media screen and (max-width: 782px) {
        .wo-settings-sections {
            flex-direction: column;
            gap: 0;
        }

        .wo-settings-nav {
            width: 100%;
            border-right: none;
            border-bottom: 1px solid #f0f0f1;
        }

        .wo-settings-tabs {
            display: flex;
            flex-wrap: wrap;
        }

        .wo-section-link {
            padding: 10px;
            border-left: none;
            border-bottom: 3px solid transparent;
        }

        .wo-section-link.active {
            border-left-color: transparent;
            border-bottom-color: #2271b1;
        }

        .wo-settings-save-container {
            border-top: none;
            padding: 10px 15px;
        }

        .wo-field-row {
            flex-direction: column;
            gap: 5px;
        }

        .wo-maintenance-tools {
            flex-direction: column;
        }

        .wo-maintenance-tool {
            width: 100%;
        }
    }
</style>

<script>
    jQuery(document).ready(function($) {
        // Initialize tooltips
        if (typeof $.fn.tipTip !== 'undefined') {
            $('.tooltip-icon').tipTip({
                attribute: 'title',
                fadeIn: 50,
                fadeOut: 50,
                delay: 200
            });
        } else {
            // Fallback for tooltip if TipTip is not available
            $('.tooltip-icon').each(function() {
                $(this).attr('title', $(this).attr('title'));
            });
        }

        // Section navigation
        $('.wo-section-link').on('click', function(e) {
            e.preventDefault();

            var targetId = $(this).attr('href');

            // Update active class on links
            $('.wo-section-link').removeClass('active');
            $(this).addClass('active');

            // Hide all sections and show target
            $('.wo-settings-section').removeClass('active').hide();
            $(targetId).addClass('active').show();

            // Smooth scroll to top of section on mobile
            if (window.innerWidth < 783) {
                $('html, body').animate({
                    scrollTop: $(targetId).offset().top - 50
                }, 300);
            }
        });

        // Handle dependencies between fields
        function handleDependentFields() {
            $('[data-depends-on]').each(function() {
                var $this = $(this);
                var dependsOn = $this.data('depends-on');
                var $controller = $('#' + dependsOn);
                var $container = $this.closest('.dependent-container');

                function toggleField() {
                    if ($controller.is(':checked')) {
                        $this.addClass('active').prop('disabled', false);
                        $container.removeClass('hidden-dependent');
                    } else {
                        $this.removeClass('active').prop('disabled', true);
                        $container.addClass('hidden-dependent');
                    }
                }

                $controller.on('change', toggleField);
                toggleField(); // Initial state
            });
        }

        handleDependentFields();

        // Confirm before clearing logs
        $('.wo-clear-logs-button').on('click', function(e) {
            if (!confirm($(this).data('confirm'))) {
                e.preventDefault();
            }
        });

        // Settings form submission
        $('#wo-settings-form').on('submit', function(e) {
            e.preventDefault();

            var $form = $(this);
            var $submitButton = $('#wo-save-settings');
            var $status = $('#wo-save-status');

            // Show loading state
            $submitButton.prop('disabled', true).text('Saving...');

            // Get form data
            var formData = new FormData();

            // Add all text, number, and email inputs
            $form.find('input[type="text"], input[type="number"], input[type="email"], input[type="hidden"]').each(function() {
                var $input = $(this);
                if (!$input.is(':disabled')) {
                    formData.append($input.attr('name'), $input.val());
                }
            });

            // Add checkboxes with their correct values
            $form.find('input[type="checkbox"]').each(function() {
                var $checkbox = $(this);
                if ($checkbox.prop('checked')) {
                    formData.append($checkbox.attr('name'), '1');
                }
            });

            // Add nonce
            formData.append('nonce', woRemoteLogout.nonce);
            formData.append('action', 'wo_save_settings');

            // Send AJAX request
            $.ajax({
                url: woRemoteLogout.ajaxUrl,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $submitButton.prop('disabled', false).text('Save Settings');

                    if (response.success) {
                        $status.html('<div class="notice notice-success inline"><p>Settings saved successfully.</p></div>').show();

                        // Refresh checkbox values to match what was saved
                        $form.find('input[type="checkbox"]').each(function() {
                            var $checkbox = $(this);
                            var formVal = formData.get($checkbox.attr('name'));
                            $checkbox.prop('checked', formVal == '1');
                        });

                        // Reapply dependent fields logic
                        handleDependentFields();
                    } else {
                        var errorMsg = response.data || 'Unknown error occurred.';
                        $status.html('<div class="notice notice-error inline"><p>Error: ' + errorMsg + '</p></div>').show();
                    }

                    // Highlight the status message
                    $status.css('opacity', 0).animate({
                        opacity: 1
                    }, 300);

                    // Hide status after 3 seconds
                    setTimeout(function() {
                        $status.fadeOut(300, function() {
                            $(this).empty();
                        });
                    }, 3000);
                },
                error: function(xhr, status, error) {
                    console.error('Error saving settings:', xhr, status, error);
                    $submitButton.prop('disabled', false).text('Save Settings');
                    $status.html('<div class="notice notice-error inline"><p>Error saving settings. Please try again.</p></div>').show();
                }
            });
        });

        // Maintenance forms submission
        $('.wo-clear-logs-form, .wo-repair-table-form').on('submit', function(e) {
            e.preventDefault();

            var $form = $(this);
            var $button = $form.find('button');
            var originalText = $button.text();

            // Show loading state
            $button.prop('disabled', true).text('Processing...');

            // Convert form data to FormData
            var formData = new FormData(this);

            // Send AJAX request
            $.ajax({
                url: woRemoteLogout.ajaxUrl,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $button.prop('disabled', false).text(originalText);

                    if (response.success) {
                        $('<div class="notice notice-success is-dismissible"><p>' + response.data + '</p></div>')
                            .insertBefore($form)
                            .delay(3000)
                            .fadeOut(300, function() {
                                $(this).remove();
                            });
                    } else {
                        $('<div class="notice notice-error is-dismissible"><p>Error: ' + (response.data || 'Unknown error') + '</p></div>')
                            .insertBefore($form);
                    }
                },
                error: function(xhr, status, error) {
                    $button.prop('disabled', false).text(originalText);
                    $('<div class="notice notice-error is-dismissible"><p>Error processing request. Please try again.</p></div>')
                        .insertBefore($form);
                }
            });
        });
    });
</script>