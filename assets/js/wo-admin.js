/**
 * WP OAuth Server - Remote Logout Extension Admin JS
 *
 * Shared JavaScript functionality for the admin interface
 */

jQuery(document).ready(function ($) {
  /**
   * Copy to clipboard functionality
   * Used across all tabs for copy buttons
   */
  function setupCopyButtons() {
    // Helper function to copy text to clipboard
    function copyToClipboard(text, $button) {
      // Use newer clipboard API if available
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard
          .writeText(text)
          .then(() => showCopyFeedback($button))
          .catch(() => legacyCopy(text, $button)); // Fallback if permission denied
      } else {
        legacyCopy(text, $button);
      }
    }

    // Legacy method for copying
    function legacyCopy(text, $button) {
      var $temp = $('<textarea>');
      $('body').append($temp);
      $temp.val(text).select();
      var success = document.execCommand('copy');
      $temp.remove();

      if (success) {
        showCopyFeedback($button);
      }
    }

    // Show visual feedback that content was copied
    function showCopyFeedback($button) {
      // Button feedback
      var originalIcon = $button.find('.dashicons').attr('class');
      $button.find('.dashicons').removeClass().addClass('dashicons dashicons-yes-alt');
      $button.css('color', '#46b450');

      // Create and show tooltip
      var buttonText = $button.data('success-text') || 'Copied!';
      var $tooltip = $('<div class="wo-copy-tooltip">' + buttonText + '</div>');
      $('body').append($tooltip);

      var buttonOffset = $button.offset();
      $tooltip
        .css({
          position: 'absolute',
          zIndex: 9999,
          left: buttonOffset.left - $tooltip.width() / 2 + $button.width() / 2,
          top: buttonOffset.top - 30,
          padding: '4px 10px',
          backgroundColor: 'rgba(0, 0, 0, 0.75)',
          color: '#fff',
          fontSize: '12px',
          fontWeight: '500',
          borderRadius: '3px',
          boxShadow: '0 2px 5px rgba(0,0,0,0.2)',
          pointerEvents: 'none',
          opacity: 0,
          textAlign: 'center',
        })
        .animate(
          {
            opacity: 1,
            top: buttonOffset.top - 35,
          },
          200
        )
        .delay(1500)
        .animate(
          {
            opacity: 0,
            top: buttonOffset.top - 30,
          },
          200,
          function () {
            $(this).remove();

            // Reset button
            $button.css('color', '');
            $button.find('.dashicons').removeClass().addClass(originalIcon);
          }
        );
    }

    // Attach click handlers to all copy buttons
    $(document).on('click', '.copy-endpoint, .copy-code', function (e) {
      e.preventDefault();
      var $button = $(this);
      var textToCopy = $button.data('endpoint') || $button.data('code');

      if (textToCopy) {
        copyToClipboard(textToCopy, $button);
      }
    });
  }

  /**
   * Toggle functionality for details icons
   * Used in logs tab
   */
  function setupToggleButtons() {
    // Log debugging information to help identify issues
    console.log('Setting up toggle details buttons...');
    console.log('Found toggle buttons:', $('.toggle-details-icon-btn').length);

    // Add tooltips dynamically from title attributes
    $('.toggle-details-icon-btn').each(function () {
      var $btn = $(this);
      var $icon = $btn.find('.dashicons');
      var title = $icon.attr('title') || 'View Details';

      // Add tooltip data attribute if not already set
      if (!$btn.attr('data-tooltip')) {
        $btn.attr('data-tooltip', title);
      }

      console.log('Processed button:', $btn.html(), 'with target:', $btn.data('target'));
    });

    // Handle click on toggle details icons
    $(document).on('click', '.toggle-details-icon-btn', function (e) {
      e.preventDefault();
      e.stopPropagation();

      console.log('Toggle button clicked!');

      var $button = $(this);
      var $icon = $button.find('.dashicons');
      var targetId = $button.data('target');

      console.log('Target ID:', targetId);

      if (!targetId) {
        console.error('No target ID found for this button');
        return false;
      }

      var $detailsRow = $('#' + targetId);

      if ($detailsRow.length === 0) {
        console.error('Target element not found:', targetId);
        return false;
      }

      console.log('Details row found:', $detailsRow.length);

      // Check if the details are visible
      var isVisible = $detailsRow.is(':visible');
      console.log('Details currently visible:', isVisible);

      // Toggle visibility
      if (isVisible) {
        // Hide details
        console.log('Hiding details');
        $button.removeClass('active');
        $icon.removeClass('dashicons-visibility-off').addClass('dashicons-visibility');
        $icon.attr('title', 'View Details');
        $button.attr('data-tooltip', 'View Details');

        // Animate
        $detailsRow.fadeOut(200, function () {
          $detailsRow.removeClass('visible');
        });
      } else {
        // Show details
        console.log('Showing details');
        $button.addClass('active');
        $icon.removeClass('dashicons-visibility').addClass('dashicons-visibility-off');
        $icon.attr('title', 'Hide Details');
        $button.attr('data-tooltip', 'Hide Details');

        // Close other open details
        $('.toggle-details-icon-btn.active')
          .not($button)
          .each(function () {
            var $otherButton = $(this);
            var $otherIcon = $otherButton.find('.dashicons');
            var otherId = $otherButton.data('target');
            if (otherId !== targetId) {
              $('#' + otherId).fadeOut(200);
              $otherButton.removeClass('active');
              $otherIcon.removeClass('dashicons-visibility-off').addClass('dashicons-visibility');
              $otherIcon.attr('title', 'View Details');
              $otherButton.attr('data-tooltip', 'View Details');
            }
          });

        // Animate
        $detailsRow.fadeIn(200, function () {
          $detailsRow.addClass('visible');
        });
      }

      return false;
    });
  }

  /**
   * Settings form handling
   * Used to save all types of settings
   */
  function setupSettingsFormHandling() {
    // Handle settings form submission
    $('#wo-settings-form').on('submit', function (e) {
      e.preventDefault();

      console.log('Settings form submitted');

      var $form = $(this);
      var $submitButton = $('#wo-save-settings');
      var $status = $('#wo-save-status');

      // Show loading state
      $submitButton.prop('disabled', true).text('Saving...');

      // Get form data
      var formData = new FormData();

      // Add all text, number, and email inputs
      $form
        .find('input[type="text"], input[type="number"], input[type="email"], input[type="hidden"]')
        .each(function () {
          var $input = $(this);
          if (!$input.is(':disabled')) {
            formData.append($input.attr('name'), $input.val());
          }
        });

      // Add checkboxes with their correct values
      $form.find('input[type="checkbox"]').each(function () {
        var $checkbox = $(this);
        if ($checkbox.prop('checked')) {
          formData.append($checkbox.attr('name'), '1');
        } else {
          formData.append($checkbox.attr('name'), '0');
        }
      });

      // Add nonce
      formData.append('nonce', woRemoteLogout.nonce);
      formData.append('action', 'wo_save_settings');

      console.log('Submitting settings form data');

      // Send AJAX request
      $.ajax({
        url: woRemoteLogout.ajaxUrl,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
          $submitButton.prop('disabled', false).text('Save Settings');

          if (response.success) {
            $status
              .html(
                '<div class="notice notice-success inline"><p>Settings saved successfully.</p></div>'
              )
              .show();
          } else {
            var errorMsg = response.data || 'Unknown error occurred.';
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
          setTimeout(function () {
            $status.fadeOut(300, function () {
              $(this).empty();
            });
          }, 3000);
        },
        error: function (xhr, status, error) {
          console.error('Error saving settings:', xhr, status, error);
          $submitButton.prop('disabled', false).text('Save Settings');
          $status
            .html(
              '<div class="notice notice-error inline"><p>Error saving settings. Please try again.</p></div>'
            )
            .show();
        },
      });
    });
  }

  /**
   * Enhance form elements across admin interface
   */
  function enhanceFormElements() {
    // Add hover effects to form buttons
    $('.wo-form-submit .button').hover(
      function () {
        $(this).css('transform', 'translateY(-1px)');
      },
      function () {
        $(this).css('transform', '');
      }
    );

    // Focus effects for input fields
    $('.wo-text-input, .wo-select')
      .focus(function () {
        $(this).css('border-color', '#2271b1');
      })
      .blur(function () {
        $(this).css('border-color', '');
      });
  }

  // Initialize all UI enhancements
  function initUIEnhancements() {
    setupCopyButtons();
    setupToggleButtons();
    setupSettingsFormHandling();
    enhanceFormElements();

    // Add shared animations for the admin interface
    $(
      '<style>' +
        '@keyframes rotation { from { transform: rotate(0deg); } to { transform: rotate(359deg); } }' +
        '.wo-processing .dashicons { animation: rotation 2s infinite linear; }' +
        '.wo-card { transition: box-shadow 0.2s; }' +
        '.wo-card:hover { box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08); }' +
        '</style>'
    ).appendTo('head');
  }

  // Call initialization
  initUIEnhancements();
});
