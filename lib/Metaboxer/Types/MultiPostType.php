<?php

namespace PublicFunction\Toolkit\Metaboxer\Types;

use \WP_Query;


class MultiPostType extends CheckboxesType
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

    /**
     * MultiPostType constructor.
     * @param $args
     */
    public function __construct($args)
    {
        parent::__construct($args);

        if (empty($this->query)) {
            $this->query = [
                'post_type' => $this->post_type,
                'nopaging'  => true,
                'orderby'   => $this->orderby,
                'order'     => $this->order
            ];
            $this->children = [];
            $this->set_children();
        } elseif (!isset($this->query['post_type'])) {
            $this->query['post_type'] = $this->post_type;
            $this->query['orderby'] = isset($this->query['orderby']) ? $this->query['orderby'] : $this->orderby;
            $this->query['order'] = isset($this->query['order']) ? $this->query['order'] : $this->order;
            $this->children = [];
            $this->set_children();
        }
    }

    /**
     * Checks for sequential ids and changes the keys to use the value. this is meant
     * to be used with select, multi-checkbox and multi-radio fields.
     * @return array
     */
    protected function associativeOptions()
    {
        $output = [];
        $i = 0;

        if (empty($this->query)) return $output;

        $post_query = new WP_Query($this->query);
        foreach($post_query->posts as $option) {
            $output[$this->id.'_'.$i] = array(
                'label' => $option->post_title,
                'value' => $option->ID
            );
            $i++;
        }

        return $output;
    }


}
