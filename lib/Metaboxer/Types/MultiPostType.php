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

    public function __construct($args)
    {
        parent::__construct($args);


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
        $args = [
            'post_type' => $this->post_type,
            'nopaging' => true,
            'orderby'   => 'post_title',
            'order'     => 'ASC'
        ];

        $post_query = new \WP_Query($args);
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
