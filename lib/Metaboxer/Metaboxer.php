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

class Metaboxer extends RunableAbstract
{
    protected $metaboxes;

    protected $metaCache;

    protected $outputCache;

    protected $helper;

    public function __construct(Container $c)
    {
        require_once trailingslashit(__DIR__) . 'functions.php';
        parent::__construct($c);

        $this->helper = new Helpers();

        $boxes = $this->boxes();
        foreach ($boxes as $bid => $box) {
            $metabox = new Metabox($c, $bid, $box);
            $this->metaboxes[$bid] = $metabox->enqueue();
        }

        $this->rest_api()->addEndpoint("/metaboxer/(?P<post>[0-9]+)/(?P<path>[a-zA-Z|-|_]+)", [
            'callback' => [$this, 'metaboxerRest']
        ]);
    }

    /**
     * @return array|JsonConfig
     */
    public function boxes()
    {
        static $fields = [];
        if(empty($fields)) {
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

    private function getMeta($id) {
        if (!isset($this->metaCache[$id])) {
            $this->metaCache[$id] = get_post_meta($id);
        }

        return $this->metaCache[$id];
    }

    /**
     * @return array
     */
    public function getDefaults()
    {
        static $defaults = [];
        if(empty($defaults)) {
            foreach($this->boxes() as $bid => $box) {
                $fields = [];
                if (is_string($box['fields'])) {
                    $box['fields'] = $this->helper->shortcodeOrCallback($box['fields']);
                }
                if(!empty($box['fields']) && is_array($box['fields'])) {
                    foreach($box['fields'] as $id => $field) {
                        $fields[$id] = isset($field['default']) ? $this->helper->shortcodeOrCallback($field['default']) : '';
                        if ($field['type'] == 'image') {
                            $fields[$id.'_id'] = '';
                        } else if ($field['type'] == 'gallery') {
                            if (is_string($field['fields'])) {
                                $field['fields'] = $this->helper->shortcodeOrCallback($field['fields']);
                            }
                            foreach ($field['fields'] as $gid => $gfield) {
                                $field['fields'][$gid] = isset($gfield['default']) ? $this->helper->shortcodeOrCallback($gfield['default']) : '';
                                if ($gfield['type'] === 'image') $field['fields'][$gid.'_id'] = '';
                            }
                            $fields[$id] = array(
                                'count' => isset($field['default']) ? $field['default'] : '1',
                                'fields' => $field['fields']
                            );
                        }
                    }
                }
                $defaults[$bid] = $fields;
            }
        }

        return $defaults;
    }

    /**
     * Returns an array of all fields with their defaults
     * @param \WP_Post|int|null $post
     * @return array
     */
    public function saved($post)
    {
        $defaults = $this->getDefaults();
        $boxes = $this->boxes();

        if(empty($post) || is_int($post))
            $post = get_post($post);

        $meta = $this->getMeta($post->ID);

        foreach($meta as $key => $value) {
            if (is_array($value) && (is_serialized($value[0]) || count($value) === 1)) {
                $meta[$key] = maybe_unserialize($value[0]);
            }
        }

        if (isset($this->outputCache[$post->ID])) {
            $output = $this->outputCache[$post->ID];
        } else {
            $output = [];
            foreach($defaults as $bid => $box) {
                $correctPost = (is_array($boxes[$bid]['post_type']) ? in_array($post->post_type, $boxes[$bid]['post_type']) : $boxes[$bid]['post_type'] == $post->post_type);
                if(!isset($output[$bid]) && $correctPost) {
                    $output[$bid] = [];
                } else {
                    continue;
                }
                if(is_array($box)) {
                    foreach($box as $key => $value) {
                        if ($this->metaboxes[$bid]->is_single()) {
                            $metaValue = isset($meta[$bid.'_meta_'.$key]) ? $meta[$bid.'_meta_'.$key] : $value;
                        } else {
                            $metaValue = isset($meta[$bid.'_meta'][$key]) ? $meta[$bid.'_meta'][$key] : $value;
                        }
                        if ($value && is_array($value) && isset($value['fields'])) { //This is a gallery field. Lots of work to do...
                            $count = intval(is_array($metaValue) ? $value['count'] : $metaValue);
                            $i = 0;
                            $metaValue = array();
                            if (is_string($value['fields'])) {
                                $value['fields'] = $this->helper->shortcodeOrCallback($value['fields']);
                            }
                            while ($i < $count) {
                                $metaValue[$i] = array();
                                foreach ($value['fields'] as $field_key => $field) {
                                    $default = (isset($field['default']) ? $field['default'] : '');
                                    if (substr($field_key, -3) === '_id') { // Image ID field
                                        $image_key = substr($field_key, 0, -3);
                                        if ($this->metaboxes[$bid]->is_single()) {
                                            $fieldValue = isset($meta[$bid.'_meta_'.$key.'_data'][$image_key.'_'.$i]) ? $meta[$bid.'_meta_'.$key.'_data'][$image_key.'_'.$i] : $default;
                                        } else {
                                            $fieldValue = isset($meta[$bid.'_meta'][$key.'_'.$image_key.'_'.$i]) ? $meta[$bid.'_meta'][$key.'_'.$image_key.'_'.$i] : $default;
                                        }
                                    } else {
                                        if ($this->metaboxes[$bid]->is_single()) {
                                            $fieldValue = isset($meta[$bid.'_meta_'.$key.'_data'][$field_key.'_'.$i]) ? $meta[$bid.'_meta_'.$key.'_data'][$field_key.'_'.$i] : $default;
                                        } else {
                                            $fieldValue = isset($meta[$bid.'_meta'][$key.'_'.$field_key.'_'.$i]) ? $meta[$bid.'_meta'][$key.'_'.$field_key.'_'.$i] : $default;
                                        }
                                    }
                                    $metaValue[$i][$field_key] = $fieldValue;
                                    if (is_array($field) && $field['type'] == 'image' && substr($field_key)) {
                                        if ($this->metaboxes[$bid]->is_single()) {
                                            $metaValue[$i][$field_key.'_id'] = isset($meta[$bid.'_meta_'.$key.'_data'][$field_key.'_'.$i.'_id']) ? $meta[$bid.'_meta_'.$key.'_data'][$field_key.'_'.$i.'_id'] : '';
                                        } else {
                                            $metaValue[$i][$field_key.'_id'] = isset($meta[$bid.'_meta'][$key.'_'.$field_key.'_'.$i.'_id']) ? $meta[$bid.'_meta'][$key.'_'.$field_key.'_'.$i.'_id'] : '';
                                        }
                                    }
                                }
                                $i++;
                            }
                        }
                        $output[$bid][$key] = $metaValue;
                    }
                }
            }
            $this->outputCache[$post->ID] = $output;
        }

        return $output;
    }

    public function clear_meta_cache() {
        $this->metaCache = [];
        $this->outputCache = [];
    }

    /**
     * Used by the Metabox and GalleryType classes
     * @return array
     */
    public static function get_type_classes() {
        return array(
            'text' => TextType::class,
            'number' => TextType::class,
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
        );
    }

    /**
     * @param string $path
     * @param int|object $post
     * @return null|mixed
     */
    public function meta($path = '', $post) {
        return DotNotation::parse($path, $this->saved($post));
    }

    /**
     * @param \WP_REST_Request $request
     * @return mixed|\WP_Error|null
     */
    public function metaboxerRest(\WP_REST_Request $request)
    {
        if (!isset($request['path'])) {
            return new \WP_Error('pf_no_path', 'Invalid Metaboxer path', ['status' => 404]);
        }
        if (!isset($request['post'])) {
            return new \WP_Error('pf_no_post', 'Invalid Metaboxer Post ID', ['status' => 404]);
        }

        return $this->meta($request['path'], intval($request['post']));
    }

    public function run() {
        foreach($this->metaboxes as $metabox) {
            if ($metabox->registered) {
                $metabox->run();
            }
        }
        $this->loader()->addAction('wp_loaded', [$this, 'clear_meta_cache']);
    }

}
