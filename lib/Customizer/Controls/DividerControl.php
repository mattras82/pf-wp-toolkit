<?php

namespace PublicFunction\Toolkit\Customizer\Controls;

class DividerControl extends BaseControl
{
    public function render_content()
    {
        ?>
        <hr style="border-bottom: none; margin: 10px -12px 5px;"/><?php
    }
}
