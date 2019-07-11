<?php

namespace PublicFunction\Toolkit\Metaboxer\Types;

use PublicFunction\Toolkit\Core\Markup;
use \WP_Query;

class PostType extends SelectType
{
    /**
     * Holds either a string or an array of strings that dictate the post type(s)
     * available for this field.
     * @var mixed
     */
    protected $post_type;

    public function __construct($args)
    {
        parent::__construct($args);


    }

    /**
     * @inheritdoc
     */
    public function set_children()
    {
        $args = [
            'post_type' => $this->post_type,
            'nopaging' => true,
            'orderby'   => 'post_title',
            'order'     => 'ASC'
        ];

        $post_query = new WP_Query($args);

        $this->children = (!empty($post_query->posts) ? $post_query->posts : []);
    }

    /**
     * @inheritdoc
     */
    public function display_field($meta = [])
    {
        echo $this->display_field_wrap();

        if ($this->label)
            echo Markup::tag('label', ['class' => ['field-label', 'field-label-' . $this->type]], $this->label);

        $options = '';
        if ($this->placeholder)
            $options .= Markup::tag('option', ['disabled' => 'disabled', 'selected' => 'selected'], $this->placeholder);

        foreach($this->children as $child) {
            $attr = [
                'value' => $child->ID
            ];
            if ((isset($meta[$this->key]) && $meta[$this->key] == $child->ID) || $this->default == $child->ID) {
                $attr['selected'] = 'selected';
            }
            $options .= Markup::tag('option', $attr, $child->post_title);
        }

        unset($this->field_attr['value']);
        unset($this->field_attr['type']);

        echo Markup::tag('select', $this->field_attr, $options);

        if($this->description)
            echo Markup::tag('p', ['class' => 'description'], $this->description);

        echo '</div>';

        return true;

    }

}
