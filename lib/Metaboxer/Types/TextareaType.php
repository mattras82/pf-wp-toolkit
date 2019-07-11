<?php

namespace PublicFunction\Toolkit\Metaboxer\Types;

use PublicFunction\Toolkit\Core\Markup;

class TextareaType extends TextType
{
    /**
     * @inheritdoc
     */
    public function field_html()
    {
        $contents = (isset($this->field_attr['value']) ? $this->field_attr['value'] : $this->default);
        unset($this->field_attr['value']);
        unset($this->field_attr['type']);
        return Markup::tag('textarea', $this->field_attr, $contents);
    }

}
