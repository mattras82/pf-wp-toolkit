<?php

namespace PublicFunction\Toolkit\Customizer\Controls;

class InlineControl extends BaseControl
{
    public $type = 'number';

    protected function render()
    {
        $styles = [
            'display:inline-block',
            'clear:none',
            'max-width:' . intval($this->options['width']) . '%'
        ];
        ?>
        <li id="<?php echo $this->itemId(); ?>" class="<?php echo $this->itemCssClasses(); ?>" style="<?php echo join(";", $styles)?>">
            <?php $this->render_content(); ?>
        </li>
        <?php
    }

    public function render_content()
    {
        $labelStyles = [
            'font-size:11px',
            'height:1rem',
            'line-height:1.1',
        ]?>
        <label class="inline">
            <?php if ( ! empty( $this->label ) ) : ?>
                <span class="customize-control-title" style="<?php echo join(';', $labelStyles) ?>"><?php echo esc_html( $this->label ); ?></span>
            <?php endif; ?>
            <input type="<?php echo $this->type ?>" <?php $this->link(); ?> value="<?php echo $this->value(); ?>" />
        </label>
        <?php
    }
}
