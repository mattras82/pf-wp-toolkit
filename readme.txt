=== Public Function WordPress Toolkit Plugin ===
Tested up to: 5.4
Requires at least: 4.8
License: GPL-3.0+
License URI: http://www.gnu.org/licenses/gpl-3.0.txt

WordPress plugin for adding additional, developer-friendly functionality to a site. This includes JSON config file based functionality for Custom Post Types, Metaboxes, Theme Customizer, and more.

== Description ==

WordPress plugin for adding additional, developer-friendly functionality to a site. This includes JSON config file based functionality for Custom Post Types, Metaboxes, Theme Customizer, and more.

== Installation ==

1. Install the plugin:
    * Manually: Clone contents of this repository and create "pf-wp-toolkit" folder under wp-content/plugins/
    * Easily: Use the [GitHub Updater plugin](https://github.com/afragen/github-updater/releases) in the Admin to install this plugin from its [GitHub repository](https://github.com/mattras82/pf-wp-toolkit)
1. Enable plugin under Wordpress Admin

== Changelog ==

= 1.0.6 =
 - Enhancement: AJAX metabox refreshing works in the Block Editor now
 - Enhancement: AJAX metabox refreshing works for metaboxes with WYSIWYG fields
 - Updating jQuery & jQuery Migrate to their newest versions (3.5.1 & 3.2)
 - Bug Fix: Fixing serialization of gallery, checkbox, & radio fields in AJAX Metaboxer handler
 - Bug Fix: Gallery Metaboxer tabs broke when count was over 10. Not anymore!
 - Bug Fix: Metaboxer Radios displaying as checkboxes is now fixed (broken in v1.0.5)

= 1.0.5 =
 - Addition: Adding pf_lazy_option and pf_lazy_meta helper functions
 - Addition: Filtering robots.txt & <meta name="robots"> contents to discourage crawling and indexing by bots
 - Enhancement: Metaboxer max_items enhancement in gallery type
 - Enhancement: Adding title attribute to lazy svg images
 - Enhancement: Improved behavior for placeholder option for Select and Post type fields in Metaboxer
 - Enhancement: Adding custom $crumbs array and several default pages to pf_breadcrumb helper function
 - Enhancement: Switching build tools to new @goldencomm/build-scripts package, which updated the lazy-media assets
 - Bug Fix: Customizer Select2 field type placeholder & pre-selected option
 - Bug Fix: Metaboxer fields are now initialized after CustomPostTypes registers everything

= 1.0.4 =
- Bug fixes in `pf_get_partial` & Metabox hydration

= 1.0.3 =
- Enhancing lazy-images, fixing jQuery migrate & wysiwyg bugs, adding filters to Customizer & Metaboxer functions, updating NPM packages

= 1.0.2 =
- Enhancing metaboxer types with custom option/query functionality

= 1.0.1 =
- Admin media uploader text bug fix

= 1.0.0 =
- Initializing plugin
