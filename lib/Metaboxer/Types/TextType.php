<?php

namespace PublicFunction\Toolkit\Metaboxer\Types;


class TextType extends BaseType
{

    public function placeholder($value) {
        $this->field_attr['placeholder'] = $value;
    }

    protected function setup_register_args($args) {
        if ($this->type === 'number') {
            $type = 'integer';
            if (!empty($this->field_attr['step']) && !is_int($this->field_attr['step'])) {
                $type = 'number';
            }
            $args['type'] = $type;
            if (is_string($this->default)) {
                $this->default = $type == 'number' ? floatval($this->default) : intval($this->default);
            }
        }

        return parent::setup_register_args($args);
    }

}
