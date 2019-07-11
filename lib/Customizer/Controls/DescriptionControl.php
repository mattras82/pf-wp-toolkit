<?php

namespace PublicFunction\Toolkit\Customizer\Controls;

class DescriptionControl extends BaseControl
{
    public function render_content()
    {
        if(!empty($this->label)) :?>
            <strong><?php echo esc_html($this->label) ?></strong>
        <?php endif; if ( ! empty( $this->description ) ) : ?>
            <span class="description customize-control-description"><?php echo $this->description; ?></span>
        <?php endif;
    }
}
