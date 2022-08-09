<?php

/**
 * NO STEP ON SNEK:
 *
 * This abstract class, unlike others within this theme, uses the snake_casing
 * naming convention: https://en.wikipedia.org/wiki/Snake_case like most WordPress
 * core classes. Everything else will use the camelCasing naming convention.
 */

namespace PublicFunction\Toolkit\Metaboxer;

use PublicFunction\Toolkit\Plugin;
use PublicFunction\Toolkit\Core\RunableAbstract;
use WP_Post;
use WP_Term;

abstract class MetaboxAbstract extends RunableAbstract
{
    /**
     * The metakey name to use for db storage. If use_single_keys flag is set to true,
     * this will be used as a prefix for all keys
     * @var string
     */
    protected $metakey = 'undefined';

    /**
     * Use single keys for storage in DB vs using one key and an associative array
     * @var boolean
     */
    protected $use_single_keys = true;

    /**
     * The name of the box, used for the title and html ID
     * @var string
     */
    protected $name = 'GC Metabox';

    /**
     * The html ID used by the metabox html elements
     * @var string
     */
    protected $html_id;

    /**
     * default key value pairs for this metabox
     * @var array
     */
    public $defaults = [];

    /**
     * The post type this metabox is added to
     * @var string
     */
    protected $post_type = 'post';

    /**
     * Can be either normal, side, advanced
     * @var string
     */
    protected $context = 'normal';

    /**
     * Can be either high or low
     * @var string
     */
    protected $priority = 'high';

    /**
     * The display callback. This is CAN be changed, but is set to the display method of this abstract
     * @var callable
     */
    protected $callback;

    /**
     * Used to prefix metaboxes names within the theme container
     * @var string
     */
    protected $storage_name = 'metabox';

    public function __construct(\PublicFunction\Toolkit\Core\Container &$container)
    {
        parent::__construct($container);

        // by default, all callbacks are set to the abstract
        // display method
        $this->callback = [$this, 'display'];
        $this->setup();
    }

    /**
     * This needs to be run on every Metabox instance
     * @return mixed
     */
    abstract public function setup();

    /**
     * Checks to see if we're in the correct screen to load scripts
     * @return bool
     */
    public function correct_screen()
    {
        global $pagenow, $typenow;

        if (empty($typenow) && !empty($_GET['post']))
            $typenow = get_post($_GET['post'])->post_type;

        return (is_admin() && $typenow == $this->post_type) && ($pagenow == 'post-new.php' || $pagenow == 'post.php');
    }

    /**
     * Adds the metabox to WordPress
     */
    public function add()
    {
        add_meta_box(
            $this->html_id,
            $this->name,
            $this->callback,
            $this->post_type,
            $this->context,
            $this->priority
        );

        if ($this->correct_screen())
            $this->scripts();
    }

    /**
     * @param int $id
     * @param array $defaults
     * @param null|string $parent_key
     */
    private function _save_single_keys($id, $defaults = [], $object_type = 'post', $parent_key = null)
    {
        foreach ($defaults as $default_key => $default_value) {
            $mk = $parent_key . '_' . $default_key;

            if (is_array($default_value)) {
                $this->_save_single_keys($id, $default_value, $object_type, $mk);
            } else {
                if (($value = isset($_POST[$mk]) ? $_POST[$mk] : null) && !empty($value)) {
                    update_metadata($object_type, $id, $mk, $value);
                } else {
                    delete_metadata($object_type, $id, $mk);
                }
            }
        }
    }

    /**
     * Saves the post's meta data created by this metabox
     * @param null|int $id
     * @param string $object_type
     * @return int|null
     */
    protected function save($id = null, $object_type = 'post')
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return $id;

        if (isset($_POST['_inline_edit']))
            return $id;

        if ($_POST) {

            if ($this->use_single_keys) {
                $this->_save_single_keys($id, $this->defaults, $object_type, $this->metakey);
            } else {
                if (($value = $_POST[$this->metakey]) && !empty($value)) {
                    update_metadata($object_type, $id, $this->metakey, $value);
                } else {
                    delete_metadata($object_type, $id, $this->metakey);
                }
            }
        }

        return $id;
    }

    public function save_post($post_id = null)
    {
        $correct_screen = in_array(get_post_type($post_id), (array) $this->post_type);
        if ($correct_screen) {
            return $this->save($post_id);
        }
        return $post_id;
    }

    /**
     * The method that takes care of diplaying the form
     * @param \WP_Post $post
     */
    public function display(\WP_Post $post)
    {
    }

    /**
     * Adds scripts to the admin head using wp_enqueue_{script/style}
     */
    public function scripts()
    {
    }

    /**
     * Wrapper to check for correct screen before loading the head content from child classes
     */
    public function loadHead()
    {
        if ($this->correct_screen())
            $this->head();
    }

    /**
     * Method used to add styling or inline scripts to the page
     */
    public function head()
    {
    }

    /**
     * Sets the name of the metabox
     * @param string $name
     * @return $this
     */
    public function name($name = '')
    {
        $this->name = $name;
        $this->html_id = sanitize_title($this->name . ' pf metabox');
        return $this;
    }

    /**
     * Sets the post type of the metabox
     * @param string $type
     * @return $this
     */
    public function post_type($type)
    {
        $this->post_type = $type;
        return $this;
    }

    /**
     * Sets the context of the metabox
     * @param string $context 'normal', 'side', 'advanced'
     * @return  $this
     */
    public function context($context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Sets the default values for this metabox's fields.
     * @param array $defaults
     * @return  $this
     */
    public function defaults($defaults = [])
    {
        $this->defaults = $defaults;
        return $this;
    }

    /**
     * Returns an HTML ID for an element.
     * @param int|string $name
     * @return string
     */
    protected function get_html_id($name)
    {
        $args = func_get_args();
        $sub = '';

        if (isset($args[1]))
            for ($i = 1; $i < count($args); $i++)
                $sub .= "_{$args[$i]}";

        return $this->metakey . '_' . $name . $sub;
    }

    /**
     * Print wrapper for $this->getHtmlID()
     * @param  string $name
     * @return void
     */
    protected function html_id($name = '')
    {
        echo $this->get_html_id($name);
    }

    /**
     * Returns the Input name for the element.
     * @param int|string $name
     * @return string
     */
    protected function get_input_name($name)
    {
        $args = func_get_args();
        $sub = '';
        if (isset($args[1])) {
            for ($i = 1; $i < count($args); $i++) {
                $sub .= $this->use_single_keys ? "_{$args[$i]}" : "[{$args[$i]}]";
            }
        }

        return $this->metakey . ($this->use_single_keys ? "_{$name}" : "[{$name}]") . $sub;
    }

    /**
     * Print wrapper for getInputName()
     * @param $name
     */
    protected function input_name($name)
    {
        echo $this->get_input_name($name);
    }

    /**
     * Returns the meta data for a post based on the metakey of a child class
     * @param int $post
     * @param string $object_type
     * @return mixed
     */
    protected function _get_meta($id, $object_type = 'post')
    {
        if ($id instanceof WP_Post) {
            $id = intval($id->ID);
        } else if ($id instanceof WP_Term) {
            $id = intval($id->term_id);
            $object_type = 'term';
        } else if (empty($id)) {
            $id = intval(get_the_ID());
        }

        return get_metadata($object_type, $id, $this->metakey, true);
    }

    /**
     * @param int $id
     * @param array $defaults
     * @param string $object_type
     * @param null|string $parent_key
     * @return array
     */
    private function _get_single_key_meta($id = null, $defaults = [], $object_type = 'post', $parent_key = null)
    {
        $meta = [];
        if (!$parent_key)
            $parent_key = $this->metakey;

        if (!is_int($id))
            $id = get_the_ID();

        foreach ($defaults as $key => $value) {
            $meta_key = $parent_key . '_' . $key;
            $val = null;
            if (is_array($value)) {
                $val = $this->_get_single_key_meta($id, $value, $object_type, $meta_key);
            }
            $val = $val ?: get_metadata($object_type, $id, $meta_key, true);
            if ($val) {
                $meta[$key] = $val;
            }
        }

        return wp_parse_args($meta, $defaults);
    }

    /**
     * @param int $id
     * @param string $object_type
     * @return array
     */
    protected function get_meta_with_defaults($id, $object_type = 'post')
    {
        return $this->use_single_keys ?
            $this->_get_single_key_meta($id, $this->defaults, $object_type) :
            wp_parse_args($this->_get_meta($id, $object_type), $this->defaults);
    }

    /**
     * Returns either all meta data or one by key if passed.
     * @param  \WP_Post|\WP_Term|int $id
     * @param  string $key
     * @return mixed
     */
    public function get_meta($key = null, $id = null, $object_type = 'post')
    {
        if (empty($id))
            $id = intval(get_the_ID());

        if ($id instanceof WP_Post)
            $id = intval($id->ID);

        if ($id instanceof WP_Term) {
            $id = intval($id->term_id);
            $object_type = 'term';
        }

        if (!is_int($id))
            return '';

        $values = $this->get_meta_with_defaults($id, $object_type);
        return !empty($key) && isset($values[$key]) ? $values[$key] : $values;
    }

    /**
     * Static wrapper for getMeta
     * @param  \WP_Post|int $post_id
     * @param  string $key
     * @return mixed
     */
    public static function meta($key = null, $post_id = null, $object_type = 'post')
    {
        return (new static(Plugin::getInstance()->container()))->get_meta($key, $post_id, $object_type);
    }

    /**
     * Adds the metabox to the theme
     * @param null $name
     */
    public function enqueue($name = null)
    {
        $name = !empty($name) ? $name : $this->metakey;
        $this->container->set("{$this->storage_name}_{$name}", function () {
            return $this;
        });
    }

    /**
     * Run
     */
    public function run()
    {
        foreach ((array) $this->post_type as $type) {
            $this->loader()->addAction("add_meta_boxes_$type", [$this, 'add']);
        }
        $this->loader()->addAction('admin_head', [$this, 'loadHead']);
        $this->loader()->addAction('save_post', [$this, 'save_post']);
    }
}
