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

    /**
     * Order By string for WP Query
     * @var string
     */
    protected $orderby = 'post_title';

    /**
     * Order string for WP Query
     * @var string
     */
    protected $order = 'ASC';

    /**
     * Associative array object for the WP_Query to generate the posts
     * @var array
     */
    protected $query = [];

    public function __construct($args)
    {
        parent::__construct($args);


    }

    /**
     * @inheritdoc
     */
    public function set_children()
    {
        if (empty($this->query)) {
            $this->query = [
                'post_type' => $this->post_type,
                'nopaging'  => true,
                'orderby'   => $this->orderby,
                'order'     => $this->order
            ];
        } elseif (!isset($this->query['post_type'])) {
            $this->query['post_type'] = $this->post_type;
            $this->query['orderby'] = isset($this->query['orderby']) ? $this->query['orderby'] : $this->orderby;
            $this->query['order'] = isset($this->query['order']) ? $this->query['order'] : $this->order;
        }

        $post_query = new WP_Query($this->query);

        $this->children = (!empty($post_query->posts) ? $post_query->posts : []);
    }

    /**
     * @inheritdoc
     */
    public function get_child_markup($child, $meta) {
        $attr = [
            'value' => $child->ID
        ];
        if ((isset($meta[$this->key]) && $meta[$this->key] == $child->ID) || (!isset($meta[$this->key]) && $this->default == $child->ID)) {
            $attr['selected'] = 'selected';
        }
        return Markup::tag('option', $attr, $child->post_title);
    }

}
