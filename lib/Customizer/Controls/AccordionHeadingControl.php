<?php

namespace PublicFunction\Toolkit\Customizer\Controls;

class AccordionHeadingControl extends BaseControl
{
    protected function render()
    {
        ?>
        <li id="<?php echo $this->itemId(); ?>" class="<?php echo $this->itemCssClasses('pfwp-customize-accordion-heading'); ?>">
            <?php if (!empty($this->label)): ?>
                <span class="pfwp-accordion-heading">
                <?php echo $this->label ?>
                    <span class="pfwp-accordion-handle">
                    <button type="button" class="button-link item-edit" aria-expanded="false">
                        <span class="screen-reader-text">Edit menu item: Resources (Page)</span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                </span>
            </span>
            <?php endif ?>
        </li>
        <?php
    }
}
