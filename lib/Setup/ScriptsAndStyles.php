<?php

namespace PublicFunction\Toolkit\Setup;

use PublicFunction\Toolkit\Core\RunableAbstract;
use PublicFunction\Toolkit\Plugin;

class ScriptsAndStyles extends RunableAbstract
{
    /**
     * Wether or not this instance is meant to be used in the admin
     * @var bool
     */
    public $admin = false;

    /**
     * Weather or not we need to load the media.js for the admin
     * @var bool
     */
    public $media = false;

    /**
     * The registered styles
     * @var array
     */
    protected $styles = [];

    /**
     * The registered objects
     * @var array
     */
    protected $localizedObjects = [];

    /**
     * The registered scripts
     * @var array
     */
    protected $scripts = [];

    /**
     * Last registered item
     * @var array
     */
    protected $lastItemType;

    /**
     * Global callable to prevent this from instantiating
     * @var callable|null
     */
    protected $blocker;

    /**
     * Array of script handles to add the HTML5 defer property to
     * @var array
     */
    protected $deferredScripts = [];

    /**
     * Returns a prefixed handle
     * @param string $name
     * @return string
     */
    public function prefix($name = '')
    {
        return strtolower( $this->get('theme.short_name') ) . '_' . $name;
    }

    /**
     * Provide a callable function that blocks the entire class from running. If
     * you need to block an individual file, pass a callable at the end of the
     * asset add function
     * @param callable|null $blocker
     * @return $this
     */
    public function blockIf(callable $blocker = null)
    {
        if(is_callable($blocker))
            $this->blocker = $blocker;

        return $this;
    }

    /**
     * The path of the file to return the base version of it
     * @param null|string $file
     * @return string
     */
    public function version($file = null)
    {
        if ($this->get('env.production'))
            return $this->get('theme.build');

        if($file === 'theme')
            return $this->get('theme.version');

        if(strpos($file, $this->get('theme.directory')) !== false) {
            $file = str_replace(
                $this->get('theme.directory'),
                $this->get('theme.path'),
                $file
            );
        }

        if(empty($file) || !file_exists($file))
            $file = $this->get('theme.path') . 'style.css';

        return base_convert(date('YmdHis', filemtime($file)), 10, 36);
    }

    /**
     * Registers a stylesheet with the theme to be later enqueued
     * @param string $handle
     * @param string $source
     * @param array $dependencies
     * @param null|string $version
     * @param string $screen
     * @param callable|null $blocker
     * @return $this
     */
    public function style($handle, $source, $dependencies = [], $version = 'asset', $screen = 'all', callable $blocker = null)
    {
        if(empty($handle))
            throw new \RuntimeException('A valid `$handle` is required to register a new style');

        if(empty($source))
            throw new \RuntimeException('A valid `$source` is required to register a new style');

        $this->lastItemType = 'style';
        $this->styles[] = [
            'handle' => $handle,
            'source' => $source,
            'dependencies' => $dependencies,
            'version' => $version == 'asset' ? $this->version($source) : $version,
            'screen' => $screen,
            'blocker' => $blocker
        ];

        return $this;
    }

    /**
     * Adds a localized object to be enqueued before a specific script
     * @param string $handle
     * @param array $object
     * @param array $data
     * @return $this
     */
    public function localize($handle, $object, $data = [])
    {
        if(empty($handle))
            throw new \RuntimeException('A valid `$handle` is required to register a localized object');

        if(empty($object))
            throw new \RuntimeException('A valid `$object` is required to register a localized object');

        $this->lastItemType = 'localized';
        $this->localizedObjects[] = [
            'handle' => $handle,
            'object' => $object,
            'data' => $data
        ];

        return $this;
    }

    /**
     * Registers a script with the theme to be later enqueued
     * @param string $handle
     * @param string $source
     * @param array $dependencies
     * @param null|string $version
     * @param bool $footer
     * @param callable|null $blocker
     * @param bool $defer
     * @return $this
     */
    public function script($handle, $source, $dependencies = [], $version = 'asset', $footer = true, callable $blocker = null, $defer = false)
    {
        if(empty($handle))
            throw new \RuntimeException('A valid `$handle` is required to register a new style');

        if(empty($source))
            throw new \RuntimeException('A valid `$source` is required to register a new style');

        $this->lastItemType = 'script';
        $this->scripts[] = [
            'handle' => $handle,
            'source' => $source,
            'dependencies' => $dependencies,
            'version' => $this->version($version == 'asset' ? $source : $version),
            'footer' => $footer,
            'blocker' => $blocker
        ];

        if($defer)
            $this->deferredScripts[] = $this->prefix($handle);

        return $this;
    }

    private function getItemByHandle($handle, $items)
    {
        $idx = -1;
        foreach($items as $key => $item) {
            if($item['handle'] == $handle) {
                $idx = $key;
                break;
            }
        }
        return $idx !== -1 ? $idx : false;
    }

    /**
     * @param null|string $dependency
     * @param string $parent
     * @param string $type
     * @return $this
     */
    public function asDependency($dependency = null, $parent = 'main', $type = 'script')
    {
        if($dependency === null)
            $type = $this->lastItemType;

        switch($type) {
            case 'script':
            case 'scripts':
            case 'js':
                if($dependency === null)
                    $dependency = $this->prefix($this->scripts[end(array_keys($this->scripts))]['handle']);

                if($parent = $this->getItemByHandle($parent, $this->scripts))
                    $this->scripts[$parent]['dependencies'][] = $dependency;

                break;
            case 'style':
            case 'styles':
            case 'css':
                if($dependency === null)
                    $dependency = $this->prefix($this->styles[end(array_keys($this->styles))]['handle']);

                if($parent = $this->getItemByHandle($parent, $this->styles))
                    $this->styles[$parent]['dependencies'][] = $dependency;

                break;
        }

        return $this;
    }

    public function addDefer($tag, $handle, $src) {

        if (in_array($handle, $this->deferredScripts)) {
            return '<script defer src="'.$src.'"></script>';
        }

        return $tag;
    }

    public function removeMigrate($scripts)
    {
        if (!is_admin() && isset($scripts->registered['jquery'])) {
            $jquery = $scripts->registered['jquery'];

            if ($jquery->deps) {
                $jquery->deps = array_diff($jquery->deps, ['jquery-migrate']);
            }
        }
    }

    public function jqueryToFooter()
    {
        if (!is_admin()) {
            $wp_scripts = wp_scripts();
            $wp_scripts->add_data('jquery', 'group', 1);
            $wp_scripts->add_data('jquery-core', 'group', 1);
            if($this->get('use_jquery_migrate'))
                $wp_scripts->add_data('jquery-migrate', 'group', 1);
        }
    }

    public function replaceJquery()
    {
        if (!is_admin()) {
            wp_deregister_script('jquery');
            $jqueryPath = Plugin::getInstance()->theme_or_plugin('assets.dir', 'jquery.min.js');
            wp_register_script('jquery', $jqueryPath, [], null, true);
            wp_enqueue_script('jquery');
        }
    }

    public function printLazyStyles()
    {
        ?>
        <style>
            img[data-src] {
                transition: opacity 200ms ease-in-out;
            }
            img[data-src].no-js {
                display: none;
            }
            img[data-src].loading {
                opacity: 0.5;
            }
            img[data-src].loaded {
                opacity: 1;
            }
        </style>
        <?php
    }

    public function lazyLoadBlockStyles($html) {
        if (stripos($html, 'wp-block-library-css') && !stripos($html, 'noscript')) {
            $noscript = "<noscript>$html</noscript>";
            $start = stripos($html, 'href')+6;
            $quote = substr($html, $start - 1, 1) === '"' ? '"' : "'";
            $end = stripos($html, $quote, $start);
            $href = substr($html, $start, $end - $start);
            $html = "<script>window.wp_block_library_css = '$href';</script>" . $noscript;
        }
        return $html;
    }

    public function lazyLoadBlockStylesScript()
    {
        ?>
        <script type="text/javascript">
            if (wp_block_library_css && document.querySelector('[class^="wp-block"], [class^="has-"]')) {
                var $link = document.createElement('link');
                $link.setAttribute('rel', 'stylesheet');
                $link.setAttribute('media', 'all');
                $link.setAttribute('type', 'text/css');
                $link.setAttribute('href', wp_block_library_css);
                document.head.appendChild($link);
            }
        </script>
        <?php
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->loader()->addAction(($this->admin ? 'admin' : 'wp') . '_enqueue_scripts', function() {

            if(is_callable($this->blocker) && call_user_func($this->blocker, []))
                return false;

            if($this->admin && $this->media)
                wp_enqueue_media();

            if(count($this->scripts) > 0) {
                foreach($this->scripts as $item) {
                    wp_register_script(
                        $this->prefix($item['handle']),
                        $item['source'],
                        $item['dependencies'],
                        $item['version'],
                        $item['footer']
                    );
                }
            }

            if(count($this->localizedObjects) > 0) {
                foreach($this->localizedObjects as $obj) {
                    wp_localize_script($this->prefix($obj['handle']), $obj['object'], $obj['data']);
                }
            }

            if(count($this->scripts) > 0) {
                foreach($this->scripts as $item) {
                    if(is_callable($item['blocker']) && call_user_func_array($item['blocker'], []))
                        continue;

                    wp_enqueue_script($this->prefix($item['handle']));
                }
            }

            if(count($this->styles) > 0) {
                foreach($this->styles as $item) {
                    wp_register_style(
                        $this->prefix($item['handle']),
                        $item['source'],
                        $item['dependencies'],
                        $item['version'],
                        $item['screen']
                    );
                }

                foreach($this->styles as $item) {
                    if(is_callable($item['blocker']) && call_user_func_array($item['blocker'], []))
                        continue;

                    wp_enqueue_style($this->prefix($item['handle']));
                }
            }
        });

        if (count($this->deferredScripts) > 0)
            $this->loader()->addFilter('script_loader_tag', [$this, 'addDefer'], 10, 3);

        if (file_exists($this->get('theme.path') . 'assets/jquery.min.js') || file_exists($this->get('plugin.path') . 'assets/jquery.min.js')) {
            $this->loader()->addAction('wp_enqueue_scripts', [$this, 'replaceJquery']);
        } else {
            if (!$this->get('use_jquery_migrate'))
                $this->loader()->addAction('wp_default_scripts', [$this, 'removeMigrate']);

            $this->loader()->addAction('wp_enqueue_scripts', [$this, 'jqueryToFooter'], 11);
        }

        if ($this->get('toolkit.lazy_load')) {
            $this->loader()->addAction('wp_head', [$this, 'printLazyStyles']);
        }

        $this->loader()->addFilter('style_loader_tag', [$this, 'lazyLoadBlockStyles']);
        $this->loader()->addAction('wp_print_footer_scripts', [$this, 'lazyLoadBlockStylesScript']);
    }
}
