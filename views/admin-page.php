<?php

/**
 * Admin Page
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap wo-admin-wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <h2 class="nav-tab-wrapper">
        <?php foreach ($tabs as $tab_id => $tab_name): ?>
            <a href="#tab-<?php echo esc_attr($tab_id); ?>"
                class="nav-tab <?php echo $current_tab === $tab_id ? 'nav-tab-active' : ''; ?>"
                data-tab="<?php echo esc_attr($tab_id); ?>">
                <?php echo esc_html($tab_name); ?>
            </a>
        <?php endforeach; ?>
    </h2>

    <?php foreach ($tabs as $tab_id => $tab_name): ?>
        <div id="tab-<?php echo esc_attr($tab_id); ?>"
            class="tab-content <?php echo $current_tab === $tab_id ? 'active' : ''; ?>"
            style="display: <?php echo $current_tab === $tab_id ? 'block' : 'none'; ?>">

            <?php
            // Include tab content
            try {
                switch ($tab_id) {
                    case 'overview':
                        include_once WO_REMOTE_LOGOUT_PATH . 'views/tabs/overview.php';
                        break;
                    case 'clients':
                        // Extract client variables for the view
                        $_clients = $clients;
                        // Debug client data structure if it's empty
                        if (empty($_clients)) {
                            echo '<div class="notice notice-warning"><p>No clients found or error retrieving clients.</p>';
                            if (current_user_can('manage_options') && WO_REMOTE_LOGOUT_LOG_ENABLED) {
                                echo '<p>Debugging info for admin:</p>';
                                echo '<pre>';
                                echo 'WP OAuth Server Class exists: ' . (class_exists('WO_Server') ? 'Yes' : 'No') . "\n";
                                echo 'Number of clients: ' . count($clients) . "\n";
                                if (!empty($clients) && is_array($clients)) {
                                    echo 'First client: ' . print_r($clients[0], true);
                                }
                                echo '</pre>';
                            }
                            echo '</div>';
                        }
                        include_once WO_REMOTE_LOGOUT_PATH . 'views/tabs/clients.php';
                        break;
                    case 'logs':
                        // Extract logs variables for the view
                        $_logs = $logs;
                        $_total_logs = $total_logs;
                        $_total_pages = $total_pages;
                        $_page = $page;
                        include_once WO_REMOTE_LOGOUT_PATH . 'views/tabs/logs.php';
                        break;
                    case 'stats':
                        // Get client logout statistics for the view
                        $_clients = $clients;
                        $_stats = $stats;
                        $_client_stats = $client_stats;
                        $_options = $options; // Pass options to stats view
                        include_once WO_REMOTE_LOGOUT_PATH . 'views/tabs/stats.php';
                        break;
                    case 'settings':
                        // Extract options for the view
                        $_options = $options;
                        include_once WO_REMOTE_LOGOUT_PATH . 'views/tabs/settings.php';
                        break;
                    case 'test':
                        include_once WO_REMOTE_LOGOUT_PATH . 'views/tabs/test.php';
                        break;
                    case 'test-ui':
                        include_once WO_REMOTE_LOGOUT_PATH . 'views/test-ui.php';
                        break;
                    default:
                        do_action('wo_remote_logout_tab_' . $tab_id);
                        break;
                }
            } catch (Exception $e) {
                echo '<div class="notice notice-error"><p>Error loading tab content: ' . esc_html($e->getMessage()) . '</p></div>';
            }
            ?>

        </div>
    <?php endforeach; ?>
</div>

<script>
    // Immediate fix for tab switching
    (function() {
        // Fix tab clicking - direct approach
        document.addEventListener('DOMContentLoaded', function() {
            // Check all tab links
            var tabLinks = document.querySelectorAll('.nav-tab-wrapper .nav-tab');
            console.log('[Inline script] Found tab links:', tabLinks.length);

            // Direct event listeners
            tabLinks.forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Get tab ID (removing the # prefix)
                    var href = this.getAttribute('href');
                    var targetId = href.replace(/^#/, '');

                    console.log('[Inline script] Tab clicked:', targetId);

                    // Update active classes
                    tabLinks.forEach(function(tab) {
                        tab.classList.remove('nav-tab-active');
                    });
                    this.classList.add('nav-tab-active');

                    // Hide all content
                    var allContents = document.querySelectorAll('.tab-content');
                    allContents.forEach(function(content) {
                        content.style.display = 'none';
                        content.classList.remove('active', 'show');
                    });

                    // Show target content
                    var target = document.getElementById(targetId);
                    if (target) {
                        target.style.display = 'block';
                        target.classList.add('active', 'show');
                        // Force repaint
                        void target.offsetHeight;
                    }

                    // Update URL
                    var tab = this.getAttribute('data-tab');
                    var newUrl = window.location.pathname + '?page=wo-remote-logout&tab=' + tab;
                    history.pushState({}, '', newUrl);
                });
            });
        });
    })();
</script>

<script>
    jQuery(document).ready(function($) {
        // Initialize tabs on page load - do this only if needed
        if (typeof woTabsInitialized === 'undefined' || !woTabsInitialized) {
            var currentTab = '<?php echo esc_js($current_tab); ?>';
            console.log('Initializing from admin-page.php, current tab:', currentTab);

            // Show current tab content (redundant but failsafe)
            $('.tab-content').hide().removeClass('active show');
            $('#tab-' + currentTab).show().addClass('active show');

            // Ensure correct tab styling
            $('.nav-tab').removeClass('nav-tab-active');
            $('.nav-tab[data-tab="' + currentTab + '"]').addClass('nav-tab-active');

            // Set flag to prevent double initialization
            window.woTabsInitialized = true;
        }
    });
</script>