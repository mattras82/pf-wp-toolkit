<?php

namespace PublicFunction\Toolkit\Metaboxer;


use PublicFunction\Toolkit\Assets\Helpers;
use PublicFunction\Toolkit\Core\Container;
use PublicFunction\Toolkit\Metaboxer\Types\BaseType;
use PublicFunction\Toolkit\Metaboxer\Types\ImageType;
use PublicFunction\Toolkit\Metaboxer\Types\GalleryType;
use WP_REST_Request;
use WP_REST_Server;

class Metabox extends MetaboxAbstract
{
    protected $args;
    protected $fields;
    protected $container;
    protected $add_callback;
    protected $callback_args = [];
    protected $type_classes = [];
    protected $revisions_enabled = true;
    protected $taxonomy;
    protected $helper;
    protected $storage_name = 'pf_metabox';
    protected $fields_registered = false;

    public $registered = false;

    public function __construct(Container &$container, $name = '', $args = [])
    {
        if ($name == '') {
            $this->storage_name = strtolower($container->get('theme.short_name')) . '_metabox';
        } else {
            $this->storage_name = $name;
        }
        $this->args = $args;
        $this->metakey = $this->storage_name . '_meta';
        $this->helper = new Helpers();

        if (is_array($this->args['fields']) && count($this->args['fields']) === 1) {
            foreach ($this->args['fields'] as $field) {
                if (!in_array($field['type'], ['image', 'gallery']))
                    $this->args['use_single_keys'] = true;
            }
        }

        parent::__construct($container);
    }

    /**
     * Sets the Metabox instance up using the metabox.json file &
     * registers the metabox with the plugin's container
     * @return Metabox
     */
    public function setup()
    {
        // Grab the already registered quick metaboxes so we can add to the array. if empty,
        // create new array
        $metaboxes = $this->registeredMetaboxes();

        if (!$this->registered) {

            // Go through each property and set them as part of this
            // class instance
            foreach ($this->args as $property => $value) {
                if ($value === null)
                    continue;
                elseif (method_exists($this, $property))
                    $this->{$property}($value);
                elseif (property_exists($this, $property))
                    $this->{$property} = $value;
            }

            // Save the instance
            foreach ((array) $this->post_type as $type) {
                if (!isset($metaboxes[$type]))
                    $metaboxes[$type] = [];

                $metaboxes[$type][] = $this->metakey;
            }
            $this->registered = true;
            $this->container[$this->storage_name] = $metaboxes;
        }

        return $this;
    }

    /**
     * We run this function after WP has been loaded in case any callbacks or entities are defined by the theme setup
     * @return void
     */
    public function setupFields()
    {
        $helper = new Helpers();
        if (is_string($this->fields)) {
            $this->fields = $helper->shortcodeOrCallback($this->fields);
        }
        if ($this->fields && is_array($this->fields)) {
            $this->type_classes = Metaboxer::get_type_classes();
            foreach ($this->fields as $field_key => $field) {
                if (array_key_exists($field['type'], $this->type_classes)) {
                    $field['id'] = $this->get_html_id($field_key);
                    $field['name'] = $this->get_input_name($field_key);
                    $field['key'] = $field_key;
                    $fieldClass = new $this->type_classes[$field['type']]($field);
                    $fieldClass->add_default($this->defaults);
                    $this->fields[$field_key] = $fieldClass;
                } else {
                    _doing_it_wrong(__FUNCTION__, "{$field['type']} is not a valid field type for Metaboxer", '1.2.0');
                }
            }
        }
    }

    public function get_field($name = '')
    {
        if (isset($this->fields[$name])) {
            return $this->fields[$name];
        }
        return null;
    }

    public function registerFields()
    {
        if (!$this->fields_registered) {
            $object_type = empty($this->taxonomy) ? 'post' : 'term';
            if (!empty($this->taxonomy)) {
                $this->revisions_enabled = false;
            } else if ($this->revisions_enabled) {
                foreach ((array) $this->post_type as $type) {
                    $this->revisions_enabled = $this->revisions_enabled && post_type_supports($type, 'revisions');
                }
            }
            $register_args = [
                'object_subtype' => $object_type == 'post' && is_string($this->post_type) ? $this->post_type : ($object_type == 'term' && is_string($this->taxonomy) ? $this->taxonomy : ''),
                'single'        => true,
                'revisions_enabled' => $this->revisions_enabled
            ];
            if ($this->is_single()) {
                foreach ($this->fields as $field) {
                    if ($field instanceof BaseType) {
                        $field->register_field($object_type, $this->metakey, $register_args);
                    }
                }
            } else {
                $args = wp_parse_args([
                    'label'         => $this->name,
                    'default'       => $this->defaults,
                    'show_in_rest'  => [
                        'schema'    => [
                            'items' => [
                                'type'   => [
                                    'string',
                                    'number',
                                    'integer',
                                    'boolean'
                                ]
                            ]
                        ]
                    ],
                    'type'          => 'array',
                ], $register_args);
                register_meta($object_type, $this->metakey, $args);
            }
            $this->fields_registered = true;
        }
    }

    /**
     * WP core's autosave code sends modified data for some of our meta fields.
     * So we're going to remove that data from the autosave request so that
     * the core code copies our meta data over from the original post.
     *
     * @param  mixed $result
     * @param  WP_Rest_Server $server
     * @param  WP_REST_Request $request
     * @return mixed
     */
    public function removeFieldsFromAutosave($result, WP_REST_Server $server, WP_REST_Request $request) {
        $meta = $request->get_param( 'meta' );
        if (is_array($meta)) {
            foreach ($this->defaults as $key => $val) {
                if (isset($meta["{$this->metakey}_{$key}"])) {
                    unset($meta["{$this->metakey}_{$key}"]);
                }
            }
            $request->set_param('meta', $meta);
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function display(\WP_Post $post)
    {
        if ($this->use_single_keys) {
            $meta = $this->get_meta(null, $post);
        } else {
            $meta = $this->_get_meta($post);

            if (is_array($meta) && isset($meta[0]) && is_serialized($meta[0]))
                $meta = unserialize($meta[0]);
        }

        echo '<div class="pf-metabox" data-pf-metakey="' . $this->metakey . '">';

        $this->display_fields($meta);

        echo '</div>';
        return '';
    }

    protected function display_fields($meta)
    {
        foreach ($this->fields as $field_key => $field) {

            if ($field instanceof BaseType) {
                $field->display($meta);
            } else {
                echo '<p class="pf-metabox-error">Oh no! Something went wrong. Check the ' . $field_key . ' field in the metaboxer.json file.</p>';
            }
        }
    }

    /**
     * Display the metabox for the taxonomy.
     *
     * @param stdClass|WP_Term $term Term to show the edit boxes for.
     */
    public function display_term(\WP_Term $term, $add_wrapper = true)
    {
        if ($this->use_single_keys) {
            $meta = $this->get_meta(null, $term);
        } else {
            $meta = $this->_get_meta($term);

            if (is_array($meta) && isset($meta[0]) && is_serialized($meta[0]))
                $meta = unserialize($meta[0]);
        }

        if ($add_wrapper) {
            echo '<div id="' . $this->storage_name . '-pf-metabox" class="postbox termbox">';
            echo '<h2>' . $this->name . '</h2>';
            echo '<div class="inside">';
        }

        echo '<div class="pf-metabox pf-metabox--term" data-pf-metakey="' . $this->metakey . '">';

        $this->display_fields($meta);

        if ($add_wrapper) {
            echo '</div></div>';
        }

        echo '</div>';
        return '';
    }

    /**
     * Update the taxonomy meta data on save.
     *
     * @param int    $term_id  ID of the term to save data for.
     * @param int    $tt_id    The taxonomy_term_id for the term.
     * @param string $taxonomy The taxonomy the term belongs to.
     */
    public function save_term($term_id, $tt_id, $taxonomy)
    {
        $correct_screen = in_array($taxonomy, (array) $this->taxonomy);

        if ($correct_screen) {

            $this->save($term_id, 'term');
        }

        return $term_id;
    }

    /**
     * @inheritdoc
     */
    public function enqueue($name = null)
    {
        $this->container->set("{$this->storage_name}_metabox", function () {
            return $this;
        });
        return $this;
    }

    /**
     * @return boolean
     */
    public function is_single()
    {
        return $this->use_single_keys;
    }

    /**
     * @inheritdoc
     */
    public function add()
    {
        $callback_results = true;

        if ($this->add_callback && is_callable($this->add_callback)) {
            $callback_results = call_user_func($this->add_callback, $this->callback_args);
        }

        if ($callback_results) {
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
    }

    //////////////////////////////////////////////////
    //  Private helpers
    //////////////////////////////////////////////////

    /**
     * Returns the list of already registered metaboxes saved inside the plugin's container
     * @return array|mixed|null
     */
    private function registeredMetaboxes()
    {
        return $this->get($this->storage_name) ?: [];
    }

    /**
     * Parses options from a field argument set and returns all available options
     * even if they don't match the field. This prevents default functions like
     * display_field from error-ing out
     * @param $array
     * @return array
     */
    private function parseField($array)
    {
        return wp_parse_args($array, [
            'type' => 'text',
            'label' => 'Label One',
            'default' => 'Default Value',
            'options' => [],
            'multiple' => false
        ]);
    }

    //////////////////////////////////////////////////
    //  AJAX Abilities
    //////////////////////////////////////////////////

    public function refresh()
    {
        $data = array();
        $id = null;
        $object_type = '';
        foreach ($_POST['form'] as $form_field) {
            if (substr($form_field['name'], 0, strlen($this->metakey)) === $this->metakey) {
                if ($this->use_single_keys) {
                    // Get the index of the first open bracket, if it exists
                    if ($openPos = strpos($form_field['name'], '[')) {
                        if (substr($form_field['name'], $openPos + 1, 1) === ']') {
                            // This is a checkbox or radio field. Turn it into an array
                            $name = substr($form_field['name'], 0, -2);
                            if (empty($data[$name])) {
                                $data[$name] = array();
                            }
                            $data[$name][] = $form_field['value'];
                        } else {
                            // This is a gallery field. Turn it into an associative array
                            $name = substr($form_field['name'], 0, $openPos);
                            if (empty($data[$name])) {
                                $data[$name] = array();
                            }
                            if (substr($form_field['name'], -2) === '[]') {
                                // This is a checkbox or radio field inside the Gallery. Make it an array
                                $inner_name = substr($form_field['name'], $openPos + 1, -3);
                                if (empty($data[$name][$inner_name])) {
                                    $data[$name][$inner_name] = array();
                                }
                                $data[$name][$inner_name][] = $form_field['value'];
                            } else {
                                $inner_name = substr($form_field['name'], $openPos + 1, -1);
                                $data[$name][$inner_name] = $form_field['value'];
                            }
                        }
                    }
                    if (substr($form_field['name'], -2) === '[]') {
                    } else if (substr($form_field['name'], -1) === ']') {
                    } else {
                        $data[$form_field['name']] = $form_field['value'];
                    }
                } else {
                    $data[substr($form_field['name'], strlen($this->metakey) + 1, -1)] = $form_field['value'];
                }
            } else if ($form_field['name'] === 'post_ID') {
                $id = intval($form_field['value']);
                $_POST['post_ID'] = $id;
                $object_type = 'post';
            } else if ($form_field['name'] === 'tag_ID') {
                $id = intval($form_field['value']);
                $_POST['tag_ID'] = $id;
                $object_type = 'term';
            }
        }

        if (!empty($object_type)) {
            if ($this->use_single_keys) {
                foreach ($data as $key => $value) {
                    update_metadata($object_type, $id, $key, $value);
                }
            } else {
                update_metadata($object_type, $id, $this->metakey, $data);
            }
            if ($object_type === 'post') {
                //Set the global post variable. This is for callback functions that use the current post.
                global $post;
                $post = get_post($id);
                $this->display($post);
            } else if ($object_type === 'term') {
                global $term;
                $term = get_term($id);
                $this->display_term($term, false);
            }
        }

        wp_die();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (!empty($this->taxonomy)) {
            foreach ((array) $this->taxonomy as $tax) {
                $this->loader()->addAction("{$tax}_edit_form", [$this, 'display_term']);
            }
            $this->loader()->addAction('edit_term', [$this, 'save_term'], 20, 3);
        } else {
            parent::run();
        }

        $this->loader()->addAction('wp_ajax_' . $this->metakey . '_refresh', [$this, 'refresh']);
        $this->loader()->addAction('wp_loaded', [$this, 'setupFields']);
        // The REST API requires field registration on its init hook,
        // but that is only run for REST requests. So we catch front end
        // calls after REST would have run with send_headers. Admin pages
        // (like Revisions) also need the fields registered.
        //
        $this->loader()->addAction('rest_api_init', [$this, 'registerFields']);
        $this->loader()->addAction('send_headers', [$this, 'registerFields']);
        $this->loader()->addAction('admin_init', [$this, 'registerFields']);
        $this->loader()->addAction('rest_pre_dispatch', [$this, 'removeFieldsFromAutosave'], 10, 3);
    }
}
