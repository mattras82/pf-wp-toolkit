<?php

namespace PublicFunction\Toolkit\Customizer\Controls;

class SwitchControl extends BaseControl
{
    protected function render_content()
    {
        $on = isset($this->options['on_label']) ? $this->options['on_label'] : 'on';
        $off = isset($this->options['off_label']) ? $this->options['off_label'] : 'off';

        if ( ! empty( $this->description ) ) : ?>
            <span class="description customize-control-description"><?php echo $this->description; ?></span>
        <?php endif; ?>
        <span class="pfwp-customize-switch-wrap">
            <label>
                <span class="switch">
                    <input type="checkbox" <?php $this->link() ?> value="1">
                    <span class="switch-bg">
                        <span class="switch-on"><?php echo $on ?></span>
                        <span class="switch-off"><?php echo $off ?></span>
                        <span class="switch-handle"></span>
                    </span>
                </span>
                <?php if ( ! empty( $this->label ) ) : ?>
                    <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
                <?php endif ?>
            </label>
        </span>
        <?php
    }
}
