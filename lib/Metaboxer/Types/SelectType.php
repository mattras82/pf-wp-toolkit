<?php

namespace PublicFunction\Toolkit\Metaboxer\Types;


use PublicFunction\Toolkit\Core\Markup;

class SelectType extends CheckboxesType
{
    /**
     * Used to prepend the options list with a disabled option
     * @var string
     */
    protected $placeholder = 'Select an option';

    /**
     * @inheritdoc
     */
    public function set_child_types()
    {
        return true; //This function not needed
    }

    /**
     * @inheritdoc
     */
    public function set_children()
    {
        foreach ($this->associativeOptions() as $id => $option) {
            $this->children[] = $option;
        }
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
        if ($this->placeholder) {
            $atts = ['value' => ''];
            if ($this->required) {
                $atts['disabled'] = 'disabled';
                $atts['selected'] = 'selected';
            }
            $options .= Markup::tag('option', $atts, $this->placeholder);
        }

        foreach($this->children as $child) {
            $options .= $this->get_child_markup($child, $meta);
        }

        unset($this->field_attr['value']);
        unset($this->field_attr['type']);

        echo Markup::tag('select', $this->field_attr, $options);

        if($this->description)
            echo Markup::tag('p', ['class' => 'description'], $this->description);

        echo '</div>';

        return true;

    }


    /**
     * Generates the HTML markup string for the given child option
     *
     * @param  array $child The child object to render
     * @param  array $meta The meta values for the current post
     * @return string The HTML markup for the child
     */
    public function get_child_markup($child, $meta) {
        $attr = [
            'value' => $child['value']
        ];
        if ((isset($meta[$this->key]) && $meta[$this->key] == $child['value']) || (!isset($meta[$this->key]) && $this->default == $child['value'])) {
            $attr['selected'] = 'selected';
        }
        return Markup::tag('option', $attr, $child['label']);
    }

}
