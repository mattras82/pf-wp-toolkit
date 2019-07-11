<?php

namespace PublicFunction\Toolkit\Customizer\Controls;

class LayoutsControl extends BaseControl
{
    protected function render_content()
    {
        $name = '_customize-radio-' . $this->id;
        $this->labelAndDescription();

        if(empty($this->choices)) {
            $this->choices = array(
                'empty' => 'Empty (no sidebars, no rails)',
                'fullwidth' => 'Full Width (No sidebars)',
                'left-sidebar' => '2 Column with Left Sidebar',
                'right-sidebar' => '2 Column with Right Sidebar',
            );
        }
        ?>
        <ul class="pfwp-customize-layout-options">
            <?php foreach($this->choices as $value => $text):?>
                <li>
                    <label>
                        <input type="radio" value="<?php echo esc_attr( $value ); ?>" name="<?php echo esc_attr( $name ); ?>" <?php $this->link(); checked( $this->value(), $value ); ?> />
                        <span class="pfwp-customize-layout-option <?php echo $value ?>">&nbsp;</span>
                        <span class="pfwp-customize-layout-option-label"><?php echo $text ?></span>
                    </label>
                </li>
            <?php endforeach ?>
        </ul>
        <?php
    }
}
