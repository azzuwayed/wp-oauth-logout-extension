/**
 * WP OAuth Server - Remote Logout Extension Admin Scripts
 */
jQuery(document).ready(function ($) {
  'use strict';

  console.log('WP OAuth Server - Remote Logout Extension Admin Scripts Loaded');

  // Tab switching functionality
  $('.nav-tab-wrapper .nav-tab').on('click', function (e) {
    e.preventDefault();

    var tabId = $(this).attr('href');
    var tab = $(this).data('tab');

    // Update active tab
    $('.nav-tab-wrapper .nav-tab').removeClass('nav-tab-active');
    $(this).addClass('nav-tab-active');

    // Show the selected tab content, hide others
    $('.tab-content').hide();
    $(tabId).show();

    // Update URL without reloading
    if (history.pushState) {
      var newUrl =
        window.location.protocol +
        '//' +
        window.location.host +
        window.location.pathname +
        '?page=wo-remote-logout&tab=' +
        tab;
      window.history.pushState({ path: newUrl }, '', newUrl);
    }
  });

  // Toggle log details
  $('.toggle-details').on('click', function () {
    var $button = $(this);
    var $details = $button.next('.log-details');

    console.log('Toggle details clicked, found details:', $details.length);

    $details.slideToggle(200, function () {
      if ($details.is(':visible')) {
        $button.text('Hide Details');
      } else {
        $button.text('View Details');
      }
    });
  });

  // Handle settings form submission - now handled in wo-admin.js
  // Just for backward compatibility, but not actively used
  $('form:has(input[name="wo_stats_period_days"])').on('submit', function (e) {
    console.log('Old settings form handler called - using new handler in wo-admin.js instead');
    e.preventDefault();
    // This form is now handled by setupSettingsFormHandling() in wo-admin.js
  });

  // Default tab initialization
  var activeTab = $('.nav-tab-active').attr('href');
  if (activeTab) {
    $('.tab-content').hide();
    $(activeTab).show();
  }

  console.log('Found ' + $('.toggle-details').length + ' toggle buttons');
  console.log('Found ' + $('.tab-content').length + ' tab content sections');

  // Make tables responsive
  $('.widefat').wrap('<div class="table-responsive"></div>');

  /**
   * Handle dismissible notices
   */
  $(document).on('click', '.notice-dismiss', function () {
    $(this)
      .closest('.notice')
      .fadeOut(300, function () {
        $(this).remove();
      });
  });
});
