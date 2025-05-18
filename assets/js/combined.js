/**
 * WP OAuth Server - Remote Logout Extension
 * Combined JS file for better performance
 */
(function(w, d, $) {
  'use strict';

  // Run when DOM is ready
  $(function() {
    // Global cached DOM elements
    const $navTabs = $('.nav-tab-wrapper .nav-tab');
    const $tabContents = $('.tab-content');

    /**
     * Add CSS for transitions and animations
     */
    function addStyles() {
      $('<style>')
        .text(`
          /* Animation keyframes */
          @keyframes rotation { 
            from { transform: rotate(0deg); } 
            to { transform: rotate(359deg); } 
          }
          @keyframes copySuccess {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
          }
          
          /* Tooltips */
          .wo-copy-tooltip {
            position: absolute;
            z-index: 9999;
            padding: 4px 10px;
            background-color: rgba(0, 0, 0, 0.75);
            color: #fff;
            font-size: 12px;
            font-weight: 500;
            border-radius: 3px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            pointer-events: none;
            text-align: center;
            transition: opacity 0.2s, transform 0.2s;
          }
          
          /* Form elements */
          .wo-form-submit .button {
            transition: transform 0.2s, box-shadow 0.2s;
          }
          .wo-form-submit .button:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
          }
          .wo-text-input, .wo-select {
            transition: border-color 0.2s;
          }
          .wo-text-input:focus, .wo-select:focus {
            border-color: #2271b1;
          }
          
          /* Cards */
          .wo-card {
            transition: box-shadow 0.2s;
          }
          .wo-card:hover {
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
          }
          
          /* Processing indicators */
          .wo-processing .dashicons {
            animation: rotation 2s infinite linear;
          }
        `)
        .appendTo('head');
    }

    /**
     * Setup tab functionality
     */
    function setupTabs() {
      if (!$navTabs.length || !$tabContents.length) return;

      // Tab switching functionality
      $navTabs.on('click', function(e) {
        e.preventDefault();

        const tabId = $(this).attr('href');
        const tab = $(this).data('tab');

        // Update active tab
        $navTabs.removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');

        // Show the selected tab content, hide others
        $tabContents.hide();
        $(tabId).show();

        // Update URL without reloading
        if (history.pushState) {
          const newUrl =
            window.location.protocol +
            '//' +
            window.location.host +
            window.location.pathname +
            '?page=wo-remote-logout&tab=' +
            tab;
          window.history.pushState({ path: newUrl }, '', newUrl);
        }
      });

      // Default tab initialization
      const activeTab = $('.nav-tab-active').attr('href');
      if (activeTab) {
        $tabContents.hide();
        $(activeTab).show();
      }
    }

    /**
     * Setup toggle functionality for log details
     */
    function setupToggleDetails() {
      // Toggle log details buttons
      $('.toggle-details').on('click', function() {
        const $button = $(this);
        const $details = $button.next('.log-details');

        $details.slideToggle(200, function() {
          $button.text($details.is(':visible') ? 'Hide Details' : 'View Details');
        });
      });

      // Icon toggle buttons
      const $toggleButtons = $('.toggle-details-icon-btn');

      // Add tooltips dynamically from title attributes
      $toggleButtons.each(function() {
        const $btn = $(this);
        const $icon = $btn.find('.dashicons');
        const title = $icon.attr('title') || 'View Details';

        // Add tooltip data attribute if not already set
        if (!$btn.attr('data-tooltip')) {
          $btn.attr('data-tooltip', title);
        }
      });

      // Handle click on toggle details icons
      $(document).on('click', '.toggle-details-icon-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const $button = $(this);
        const $icon = $button.find('.dashicons');
        const targetId = $button.data('target');

        if (!targetId) {
          return false;
        }

        const $detailsRow = $('#' + targetId);

        if ($detailsRow.length === 0) {
          return false;
        }

        // Check if the details are visible
        const isVisible = $detailsRow.is(':visible');

        // Toggle visibility
        if (isVisible) {
          // Hide details
          $button.removeClass('active');
          $icon.removeClass('dashicons-visibility-off').addClass('dashicons-visibility');
          $icon.attr('title', 'View Details');
          $button.attr('data-tooltip', 'View Details');

          // Animate
          $detailsRow.fadeOut(200, function() {
            $detailsRow.removeClass('visible');
          });
        } else {
          // Show details
          $button.addClass('active');
          $icon.removeClass('dashicons-visibility').addClass('dashicons-visibility-off');
          $icon.attr('title', 'Hide Details');
          $button.attr('data-tooltip', 'Hide Details');

          // Close other open details
          $('.toggle-details-icon-btn.active')
            .not($button)
            .each(function() {
              const $otherButton = $(this);
              const $otherIcon = $otherButton.find('.dashicons');
              const otherId = $otherButton.data('target');
              if (otherId !== targetId) {
                $('#' + otherId).fadeOut(200);
                $otherButton.removeClass('active');
                $otherIcon.removeClass('dashicons-visibility-off').addClass('dashicons-visibility');
                $otherIcon.attr('title', 'View Details');
                $otherButton.attr('data-tooltip', 'View Details');
              }
            });

          // Animate
          $detailsRow.fadeIn(200, function() {
            $detailsRow.addClass('visible');
          });
        }

        return false;
      });
    }

    /**
     * Setup copy to clipboard functionality
     */
    function setupCopyButtons() {
      // Helper function to copy text to clipboard
      function copyToClipboard(text, $button) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard
            .writeText(text)
            .then(() => showCopyFeedback($button))
            .catch(() => legacyCopy(text, $button));
        } else {
          legacyCopy(text, $button);
        }
      }

      // Legacy method for copying
      function legacyCopy(text, $button) {
        const $temp = $('<textarea>');
        $('body').append($temp);
        $temp.val(text).select();
        const success = document.execCommand('copy');
        $temp.remove();

        if (success) {
          showCopyFeedback($button);
        }
      }

      // Show visual feedback that content was copied
      function showCopyFeedback($button) {
        // Button feedback
        const originalIcon = $button.find('.dashicons').attr('class');
        $button.find('.dashicons').removeClass().addClass('dashicons dashicons-yes-alt');
        $button.css('color', '#46b450');

        // Create and show tooltip
        const buttonText = $button.data('success-text') || 'Copied!';
        const $tooltip = $('<div class="wo-copy-tooltip">' + buttonText + '</div>');
        $('body').append($tooltip);

        const buttonOffset = $button.offset();
        $tooltip.css({
          left: buttonOffset.left - $tooltip.width() / 2 + $button.width() / 2,
          top: buttonOffset.top - 30,
          opacity: 0,
        });
        
        // Use requestAnimationFrame for smoother animation
        requestAnimationFrame(() => {
          $tooltip.css({
            opacity: 1,
            top: buttonOffset.top - 35,
          });
          
          setTimeout(() => {
            $tooltip.css({
              opacity: 0,
              top: buttonOffset.top - 30,
            });
            
            setTimeout(() => {
              $tooltip.remove();
              // Reset button
              $button.css('color', '');
              $button.find('.dashicons').removeClass().addClass(originalIcon);
            }, 200);
          }, 1500);
        });
      }

      // Attach click handlers to all copy buttons
      $(document).on('click', '.copy-endpoint, .copy-code', function(e) {
        e.preventDefault();
        const $button = $(this);
        const textToCopy = $button.data('endpoint') || $button.data('code');

        if (textToCopy) {
          copyToClipboard(textToCopy, $button);
        }
      });
    }

    /**
     * Setup settings form handling
     */
    function setupSettingsFormHandling() {
      // Cache DOM elements
      const $form = $('#wo-settings-form');
      if (!$form.length) return;
      
      const $submitButton = $('#wo-save-settings');
      const $status = $('#wo-save-status');

      // Handle settings form submission
      $form.on('submit', function(e) {
        e.preventDefault();

        // Show loading state
        $submitButton.prop('disabled', true).text('Saving...');

        // Get form data
        const formData = new FormData();

        // Add all text, number, and email inputs
        $form
          .find('input[type="text"], input[type="number"], input[type="email"], input[type="hidden"]')
          .each(function() {
            const $input = $(this);
            if (!$input.is(':disabled')) {
              formData.append($input.attr('name'), $input.val());
            }
          });

        // Add checkboxes with their correct values
        $form.find('input[type="checkbox"]').each(function() {
          const $checkbox = $(this);
          formData.append($checkbox.attr('name'), $checkbox.prop('checked') ? '1' : '0');
        });

        // Add nonce
        if (typeof woRemoteLogout !== 'undefined') {
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
                $status
                  .html(
                    '<div class="notice notice-success inline"><p>Settings saved successfully.</p></div>'
                  )
                  .show();
              } else {
                const errorMsg = response.data || 'Unknown error occurred.';
                $status
                  .html('<div class="notice notice-error inline"><p>Error: ' + errorMsg + '</p></div>')
                  .show();
              }

              // Highlight the status message
              $status.css('opacity', 0).animate(
                {
                  opacity: 1,
                },
                300
              );

              // Hide status after 3 seconds
              setTimeout(function() {
                $status.fadeOut(300, function() {
                  $(this).empty();
                });
              }, 3000);
            },
            error: function() {
              $submitButton.prop('disabled', false).text('Save Settings');
              $status
                .html(
                  '<div class="notice notice-error inline"><p>Error saving settings. Please try again.</p></div>'
                )
                .show();
            },
          });
        }
      });
    }

    /**
     * Setup responsive tables
     */
    function setupResponsiveTables() {
      // Make tables responsive (wrap once on init rather than repeatedly)
      $('.widefat').not('.table-responsive .widefat').wrap('<div class="table-responsive"></div>');
    }

    /**
     * Setup dismissible notices
     */
    function setupDismissibleNotices() {
      $(document).on('click', '.notice-dismiss', function() {
        $(this)
          .closest('.notice')
          .fadeOut(300, function() {
            $(this).remove();
          });
      });
    }

    // Initialize all components
    function init() {
      // Add shared styles first
      addStyles();
      
      // Setup UI components
      setupTabs();
      setupToggleDetails();
      setupCopyButtons();
      setupSettingsFormHandling();
      setupResponsiveTables();
      setupDismissibleNotices();
    }

    // Start initialization
    init();
  });
})(window, document, jQuery); 