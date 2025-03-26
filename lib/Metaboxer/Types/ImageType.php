<?php

namespace PublicFunction\Toolkit\Metaboxer\Types;


use PublicFunction\Toolkit\Core\Markup;

class ImageType extends BaseType
{
    /**
     * @var string
     */
    protected $preview_size;

    /**
     * @var string
     */
    protected $media_type = 'image';

    /**
     * @var string
     */
    protected $media_label = 'image';

    public function __construct($args)
    {
        parent::__construct($args);

        if (!$this->preview_size)
            $this->preview_size = 'medium';
    }

    /**
     * @inheritdoc
     */
    public function display($meta) {
        if (!$this->maybe_show($meta)) {
            return '';
        }

        if (isset($meta[$this->key]))
            $this->field_attr['value'] = [
                'url' => $meta[$this->key],
                'id' => isset($meta[$this->key.'_id']) ? $meta[$this->key.'_id'] : ''
            ];

        return $this->display_field();
    }

    /**
     * @inheritdoc
     */
    protected function display_field()
    {
        $value = $this->get_value();
        $image = $this->get_attachment($value);

        echo '<div class="pf-metabox-image'.($image ? ' has-image' : '').'" data-media-type="'.$this->media_type.'">';

        echo Markup::tag('input', [
            'type' => 'hidden',
            'id' => $this->id.'_attachment_url',
            'name' => $this->name,
            'class' => 'pf-metabox-image-url',
            'value' => $value['url']
        ]);

        echo Markup::tag('input', [
            'type' => 'hidden',
            'id' => $this->id.'_attachment_id',
            'name' => (strpos($this->name, ']') ? str_replace(']', '_id]', $this->name) : $this->name.'_id'),
            'class' => 'pf-metabox-image-id',
            'value' => $value['id']
        ]);

        if($this->label)
            echo Markup::tag('h4', ['class' => 'pf-metabox-image-label'], $this->label);
        if($this->description)
            echo Markup::tag('p', ['class' => 'pf-metabox-image-description'], $this->description);

        echo '<div class="pf-metabox-image-preview" style="display:'.($image ? 'block' : 'none').'">';

        $this->display_preview($image);

        echo Markup::tag('p', [
            'class' => 'pf-metabox-image-change-description'
        ], 'Click the image above to edit or update');

        echo '</div>';

        echo Markup::tag('a', [
            'href'  => 'javascript:void(0)',
            'class' => 'pf-metabox-image-change-or-remove'
        ], __($image ? 'Remove '.$this->media_label : 'Add '.$this->media_label, pf_toolkit('textdomain')));

        echo '</div>';

        return true;
    }

    protected function get_value() {
        if (!is_array($this->field_attr['value'])) {
            return [
                'id' => null,
                'url' => $this->default
            ];
        } else {
            return $this->field_attr['value'];
        }
    }

    protected function get_attachment($value) {
        return ($value['id'] ? wp_get_attachment_image_url($value['id'], $this->preview_size) : $value['url']);
    }

    protected function display_preview($src = null) {
        echo Markup::tag('a', [
            'href' => 'javascript:void(0)',
            'class' => 'pf-metabox-image-change-image',
            'title' => 'Change '.ucwords($this->media_type)
        ], Markup::tag('img', ['src' => $src, 'alt' => 'Image preview']));
    }

    /**
     * Adds this field & the additional media post ID to default array
     * @inheritdoc
     */
    public function add_default(&$defaults) {

        $defaults[$this->key] = $this->default;
        $defaults["{$this->key}_id"] = '';

        return $defaults;
    }

    /**
     * Registers this field & the Media ID field
     * @inheritdoc
     */
    public function register_field($object_type, $prefix, $args = []) {

        $args = $this->setup_register_args($args);
        $success = $this->register_meta($object_type, "{$prefix}_{$this->key}", $args);
        if ($success) {
            // Register the ID field
            //
            $args['type'] = 'integer';
            $args['label'] .= ' Media ID';
            unset($args['default']);
            $success = $this->register_meta($object_type, "{$prefix}_{$this->key}_id", $args);
        }

        return $success;
    }
}
