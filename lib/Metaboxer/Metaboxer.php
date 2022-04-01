<?php

namespace PublicFunction\Toolkit\Metaboxer;


use PublicFunction\Toolkit\Assets\Helpers;
use PublicFunction\Toolkit\Core\Container;
use PublicFunction\Toolkit\Core\RunableAbstract;
use PublicFunction\Toolkit\Core\DotNotation;
use PublicFunction\Toolkit\Metaboxer\Types\CheckboxesType;
use PublicFunction\Toolkit\Metaboxer\Types\CheckboxType;
use PublicFunction\Toolkit\Metaboxer\Types\DateType;
use PublicFunction\Toolkit\Metaboxer\Types\ImageType;
use PublicFunction\Toolkit\Metaboxer\Types\MediaType;
use PublicFunction\Toolkit\Metaboxer\Types\MultiPostType;
use PublicFunction\Toolkit\Metaboxer\Types\PostType;
use PublicFunction\Toolkit\Metaboxer\Types\RadiosType;
use PublicFunction\Toolkit\Metaboxer\Types\SelectType;
use PublicFunction\Toolkit\Metaboxer\Types\TextareaType;
use PublicFunction\Toolkit\Metaboxer\Types\TextType;
use PublicFunction\Toolkit\Metaboxer\Types\WysiwygType;
use PublicFunction\Toolkit\Metaboxer\Types\GalleryType;
use WP_Term;

class Metaboxer extends RunableAbstract
{
    protected $metaboxes;

    protected $metaCache;

    protected $outputCache;

    protected $helper;

    public function __construct(Container &$c)
    {
        require_once trailingslashit(__DIR__) . 'functions.php';
        parent::__construct($c);

        $this->helper = new Helpers();

        $boxes = $this->boxes();
        foreach ($boxes as $bid => $box) {
            $metabox = new Metabox($c, $bid, $box);
            $this->metaboxes[$bid] = $metabox->enqueue();
        }

        $this->rest_api()->addEndpoint("/metaboxer/(?P<post>[0-9]+)/(?P<path>[a-zA-Z-_.]+)", [
            'callback' => [$this, 'metaboxer_rest'],
            'permission_callback'   => '__return_true'
        ]);
    }

    /**
     * @return array|JsonConfig
     */
    public function boxes()
    {
        static $fields = [];
        if (empty($fields)) {
            $fields = new JsonConfig($this->get('theme.config_path') . 'metaboxer.json');
            $fields = $fields->get();
            foreach ($fields as &$box) {
                if (isset($box['partial']) && ($partial = $box['partial'])) {
                    $partialJson = new JsonConfig($this->get('theme.config_path') . "metaboxer/$partial.json");
                    $box = $partialJson->get();
                }
            }
        }

        return $fields;
    }


    /**
     * Gets the metadata for the object from this classes cache, or gets it from the system and then caches it
     *
     * @param  int $id The post or term ID
     * @param  string $object_type The string containing either "post" or "term"
     * @return mixed The cached metadata value
     */
    private function get_meta($id, $object_type = 'post')
    {
        if (!isset($this->metaCache[$object_type][$id])) {
            $this->metaCache[$object_type][$id] = get_metadata($object_type, $id);
        }

        return $this->metaCache[$object_type][$id];
    }

    /**
     * @return array
     */
    public function get_defaults()
    {
        static $defaults = [];
        if (empty($defaults) || pf_toolkit('pf_metaboxer_rest')) {
            foreach ($this->boxes() as $bid => $box) {
                $defaults[$bid] = $this->setup_default_fields($box['fields']);
            }
        }

        return $defaults;
    }

    private function setup_default_fields($args, $fields = [])
    {
        $types = Metaboxer::get_type_classes();

        if (is_string($args))
            $args = $this->helper->shortcodeOrCallback($args);

        if (is_array($args)) {
            foreach($args as $id => $field) {
                if (!empty($field['hide_from_rest']) && pf_toolkit('pf_metaboxer_rest'))
                    continue;
                $default = '';
                if (
                    $field['type'] === 'checkboxes'
                    || is_subclass_of($types[$field['type']], $types['checkboxes'])
                ) {
                    $default = [];
                } elseif (
                    'image' === $field['type']
                    || is_subclass_of($types[$field['type']], $types['image'])
                ) {
                    $fields[$id . '_id'] = '';
                } elseif (
                    'gallery' === $field['type']
                    || is_subclass_of($types[$field['type']], $types['gallery'])
                ) {
                    $default = array(
                        'count' => isset($field['default']) ? $field['default'] : '1',
                        'fields' => $this->setup_default_fields($field['fields'])
                    );
                    unset($field['default']);
                }
                $fields[$id] = !empty($field['default']) ? $this->helper->shortcodeOrCallback($field['default']) : $default;
            }
        }
        return $fields;
    }

    /**
     * Returns an array of all fields with their defaults
     * @param WP_Post|WP_Term|int|null $post
     * @return array
     */
    public function saved($post, $object_type = 'post')
    {
        $defaults = $this->get_defaults();
        $boxes = $this->boxes();
        $id = null;

        if ($post instanceof WP_Term) {
            $object_type = 'term';
        }

        if ($object_type === 'term') {
            $post = get_term($post);
            $id = intval($post->term_id);
        } else {
            $post = get_post($post);
            $id = intval($post->ID);
        }

        $meta = $this->get_meta($id, $object_type);

        foreach ($meta as $key => $value) {
            if (is_array($value) && (is_serialized($value[0]) || count($value) === 1)) {
                $meta[$key] = maybe_unserialize($value[0]);
            }
        }

        if (isset($this->outputCache[$object_type][$id])) {
            $output = $this->outputCache[$object_type][$id];
        } else {
            $output = [];
            foreach ($defaults as $bid => $box) {
                $correctPost = false;
                if ($object_type === 'term' && !empty($boxes[$bid]['taxonomy'])) {
                    $correctPost = in_array($post->taxonomy, (array) $boxes[$bid]['taxonomy']);
                } else if (!empty($boxes[$bid]['post_type'])) {
                    $correctPost = in_array($post->post_type, (array) $boxes[$bid]['post_type']);
                }
                if (!isset($output[$bid]) && $correctPost) {
                    $output[$bid] = [];
                } else {
                    continue;
                }
                if (is_array($box)) {
                    foreach ($box as $key => $value) {
                        if ($this->metaboxes[$bid]->is_single()) {
                            $metaValue = isset($meta[$bid . '_meta_' . $key]) ? $meta[$bid . '_meta_' . $key] : $value;
                        } else {
                            $metaValue = isset($meta[$bid . '_meta'][$key]) ? $meta[$bid . '_meta'][$key] : $value;
                        }
                        if (is_array($value) && isset($value['fields'])) { //This is a gallery field. Lots of work to do...
                            $count = intval(is_array($metaValue) ? $value['count'] : $metaValue);
                            $i = 0;
                            $metaValue = array();
                            while ($i < $count) {
                                $metaValue[$i] = array();
                                foreach ($value['fields'] as $field_key => $default) {
                                    if (substr($field_key, -3) === '_id') { // Image ID field
                                        $image_key = substr($field_key, 0, -3);
                                        if ($this->metaboxes[$bid]->is_single()) {
                                            $fieldValue = isset($meta[$bid . '_meta_' . $key . '_data'][$image_key . '_' . $i . '_id']) ? $meta[$bid . '_meta_' . $key . '_data'][$image_key . '_' . $i . '_id'] : $default;
                                        } else {
                                            $fieldValue = isset($meta[$bid . '_meta'][$key . '_' . $image_key . '_' . $i . '_id']) ? $meta[$bid . '_meta'][$key . '_' . $image_key . '_' . $i . '_id'] : $default;
                                        }
                                    } else {
                                        if ($this->metaboxes[$bid]->is_single()) {
                                            $fieldValue = isset($meta[$bid . '_meta_' . $key . '_data'][$field_key . '_' . $i]) ? $meta[$bid . '_meta_' . $key . '_data'][$field_key . '_' . $i] : $default;
                                        } else {
                                            $fieldValue = isset($meta[$bid . '_meta'][$key . '_' . $field_key . '_' . $i]) ? $meta[$bid . '_meta'][$key . '_' . $field_key . '_' . $i] : $default;
                                        }
                                    }
                                    $metaValue[$i][$field_key] = $fieldValue;
                                }
                                $i++;
                            }
                        }
                        $output[$bid][$key] = $metaValue;
                    }
                }
            }
            $this->outputCache[$object_type][$id] = $output;
        }

        return $output;
    }

    public function clear_meta_cache()
    {
        $this->metaCache = [];
        $this->outputCache = [];
    }

    /**
     * Used by the Metabox and GalleryType classes
     * @return array
     */
    public static function get_type_classes()
    {
        return apply_filters('pf_metaboxer_type_classes', array(
            'text' => TextType::class,
            'number' => TextType::class,
            'hidden' => TextType::class,
            'date' => DateType::class,
            'textarea' => TextareaType::class,
            'wysiwyg' => WysiwygType::class,
            'checkbox' => CheckboxType::class,
            'checkboxes' => CheckboxesType::class,
            'radio' => CheckboxType::class,
            'radios' => RadiosType::class,
            'select' => SelectType::class,
            'post' => PostType::class,
            'image' => ImageType::class,
            'gallery' => GalleryType::class,
            'media' => MediaType::class,
            'multi_post' => MultiPostType::class
        ));
    }

    /**
     * @param string $path
     * @param int|WP_Post|WP_Term $post
     * @param string $object_type
     * @return null|mixed
     */
    public function meta($path = '', $post = null, $object_type = 'post')
    {
        return DotNotation::parse($path, $this->saved($post, $object_type));
    }

    /**
     * @param \WP_REST_Request $request
     * @return mixed|\WP_Error|null
     */
    public function metaboxer_rest(\WP_REST_Request $request)
    {
        if (!isset($request['path'])) {
            return new \WP_Error('pf_no_path', 'Invalid Metaboxer path', ['status' => 404]);
        }
        if (!isset($request['post'])) {
            return new \WP_Error('pf_no_post', 'Invalid Metaboxer Post ID', ['status' => 404]);
        }

        pf_toolkit('pf_metaboxer_rest', true);

        return $this->meta($request['path'], intval($request['post']));
    }

    public function run()
    {
        foreach ($this->metaboxes as $metabox) {
            if ($metabox->registered) {
                $metabox->run();
            }
        }
        $this->loader()->addAction('wp_loaded', [$this, 'clear_meta_cache']);
    }

}
