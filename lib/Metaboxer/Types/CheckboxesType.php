<?php

namespace PublicFunction\Toolkit\Metaboxer\Types;


use PublicFunction\Toolkit\Core\Markup;

class CheckboxesType extends BaseType
{
    /**
     * Holds the value & label for each checkbox.
     * @var array
     */
    protected $options;

    /**
     * Holds the name of a function that will return the array of options for this input
     * @var string
     */
    protected $optionsFunction;

    /**
     * Holds the type class of each option (checkbox, radio, etc)
     * @var BaseType
     */
    protected $child_type;

    /**
     * Holds the input type of each option (checkbox, radio, etc)
     * @var string
     */
    protected $child_input_type;

    /**
     * Holds the instances of the child_type class for the instance
     * @var array
     */
    protected $children = [];

    public function __construct($args)
    {
        parent::__construct($args);

        if (isset($this->optionsFunction) && is_callable($this->optionsFunction)) {
            $this->options = call_user_func($this->optionsFunction);
        }

        $this->set_child_types();
        $this->set_children();
    }

    protected function set_child_types()
    {
        $this->child_type = CheckboxType::class;
        $this->child_input_type = 'checkbox';
    }

    protected function set_children()
    {
        foreach ($this->associativeOptions() as $id => $option) {
            $option['id'] = $id;
            $option['name'] = $this->name . ($this->child_input_type === 'checkbox' ? '[]' : '');
            $option['key'] = $this->key;
            $option['type'] = $this->child_input_type;
            $option['default'] = $this->default;
            $this->children[] = new $this->child_type($option);
        }
    }

    /**
     * @inheritdoc
     */
    public function display($meta)
    {
        if (!$this->maybe_show($meta)) {
            return '';
        }

        return $this->display_field($meta);
    }

    /**
     * @param array $meta
     * @return string
     */
    public function display_field($meta = [])
    {
        echo $this->display_field_wrap();

        if ($this->label)
            echo Markup::tag('h4', ['class' => ['field-label', 'field-label-' . $this->type]], $this->label);

        if ($this->description)
            echo Markup::tag('p', ['class' => 'description'], $this->description);

        foreach ($this->children as $child) {
            $child->display($meta);
        }

        echo '</div>';

        return true;
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
        $associative = array_keys($this->options) !== range(0, count($this->options) - 1);
        foreach ($this->options as $key => $option) {
            if (is_array($option)) {
                $output[$this->id . '_' . $i] = $option;
            } else {
                $value = [
                    'label' => $option
                ];
                if ($associative) {
                    $value['value'] = $key;
                } else {
                    $value['value'] = 'val_' . $i;
                }

                $output[$this->id . '_' . $i] = $value;
            }
            $i++;
        }

        return $output;
    }

    /**
     * @inheritdoc
     */
    protected function setup_register_args($args)
    {
        if ($this->child_input_type === 'checkbox') {
            $args['type'] = 'array';
            $args['default'] = (array) $this->default;

            if (empty($this->hide_from_rest)) {
                $args['show_in_rest'] = [
                    'schema'    => [
                        'items' => [
                            'type'   => [
                                'string',
                                'number',
                                'boolean'
                            ]
                        ]
                    ]
                ];
            }
        }

        return parent::setup_register_args($args);
    }
}
