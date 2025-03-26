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
        if (!$this->maybe_show($meta)) {
            return '';
        }

        if (!empty($meta[$this->key])) {
            if (is_array($meta[$this->key]) && in_array($this->value, $meta[$this->key])) {
                $this->field_attr['checked'] = 'checked';
            } elseif ($meta[$this->key] == $this->value) {
                $this->field_attr['checked'] = 'checked';
            }
        } else if ($this->default == $this->value && !isset($meta[$this->key])) {
            $this->field_attr['checked'] = 'checked';
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

        if ($this->description)
            echo Markup::tag('p', ['class' => 'description'], $this->description);

        echo '</div>';

        return true;
    }

    /**
     * @inheritdoc
     */
    protected function setup_register_args($args)
    {
        $args['type'] = 'boolean';
        $args['default'] = $this->default == $this->value;

        $args = parent::setup_register_args($args);

        if ($args['show_in_rest']) {
            $args['show_in_rest'] = [
                'prepare_callback'  => function ($value) {
                    if (is_array($value)) {
                        return in_array($this->value, $value);
                    } else if (!empty($value)) {
                        return $this->value == $value;
                    }
                    return $this->default == $this->value;
                }
            ];
        }

        return $args;
    }
}
