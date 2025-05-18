# WP OAuth Server - Remote Logout Extension

This extension adds a remote logout endpoint to WP OAuth Server with zero configuration.

## Features

- Adds a logout endpoint to the OAuth server
- Automatically revokes all access tokens for a user
- Logs and tracks logout events
- Simple to configure and use
- Compatible with all OAuth 2.0 grant types
- Works with all WP OAuth Server versions 3.0.0+

## Performance Optimizations

This extension has been optimized for performance:

- **Combined JavaScript & CSS:** All JavaScript and CSS files have been merged into single files to reduce HTTP requests
- **Minified Versions:** Both JavaScript and CSS have minified versions available for production use
- **Improved Animation:** Uses `requestAnimationFrame` for smoother animations and transitions
- **DOM Caching:** DOM elements are cached to reduce costly DOM queries
- **CSS Transitions:** Uses CSS transitions instead of JavaScript animations when possible
- **Modern Browser Support:** Uses modern browser APIs with fallbacks when needed
- **Reduced Debug Code:** Removed unnecessary console logging and debug code in production files
- **Optimized Selectors:** Improved CSS selectors and grouped rules for better performance
- **Mobile Optimizations:** Responsive design with mobile-first approach and performance considerations
- **Organized Code Structure:** Well-organized code with logical sections for better maintainability

## CSS/JS Minification

# Install Node.js tools globally

npm install -g clean-css-cli uglify-js

# Minify CSS

cleancss -o assets/css/combined.min.css assets/css/combined.css

# Minify JavaScript

uglifyjs assets/js/combined.js -o assets/js/combined.min.js -c -m

## Installation

1. Upload the plugin files to the `/wp-content/plugins/wp-oauth-remote-logout` directory
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Settings screen to configure the plugin
4. (Optional) Customize additional options in the plugin's settings

## License

This plugin is licensed under the GPL v2 or later.

## Credits

Developed by Abdullah Alzuwayed

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
└── wp-oauth-logout-extension.php  # Main plugin file
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
