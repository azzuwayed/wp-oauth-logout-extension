# WP OAuth Server - Remote Logout Extension

A WordPress plugin that adds a Remote Logout Endpoint to the WP OAuth Server plugin with zero configuration.

## Features

- Remote Logout REST API endpoint for OAuth clients
- Activity logging of all logout operations
- Detailed statistics and analytics
- Debugging and testing tools
- Modern and responsive admin interface

## Code Structure

The plugin is organized as follows:

```
wp-oauth-server-extension/
│
├── admin/                   # Admin interface functionality
│   └── admin-menu.php       # Menu registration and page rendering
│
├── assets/                  # Frontend assets
│   ├── css/                 # Stylesheets
│   │   └── wo-admin.css     # Shared admin styles
│   └── js/                  # JavaScript
│       └── wo-admin.js      # Shared admin scripts
│
├── includes/                # Core functionality
│   └── class-remote-logout.php  # Main plugin class
│
├── views/                   # Admin UI templates
│   ├── admin-footer.php     # Footer template
│   ├── admin-header.php     # Header template
│   ├── test-ui.php          # UI testing page
│   └── tabs/                # Admin tabs
│       ├── clients.php      # Client management tab
│       ├── logs.php         # Activity logs tab
│       ├── overview.php     # Dashboard tab
│       ├── settings.php     # Settings tab
│       ├── stats.php        # Statistics tab
│       └── test.php         # Testing tools tab
│
├── uninstall.php            # Cleanup on plugin deletion
└── wp-oauth-server-extension.php  # Main plugin file
```

## UI/UX Improvements

The admin interface has been completely redesigned with a modern, user-friendly approach:

1. **Consistent Design System**

   - Card-based UI components
   - Modern color scheme aligned with WordPress admin
   - Responsive layouts for all screen sizes

2. **Enhanced Interactive Elements**

   - Toggle switches instead of checkboxes
   - Copy-to-clipboard functionality
   - Interactive success rate bars
   - Collapsible content sections
   - Visual feedback for all user interactions

3. **Optimized Organization**

   - Sticky sidebar navigation
   - Logical grouping of related settings
   - Improved table designs with better data visualization
   - Clear status indicators

4. **Performance Optimizations**
   - Shared CSS and JS resources
   - Reduced redundancy in code
   - Better asset loading

## Development

For development and testing purposes, the plugin includes a special UI testing page that showcases all the components and interactive elements. This page is only available when `WP_DEBUG` is enabled.

## Requirements

- WordPress 5.0+
- WP OAuth Server plugin 3.0.0+
- PHP 7.0+
