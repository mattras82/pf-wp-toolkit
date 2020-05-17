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
    protected $media_type;

    /**
     * @var string
     */
    protected $media_label;

    public function __construct($args)
    {
        parent::__construct($args);

        if (!$this->preview_size)
            $this->preview_size = 'medium';

        $this->media_type = 'image';
        $this->media_label = 'image';
    }

    /**
     * @inheritdoc
     */
    public function display($meta) {
        if (is_callable($this->add_callback)) {
            if (!call_user_func($this->add_callback, $this->callback_args))
                return '';
        }

        if (isset($meta[$this->key]))
            $this->field_attr['value'] = [
                'url' => $meta[$this->key],
                'id' => isset($this->field_attr['value']['id']) ? $this->field_attr['value']['id'] : ''
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


}
