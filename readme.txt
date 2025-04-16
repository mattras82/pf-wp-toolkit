=== Public Function WordPress Toolkit Plugin ===
Tested up to: 6.8
Requires at least: 5.0
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

= 1.2.0 =

Released on: 16 Apr 2025

 - Enhancement: Adding support revision tracking of Metaboxer fields
 - Enhancement: Adding support for Metaboxer fields in native REST API
 - Enhancement: Adding the `pf_meta_field_maybe_show` function & filter for conditional Metaboxer fields
 - Other: Moved to a custom WebPack build with modern dart-sass implementation
 - Deprecated: Removing custom lazy loading script now that all supported browsers have native lazy loading

= 1.1.3 =

Released on: 30 Jan 2025

 - Enhancement: Adding support for editing the "supports" value for existing post types
 - Bug Fix: Logic fix for group footers in Customizer
 - Other: Fixing PHP 8+ deprecation warnings

= 1.1.2 =

Released on: 24 Feb 2023

 - Enhancement: Adding support to bypass custom lazy loading
 - Other: Typo fix

= 1.1.1 =

Released on: 09 Aug 2022

 - Enhancement: Adding support for displaying post thumbnail for custom post types via the `show_thumb_in_admin` flag in JSON file
 - Other: Fixing some PHP 8 warnings

= 1.1.0 =

Released on: 07 Jul 2022

 - Not-A-Bug Fix: The way that the WP Blocks stylesheet is enqueued on the page is changing to be the first stylesheet in the markup instead of the last. This may introduce some style breaks, but is not likely.
 - Other: Updating PF JS Toolkit package

= 1.0.11 =

Released on: 22 Jun 2022

 - Bug Fix: Fixing ability to pass arguments in `[pf-partial]` shortcode for Gutenberg's shortcode block
 - Other: Updating label styles for Checkboxes Metaboxer fields
 - Other: Updating public-function-toolkit & jQuery Migrate to latest versions

= 1.0.10 =

Released on: 01 Apr 2022

 - Enhancement: Adding ability to pass arguments in `[pf-partial]` shortcode
 - Bug Fix: Fixing implementation of Metaboxer for taxonomies
 - Other: Changing the placement of the description text for Checkboxes Metaboxer fields

= 1.0.9 =

Released on: 14 Aug 2021

 - Enhancement: Adding LocalDevelopment filter to skip WPCF7's spam verification
 - Bug Fix: Minor grammar fix in GalleryType's button
 - Other: Moving from `@goldencomm/toolkit` NPM package to `public-fuction-toolkit`
 - Other: Miscellaneous fixes & changes

= 1.0.8 =

Released on: 30 Mar 2021

 - New Feature: Local Development
 - Enhancement: Adding support for Yoast breadcrumbs in the `pf_breadcrumb()` function
 - Enhancement: Adding support for taxonomies in Metaboxer
 - Enhancement: Adding support for re-sorting metaboxes with WYSIWYGs in post admin screen
 - Enhancement: Adding `hide_from_rest` property for Customizer and Metaboxer fields
 - Update: Updating Lazy Images, jQuery & jQuery Migrate
 - Update: removing ie11 from the browserslist array. This plugin *no longer supports Internet Explorer* out of the box.
 - Bug Fix: Fixing the `noindex, nofollow` meta tag bug that was recently introduced by an update in the Yoast SEO plugin
 - Bug Fix: Several bug fixes in Metaboxer, Setup, & Customizer

= 1.0.7 =
 - Bug Fix: Metaboxer image IDs not being set properly in admin (broken in v1.0.6)

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
