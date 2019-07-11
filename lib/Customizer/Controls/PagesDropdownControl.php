<?php

namespace PublicFunction\Toolkit\Customizer\Controls;

use Walker_PageDropdown;

class PagesDropdownControl extends BaseControl
{
    public $type = 'pages_dropdown';

    protected function render()
    {
        ?><li id="<?php echo $this->itemId(); ?>" class="<?php echo $this->itemCssClasses(); ?>">
        <?php $this->render_content(); ?>
        </li><?php
    }

    protected function render_content()
    {
        $walker = new Walker_PageDropdown();
        ?>
        <label for="">
            <?php $this->labelAndDescription() ?>
            <select <?php $this->link(); ?>>
                <option value="-1">Select a page</option>
                <?php echo $walker->walk(get_pages(), 0) ?>
            </select>
        </label>
        <?php
    }
}
