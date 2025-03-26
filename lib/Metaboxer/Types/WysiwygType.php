<?php

namespace PublicFunction\Toolkit\Metaboxer\Types;


class WysiwygType extends TextareaType
{
    /**
     * @inheritdoc
     */
    public function display($meta)
    {
        if (!$this->maybe_show($meta)) {
            return '';
        }

        $contents = isset($meta[$this->key]) ? $meta[$this->key] : $this->default;

        $this->display_field();

        wp_editor($contents, $this->id, ['textarea_name' => $this->name]);

        return true;
    }

    public function field_html()
    {
        return '';
    }
}
