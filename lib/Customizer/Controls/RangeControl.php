<?php

namespace PublicFunction\Toolkit\Customizer\Controls;

class RangeControl extends BaseControl
{
    protected function render_content()
    {
        $min = isset($this->options['min']) ? intval($this->options['min']) : 1;
        $max = isset($this->options['max']) ? intval($this->options['max']) : 100;
        ?>
        <label>
            <?php $this->labelAndDescription() ?>
            <span class="pfwp-customize-range-slider">
                <input type="range" min="<?php echo $min ?>" max="<?php echo $max ?>" step="1" <?php $this->link() ?> value="<?php $this->value() ?>">
                <input type="number" min="<?php echo $min ?>" max="<?php echo $max ?>" step="1"<?php $this->link() ?> value="<?php $this->value() ?>">
            </span>
        </label>
        <?php
    }
}
