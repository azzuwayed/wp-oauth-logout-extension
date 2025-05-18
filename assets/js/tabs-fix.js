/**
 * Direct tab fix to ensure tabs work
 */
(function () {
  // Run as soon as DOM is ready
  document.addEventListener('DOMContentLoaded', function () {
    console.log('DOM loaded, running tabs-fix.js');

    // Debug all tab content IDs
    var allTabContents = document.querySelectorAll('.tab-content');
    console.log('*** All tab content elements:');
    allTabContents.forEach(function (tab) {
      console.log('Tab element:', tab.id, 'Display:', window.getComputedStyle(tab).display);
    });

    // Get all tab links
    var tabLinks = document.querySelectorAll('.nav-tab-wrapper .nav-tab');
    console.log('Found tab links:', tabLinks.length);

    // Debug all tab links
    tabLinks.forEach(function (link) {
      console.log(
        'Tab link href:',
        link.getAttribute('href'),
        'data-tab:',
        link.getAttribute('data-tab')
      );
    });

    // Add click handlers
    tabLinks.forEach(function (tabLink) {
      tabLink.addEventListener('click', function (e) {
        e.preventDefault();

        // Get tab ID from href (remove # if present)
        var href = this.getAttribute('href');
        var tabId = href.replace(/^#/, ''); // Remove leading # if present
        console.log('Tab clicked:', href, 'Tab ID:', tabId);

        // Update active tab styling
        tabLinks.forEach(function (link) {
          link.classList.remove('nav-tab-active');
        });
        this.classList.add('nav-tab-active');

        // Hide all tab content completely
        var tabContents = document.querySelectorAll('.tab-content');
        console.log('Found tab contents:', tabContents.length);
        tabContents.forEach(function (content) {
          content.style.display = 'none';
          content.classList.remove('active', 'show');
        });

        // Show selected tab content
        var selectedTab = document.getElementById(tabId);
        if (selectedTab) {
          console.log('Found selected tab:', tabId);
          selectedTab.style.display = 'block';
          selectedTab.classList.add('active', 'show');
          // Force repaint to ensure visibility
          void selectedTab.offsetHeight;
        } else {
          console.error('Could not find tab with ID:', tabId);
          // Fallback - try using the href directly
          var fallbackTab = document.querySelector(href);
          if (fallbackTab) {
            console.log('Found tab using fallback selector');
            fallbackTab.style.display = 'block';
          }
        }
      });
    });

    // Log all tab contents for debugging
    var allTabs = document.querySelectorAll('.tab-content');
    allTabs.forEach(function (tab) {
      console.log('Tab content ID:', tab.id, 'Display:', window.getComputedStyle(tab).display);
    });

    // Initialize default tab if needed
    var activeTab = document.querySelector('.nav-tab-active');
    if (activeTab) {
      // Simulate a click on the active tab to ensure content is shown
      console.log('Simulating click on active tab');
      activeTab.click();
    }
  });
})();
