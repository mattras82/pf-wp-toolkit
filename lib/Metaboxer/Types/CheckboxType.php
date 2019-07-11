<?php

namespace PublicFunction\Toolkit\Metaboxer\Types;


use PublicFunction\Toolkit\Core\Markup;

class CheckboxType extends BaseType
{
    /**
     * Used to set the value of the checkbox, if applicable.
     * @var string
     */
    public $value;

    /**
     * Needs a variable separate from $default for this behavior to work correctly
     * @var boolean
     */
    protected $checked_by_default;

    public function __construct($args)
    {
        parent::__construct($args);

        if (!$this->value)
            $this->value = 'on';
    }

    /**
     * @inheritdoc
     */
    public function display($meta)
    {
        if (is_callable($this->add_callback)) {
            if (!call_user_func($this->add_callback, $this->callback_args))
                return '';
        }

        if ((isset($meta[$this->key]) && !empty($meta[$this->key])) || $this->default) {
            if (is_array($meta[$this->key]) && in_array($this->value, $meta[$this->key])) {
                $this->field_attr['checked'] = 'checked';
            } elseif ($meta[$this->key] == $this->value || ($this->default == $this->value && !isset($meta[$this->key]))) {
                $this->field_attr['checked'] = 'checked';
            }
        }
        $this->field_attr['value'] = $this->value;

        return $this->display_field();
    }

    /**
     * @inheritdoc
     */
    public function display_field()
    {
        echo $this->display_field_wrap();

        echo Markup::tag('label', [
            'for' => $this->id,
            'class' => ['field-label', 'field-label-' . $this->type]
        ], $this->field_html() . Markup::tag('span', [], $this->label));

        if($this->description)
            echo Markup::tag('p', ['class' => 'description'], $this->description);

        echo '</div>';

        return true;
    }

}
