<?php

namespace PublicFunction\Toolkit\Customizer\Controls;

class TextControl extends BaseControl
{
    public $type = 'text';
    protected function render_content()
    {
        ?>
        <label>
            <?php $this->labelAndDescription() ?>
            <input type="text" <?php $this->input_attrs(); ?> value="<?php echo esc_attr( $this->value() ); ?>" <?php $this->link(); ?> />
        </label>
        <?php
    }
}
