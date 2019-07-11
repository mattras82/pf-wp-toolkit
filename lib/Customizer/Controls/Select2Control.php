<?php

namespace PublicFunction\Toolkit\Customizer\Controls;

use PublicFunction\Toolkit\Customizer\Helpers\GoogleFonts;
use PublicFunction\Toolkit\Plugin;

class Select2Control extends BaseControl
{
    public $type = 'select2';

    protected function render()
    {
        ?><li id="<?php echo $this->itemId(); ?>" class="<?php echo $this->itemCssClasses( $this->prefix . 'customize-control-select2' ); ?>">
        <?php $this->render_content(); ?>
        </li><?php
    }

    protected function render_content()
    {
        $placeholder = isset($this->options['placeholder']) ? $this->options['placeholder'] : 'Select one';
        $sid = 'pfwp-customize-select2-'. $this->id;

        if($this->options['google_fonts']) {
            $this->choices = [];
            $googleFonts = new GoogleFonts(Plugin::getInstance()->container());

            foreach($googleFonts->retrieve() as $font) {
                $this->choices[$font['family']] = $font['family'];
            }
        }

        ?>
        <label>
            <?php $this->labelAndDescription() ?>
            <select <?php $this->link() ?> id="<?php echo $sid ?>" class="select2" data-select2-placeholder="<?php echo $placeholder ?>">
                <?php foreach($this->choices as $value => $text): ?>
                    <option value="<?php echo $value ?>"><?php echo $text ?></option>
                <?php endforeach ?>
            </select>
        </label>
        <?php
    }
}
