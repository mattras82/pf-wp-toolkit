<?php

namespace PublicFunction\Toolkit\Metaboxer;


use PublicFunction\Toolkit\Assets\Helpers;
use PublicFunction\Toolkit\Core\Container;
use PublicFunction\Toolkit\Metaboxer\Types\BaseType;
use PublicFunction\Toolkit\Metaboxer\Types\CheckboxesType;
use PublicFunction\Toolkit\Metaboxer\Types\CheckboxType;
use PublicFunction\Toolkit\Metaboxer\Types\DateType;
use PublicFunction\Toolkit\Metaboxer\Types\ImageType;
use PublicFunction\Toolkit\Metaboxer\Types\PostType;
use PublicFunction\Toolkit\Metaboxer\Types\RadiosType;
use PublicFunction\Toolkit\Metaboxer\Types\SelectType;
use PublicFunction\Toolkit\Metaboxer\Types\TextareaType;
use PublicFunction\Toolkit\Metaboxer\Types\TextType;
use PublicFunction\Toolkit\Metaboxer\Types\WysiwygType;
use PublicFunction\Toolkit\Metaboxer\Types\GalleryType;

class Metabox extends MetaboxAbstract
{
    protected $args;
    protected $fields;
    protected $container;
    protected $add_callback;
    protected $callback_args = [];
    protected $type_classes = [];
    protected $helper;
    protected $storage_name = 'pf_metabox';

    public $registered = false;

    public function __construct(Container &$container, $name = '', $args = [])
    {
        $this->type_classes = Metaboxer::get_type_classes();
        if ($name == '') {
            $this->storage_name = strtolower($container->get('theme.short_name')) . '_metabox';
        } else {
            $this->storage_name = $name;
        }
        $this->args = $args;
        $this->metakey = $this->storage_name.'_meta';
        $this->helper = new Helpers();

        if (count($this->args['fields']) === 1) {
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

        if(!$this->registered) {

            // Go through each property and set them as part of this
            // class instance
            foreach($this->args as $property => $value) {
                if($value === null)
                    continue;
                elseif(method_exists($this, $property))
                    $this->{$property}($value);
                elseif(property_exists($this, $property))
                    $this->{$property} = $value;
            }

            // Save the instance
            foreach ((array) $this->post_type as $type) {
                if(!isset($metaboxes[$type]))
                    $metaboxes[$type] = [];

                $metaboxes[$type][] = $this->metakey;
            }
            $this->registered = true;
            $this->container[$this->storage_name] = $metaboxes;
        }

        return $this;
    }

	/**
	 * We run this function after the theme has been initialized in case any callbacks are defined in the functions.php file
	 * @return void
	 */
    public function setupFields() {
    	if ($this->fields) {
		    $helper = new Helpers();
		    foreach($this->fields as $field_key => $field) {
			    $this->defaults[$field_key] = isset($field['default']) ? $helper->shortcodeOrCallback($field['default']) : '';
			    if(array_key_exists($field['type'], $this->type_classes)) {
				    $field['id'] = $this->get_html_id($field_key);
				    $field['name'] = $this->get_input_name($field_key);
				    $field['key'] = $field_key;
				    $this->fields[$field_key] = new $this->type_classes[$field['type']]($field);
				    if ($field['type'] === 'image') $this->defaults[$field_key.'_id'] = '';
				    if ($this->use_single_keys && $field['type'] === 'gallery') $this->defaults[$field_key.'_data'] = '';
			    }
		    }
	    }
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

        echo '<div class="pf-metabox" data-pf-metakey="'.$this->metakey.'">';

        foreach($this->fields as $field_key => $field) {

            if ($field instanceof BaseType) {
                $field->display($meta);
            } else {
                echo '<p class="pf-metabox-error">Oh no! Something went wrong. Check the '.$field_key.' field in the metaboxer.json file.</p>';
            }
        }

        echo '</div>';
        return '';
    }

    /**
     * @inheritdoc
     */
    public function enqueue($name = null)
    {
        $this->container->set("{$this->storage_name}_metabox", function() {
            return $this;
        });
        return $this;
    }

    /**
     * @returns boolean
     */
    public function is_single() {
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

            if($this->correct_screen())
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
        return $this->get($this->storage_name) ? $this->get($this->storage_name) : [];
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
            'options' => [ ],
            'multiple' => false
        ]);
    }

    //////////////////////////////////////////////////
    //  AJAX Abilities
    //////////////////////////////////////////////////

    public function refresh($test = false) {
        $data = array();
        $post_id = null;
        foreach ($_POST['form'] as $form_field) {
            if(substr($form_field['name'], 0, strlen($this->metakey)) === $this->metakey) {
                if ($this->use_single_keys) {
                    $data[$form_field['name']] = $form_field['value'];
                } else {
                    $data[substr($form_field['name'], strlen($this->metakey)+1, -1)] = $form_field['value'];
                }
            } else if ($form_field['name'] === 'post_ID') {
                $post_id = $form_field['value'];
                $_POST['post_ID'] = $post_id;
            }
        }

        if ($post_id) {
            //Set the global post variable. This is for callback functions that use the current post.
            global $post;
            $post = get_post($post_id);

            if ($this->use_single_keys) {
                foreach($data as $key => $value) {
                    //Try adding the meta. If it exists, update it.
                    if (!add_post_meta($post_id, $key, $value, true)) {
                        update_post_meta($post_id, $key, $value);
                    }
                }
            } else {
                $current = get_post_meta($post_id, $this->metakey, true);

                if (!$current) {
                    add_post_meta($post_id, $this->metakey, 'dummy value');
                }

                update_post_meta($post_id, $this->metakey, $data);
            }
            $this->display(get_post($post_id));
        }

        wp_die();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        parent::run();

        $this->loader()->addAction('wp_ajax_'.$this->metakey.'_refresh', [$this, 'refresh']);
    }

}
