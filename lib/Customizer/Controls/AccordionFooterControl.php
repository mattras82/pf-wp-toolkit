<?php

namespace PublicFunction\Toolkit\Customizer\Controls;

class AccordionFooterControl extends BaseControl
{
    protected function render()
    {
        ?>
        <li id="<?php echo $this->itemId() ?>" class="<?php echo $this->itemCssClasses('pfwp-customize-accordion-footer'); ?>" data-pfwp-customize-accordion>
            &nbsp;</li>
        <?php
    }
}
