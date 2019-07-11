<?php

namespace PublicFunction\Toolkit\Customizer\Controls;

class TitleControl extends BaseControl
{
    public function render_content()
    {
        $styles = [
            'display: block',
            'font-size: 14px',
            'border-bottom: 1px solid rgba(0,0,0,0.1)',
            'margin: 0 -12px 0',
            'text-transform: uppercase',
            'padding: 5px 12px 7px',
        ];
        ?><h4 style="<?php echo join(';', $styles) ?>"><?php echo esc_html($this->label); ?></h4><?php
    }
}
