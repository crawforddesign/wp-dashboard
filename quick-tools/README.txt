=== Quick Tools ===
Contributors: Crawford Design Group
Tags: dashboard, documentation, admin, custom post types, workflow
Requires at least: 6.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Streamline your WordPress admin workflow with configurable documentation widgets and quick custom post type creation tools.

== Description ==

Quick Tools enhances your WordPress admin experience by providing two powerful features:

**📚 Documentation System**
* Create internal documentation for website editors
* Organized by categories (Getting Started, Advanced, Troubleshooting)
* Dashboard widgets show documentation by category
* Admin-only access keeps internal information secure
* Built-in search functionality
* Import/export capabilities for easy backup and transfer

**⚡ CPT Dashboard Widgets**
* Quick-add widgets for custom post types
* Large, prominent creation buttons
* Post statistics at-a-glance
* Recent posts with quick edit access
* Configurable number of items displayed

**Key Features:**
* Clean, intuitive admin interface
* Fully configurable settings
* Responsive design
* Translation ready
* Performance optimized
* No frontend impact

Perfect for agencies, developers, and anyone managing multiple WordPress sites who needs to provide clear documentation and streamlined workflows for content editors.

== Installation ==

1. Upload the `quick-tools` folder to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to **Quick Tools** in your admin menu to configure settings
4. Start creating documentation and selecting custom post types for dashboard widgets

== Frequently Asked Questions ==

= Who can see the documentation? =

Only users with administrator privileges can view and edit documentation. This keeps internal information secure while providing helpful guidance to authorized users.

= Can I customize the documentation categories? =

Yes! While the plugin comes with three default categories (Getting Started, Advanced, Troubleshooting), you can add, edit, or remove categories as needed through the WordPress admin.

= How many custom post types can I add to the dashboard? =

There's no limit to the number of custom post types you can select. The plugin will create dashboard widgets for each selected post type.

= Can I export my documentation to use on other sites? =

Absolutely! The Import/Export feature allows you to backup your documentation or transfer it to other sites running Quick Tools.

= Does this plugin affect my website's frontend performance? =

No. Quick Tools is purely an admin enhancement and has no impact on your website's frontend performance or appearance.

= Will this work with my existing custom post types? =

Yes! Quick Tools automatically detects all available custom post types (created by themes or other plugins) and allows you to select which ones should have dashboard widgets.

== Screenshots ==

1. Main settings page with tabbed interface
2. Documentation dashboard widgets organized by category
3. Custom post type quick-add widgets with statistics
4. Import/export interface for documentation backup
5. Documentation editor with category assignment
6. Search functionality within documentation

== Changelog ==

= 1.0.0 =
* Initial release
* Documentation system with categorized dashboard widgets
* Custom post type quick-add dashboard widgets
* Import/export functionality
* Built-in search capabilities
* Responsive admin interface
* Translation support

== Upgrade Notice ==

= 1.0.0 =
Initial release of Quick Tools. Activate to start streamlining your WordPress admin workflow.

== Developer Information ==

**Plugin Structure:**
```
quick-tools/
├── quick-tools.php (main plugin file)
├── includes/
│   ├── class-quick-tools.php
│   ├── class-documentation.php
│   ├── class-cpt-dashboard.php
│   ├── class-admin.php
│   ├── class-loader.php
│   ├── class-i18n.php
│   ├── class-activator.php
│   └── class-deactivator.php
├── admin/
│   ├── css/admin-style.css
│   ├── js/admin-script.js
│   └── views/
│       ├── admin-page.php
│       ├── documentation-tab.php
│       ├── cpt-dashboard-tab.php
│       └── import-export-tab.php
└── languages/
    └── quick-tools.pot
```

**Custom Post Type:** `qt_documentation`
**Taxonomy:** `qt_doc_category`

**Hooks Available:**
* `qt_cpt_custom_actions` - Add custom actions to CPT widgets
* Standard WordPress post type and taxonomy hooks

**Requirements:**
* WordPress 6.0+
* PHP 7.4+
* Administrator privileges for full functionality

== Support ==

For support, documentation, or customization services, visit [Crawford Design Group](https://crawforddesigngp.com).

This plugin is actively maintained and supported. We welcome feedback and feature requests.