<?php

namespace PublicFunction\Toolkit\Metaboxer\Types;


class DateType extends TextType
{

    public function __construct($args)
    {
        parent::__construct($args);

        $this->placeholder('yyyy-mm-dd');
    }

}
