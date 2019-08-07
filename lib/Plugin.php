<?php

namespace PublicFunction\Toolkit;

use PublicFunction\Toolkit\Setup\RestAPI;
use PublicFunction\Toolkit\Admin\PostsTables;
use PublicFunction\Toolkit\Core\Container;
use PublicFunction\Toolkit\Setup\JsonConfig;
use PublicFunction\Toolkit\Core\Loader;
use PublicFunction\Toolkit\Core\SingletonTrait;
use PublicFunction\Toolkit\Setup\ScriptsAndStyles;

class Plugin
{
    use SingletonTrait;

    /**
     * Flag used to check for singleton instance
     * @var bool
     */
    protected $started = false;

    /**
     * Used to store the config.json file wrapped in a JsonConfig object
     * @var JsonConfig
     */
    protected $config;

    /**
     * Storage for all
     * @var Container
     */
    protected $container;

    /**
     * Used to enqueue actions and filters for the plugin.
     * @var Loader
     */
    protected $loader;

    protected function __construct()
    {
	    $_plugin_dir = trailingslashit( plugins_url('', dirname(__FILE__) ) );
	    $_plugin_path = trailingslashit(realpath(__DIR__ . '/../'));
	    $_theme_dir = trailingslashit(get_stylesheet_directory_uri());
	    $_theme_path = trailingslashit(get_theme_root() . DIRECTORY_SEPARATOR . get_stylesheet());
	    $this->container = new Container([
            // General
            // -------------------------------------
            'plugin' => [
                'name' => 'PublicFunction WordPress Toolkit',
                'short_name' => 'pf-wp-toolkit',
                'directory' => $_plugin_dir,
                'path' => $_plugin_path,
                'version' => '1.0.2',
                'config_path' => $_plugin_path . 'config/',

                // Asset paths and directories
                // -------------------------------------
                'assets' => [
                    'dir' => trailingslashit($_plugin_dir . 'assets'),
                    'path' => trailingslashit($_plugin_path . 'assets'),

                    'images' => trailingslashit($_plugin_dir . 'assets/images'),
                    'images_path' => trailingslashit($_plugin_path . 'assets/images'),
                ],
            ],

            'theme' => [
                'directory' => $_theme_dir,
                'path' => $_theme_path,
                'config_path' => $_theme_path . 'config/',

                // Asset paths and directories
                // -------------------------------------
                'assets' => [
                    'dir' => trailingslashit($_theme_dir . 'assets'),
                    'path' => trailingslashit($_theme_path . 'assets'),

                    'images_dir' => trailingslashit($_theme_dir . 'assets/images'),
                    'images_path' => trailingslashit($_theme_path . 'assets/images'),
                ],
            ],
        ]);
	    $this->config = new JsonConfig($this->theme_or_plugin('config_path', 'config.json', true));
	    $this->container->bulkSet([
            // Reset theme array now that we have config values
		    'theme' => [
			    'name' => isset($this->config['theme']['name']) ? $this->config['theme']['name'] : null,
			    'short_name' => isset($this->config['theme']['short_name']) ? $this->config['theme']['short_name'] : null,
			    'directory' => $_theme_dir,
			    'path' => $_theme_path,
			    'version' => $this->config['version'],
			    'config_path' => trailingslashit($_theme_path . 'config'),
			    'icon'      => isset($this->config['styles']['icon']) ? $this->config['styles']['icon'] : null,
			    'build' => $this->config['build'] ?: $this->config['version'],
			    'partials' => untrailingslashit($_theme_path . (isset($this->config['theme']['partials']) ? $this->config['theme']['partials'] : 'templates/partials')),

			    // Asset paths and directories
			    // -------------------------------------
			    'assets' => [
				    'dir' => trailingslashit($_theme_dir . 'assets'),
				    'path' => trailingslashit($_theme_path . 'assets'),

				    'images_dir' => trailingslashit($_theme_dir . 'assets/images'),
				    'images_path' => trailingslashit($_theme_path . 'assets/images'),
			    ],
		    ],

            'env' => [
                'production' => $this->config['env']['production'],
                'development' => $this->config['env']['development']
            ],

            'textdomain' => $this->container->get('plugin.short_name'),

            // Core and plugin support
            // -------------------------------------

            'loader' => function () {
                return new Loader();
            },

            'rest_api' => function (Container &$c) {
	            return new RestAPI($c);
            },

            'admin_extras' => function (Container &$c) {
                return new PostsTables($c);
            },

            // Optional plugins
            // -------------------------------------
            'use_customizer' => $this->config['use_customizer'],
            'customizer' => function (Container &$c) {
                return $this->config['use_customizer'] ? new Customizer\Customizer($c) : null;
            },
            'options' => function (Container &$c) {
                return $this->config['use_customizer'] ? $c->get('customizer')->saved() : null;
            },

            'use_metaboxer' => $this->config['use_metaboxer'],
            'metaboxer' => function (Container &$c) {
                return $this->config['use_metaboxer'] ? new Metaboxer\Metaboxer($c) : null;
            },

            'use_custom_post_types' => $this->config['use_custom_post_types'],
            'custom_post_types' => function (Container &$c) {
                return new Setup\CustomPostType($c);
            },

            // Stylesheets and Script registration
            // -------------------------------------
            'admin_assets' => function (Container &$c) {
                $assets = new ScriptsAndStyles($c);
                $assets->admin = true;

                $assets->style('pf_admin', $this->theme_or_plugin('assets.dir', 'admin.css'));
                $assets->script('pf_admin', $this->theme_or_plugin('assets.dir', 'admin.js'), ['jquery']);

                return $assets;
            },

            'front_end_assets' => function (Container &$c) {
	            if ($c->get('toolkit.lazy_images')) {
                    $assets = new ScriptsAndStyles($c);

                    // Scripts
                    $assets->script('lazy_images', $c->get('plugin.assets.dir') . 'lazy-images.js', null, $c->get('plugin.version'));
                    return $assets;
                }
	            return null;
            },

            'use_jquery_migrate'    => $this->config['use_jquery_migrate']
        ]);
	    if (get_template() !== get_stylesheet()) {
            $_parent_theme_dir = trailingslashit(get_template_directory_uri());
            $_parent_theme_path = trailingslashit(get_theme_root(get_template()) . DIRECTORY_SEPARATOR . get_template());
	        $this->container->bulkSet([
	            'parent_theme' => [
	                'short_name'    => get_template(),
                    'directory'     => $_parent_theme_dir,
                    'path'          => $_parent_theme_path,
                    'partials'      => untrailingslashit($_parent_theme_path . (isset($this->config['theme']['partials']) ? $this->config['theme']['partials'] : 'templates/partials')),
                ]
            ]);
        }
    }

    /**
     * Runs the app
     */
    protected function _run()
    {
        foreach ($this->container->getRunables() as $name => $runable) {
            if ($name != 'loader')
                $runable->run();
        }

        $this->loader()->run();
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // API
    //
    // Methods here are used throughout the plugin. You can use these methods
    // by calling pf_toolkit()->filter() or pf_toolkit('plugin.path') which is the same as
    // Plugin::getInstance()->container()->get('plugin.path'). Passing a string
    // to the pf_toolkit() wrapper function returns an object from the container while
    // using the method pointer `->` returns one of the following methods.
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @param string $offset
     * @return mixed|null
     */
    public function get($offset)
    {
        return $this->container->get($offset);
    }

    /**
     * Returns the container
     * @return Container
     */
    public function container()
    {
        return $this->container;
    }

    /**
     * Returns the loader
     * @return Loader
     */
    public function loader()
    {
        return $this->container->get('loader');
    }

	/**
	 * Checks to see whether a file exists in the current theme. If it does, return theme. Otherwise returns plugin.
     * @param string $path
	 * @param string $file
     * @param bool $realpath
	 * @return string
	 */
	public function theme_or_plugin($path, $file, $realpath = false) {
		if (file_exists($this->get('theme.' . ($realpath ? $path : str_replace('dir', 'path', $path))) . $file)) {
			return $this->get("theme.$path") . $file;
		}
		return $this->get("plugin.$path") . $file;
	}

    /**
     * Adds a script to the plugin.
     * @param string $handle
     * @param string $source
     * @param array|string $dependencies
     * @param null|int|string $version
     * @param string $screen
     * @param bool $admin
     * @return mixed
     */
    public function style($handle, $source, $dependencies = [], $version = null, $screen = 'all', $admin = false)
    {
        return self::getInstance()->get($admin ? 'admin_assets' : 'front_end_assets')
            ->style($handle, $source, $dependencies, $version, $screen);
    }

    /**
     * Adds a script to the plugin
     * @param string $handle
     * @param string $source
     * @param array|string $dependencies
     * @param null|string|int $version
     * @param bool $footer
     * @param bool $admin
     * @return mixed
     */
    public function script($handle, $source, $dependencies = [], $version = null, $footer = true, $admin = false)
    {
        return self::getInstance()->get($admin ? 'admin_assets' : 'front_end_assets')
            ->script($handle, $source, $dependencies, $version, $footer);
    }

    /**
     * @param string $handle
     * @param array|string|object $object
     * @param array $data
     * @param bool $admin
     * @return ScriptsAndStyles
     */
    public function localize($handle, $object, $data = [], $admin = false)
    {
        return self::getInstance()->get($admin ? 'admin_assets' : 'front_end_assets')
            ->localize($handle, $object, $data);
    }

    /**
     * Adds an action to the plugin
     * @param string $hook
     * @param callable $callback
     * @param int $priority
     * @param int $args
     * @return $this
     */
    public function action($hook, callable $callback, $priority = 10, $args = 1)
    {
        $instance = self::getInstance();
        $instance->loader()->addAction($hook, $callback, $priority, $args);
        return $instance;
    }

    /**
     * Adds a filter
     * @param string $hook
     * @param callable $callback
     * @param int $priority
     * @param int $args
     * @return $this
     */
    public function filter($hook, callable $callback, $priority = 10, $args = 1)
    {
        $instance = self::getInstance();
        $instance->loader()->addFilter($hook, $callback, $priority, $args);
        return $instance;
    }

    /**
     * Adds a shortcode
     * @param $hook
     * @param callable $callback
     * @return Plugin|null
     */
    public function shortcode($hook, callable $callback)
    {
        $instance = self::getInstance();
        $instance->loader()->addShortcode($hook, $callback);
        return $instance;
    }

    /**
     * Returns prefixed string with short namespace
     * @param string $name
     * @return string
     */
    public function prefix($name = '')
    {
        return $this->get('front_end_assets')->prefix($name);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Static API
    //
    // Primarily used to start and stop the plugin
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Starts the plugin
     * @return Plugin
     */
    public static function start()
    {
        $instance = self::getInstance();

        require_once trailingslashit(__DIR__) . 'Core' . DIRECTORY_SEPARATOR . 'functions.php';

        if (!$instance->started) {
            $instance->_run();
            $instance->started = true;
        }

        return $instance;
    }

    /**
     * Kills the application and redirects to a wordpress error page with a message
     * @param string $error
     * @param string $subtitle
     * @param string $title
     */
    public static function stop($error, $subtitle = '', $title = '')
    {
        $title = $title ?: __(self::getInstance()->get('plugin.name').' - Error', self::getInstance()->get('textdomain'));
        $message = "<h1>{$title}";

        if ($subtitle)
            $message .= "<br><small>{$subtitle}</small>";

        $message .= "</h1>";
        $message .= "<p>{$error}</p>";

        wp_die($message);
    }
}
