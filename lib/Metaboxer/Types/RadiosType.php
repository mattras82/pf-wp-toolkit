<?php

namespace PublicFunction\Toolkit\Metaboxer\Types;


class RadiosType extends CheckboxesType
{

    protected function set_child_types() {
        $this->child_type = CheckboxType::class;
        $this->child_input_type = 'radio';
    }

}
