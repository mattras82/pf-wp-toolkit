<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              github.com/mattras82/pf-wp-toolkit
 * @since             1.0.0
 * @package           PF_WP_Toolkit
 *
 * @wordpress-plugin
 * Plugin Name:       PF WP Toolkit
 * Plugin URI:        github.com/mattras82/pf-wp-toolkit
 * Description:       This plugin adds developer-friendly functionality for WordPress Customizer, metaboxes, custom post types, and more.
 * Version:           1.0.4
 * Author:            Public Function
 * Author URI:        publicfunction.site
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       pf-wp-toolkit
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/mattras82/pf-wp-toolkit
 * GitHub Branch: master
 */

spl_autoload_register(function($class) {
	$prefix = 'PublicFunction\\Toolkit\\';

	$len = strlen($prefix);
	if (strncmp($prefix, $class, $len) !== 0)
		return;

	$d = DIRECTORY_SEPARATOR;
	$base = __DIR__ . "{$d}lib{$d}";
	$relative_class = substr($class, $len);
	$file = $base . str_replace('\\', $d, $relative_class) . '.php';

	if (file_exists($file)) {
		require $file;
	}

	return;
});

/**
 * This plugin requires at least 5.5.12
 */
if(!version_compare('5.5.12', phpversion(), '<=')) {
    PublicFunction\Toolkit\Plugin::stop(
        sprintf(__( 'You must be using PHP 5.5.12 or greater, currently running %s' ), phpversion()),
        __('Invalid PHP Version', 'pf-wp-toolkit')
    );
}


/**
 * Returns an instance of this plugin
 * @param null|string $name
 * @param null|string|callable $value
 * @return \PublicFunction\Toolkit\Plugin|mixed
 */
function pf_toolkit( $name = null, $value = null ) {
    $instance = \PublicFunction\Toolkit\Plugin::getInstance();
    $container = $instance->container();

    if( !empty($value) )
        return $container->set($name, $value);

    if( !empty($name) ) {
        return $container->get($name);
    }

    return $instance;
}

/**
 * Starts the plugin
 */
function pf_toolkit_start() {
    PublicFunction\Toolkit\Plugin::start();
}

add_action('plugins_loaded', 'pf_toolkit_start');
