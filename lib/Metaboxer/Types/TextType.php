<?php

namespace PublicFunction\Toolkit\Metaboxer\Types;


class TextType extends BaseType
{

    public function placeholder($value) {
        $this->field_attr['placeholder'] = $value;
    }

}
