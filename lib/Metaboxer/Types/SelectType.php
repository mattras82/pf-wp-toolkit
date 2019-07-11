<?php

namespace PublicFunction\Toolkit\Metaboxer\Types;


use PublicFunction\Toolkit\Core\Markup;

class SelectType extends CheckboxesType
{
    /**
     * Used to prepend the options list with a disabled option
     * @var string
     */
    protected $placeholder;

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
        if ($this->placeholder)
            $options .= Markup::tag('option', ['disabled' => 'disabled', 'selected' => 'selected'], $this->placeholder);

        foreach($this->children as $child) {
            $attr = [
                'value' => $child['value']
            ];
            if ((isset($meta[$this->key]) && $meta[$this->key] == $child['value']) || $this->default == $child['value']) {
                $attr['selected'] = 'selected';
            }
            $options .= Markup::tag('option', $attr, $child['label']);
        }

        echo Markup::tag('select', [
            'id' => $this->id,
            'name' => $this->name
        ], $options);

        if($this->description)
            echo Markup::tag('p', ['class' => 'description'], $this->description);

        echo '</div>';

        return true;

    }

}
