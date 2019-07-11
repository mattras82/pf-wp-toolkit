<?php

namespace PublicFunction\Toolkit\Customizer\Controls;


class PagesArchivesDropdownControl extends BaseControl
{
    public $type = 'pages_archives_dropdown';

    protected function render()
    {
        ?><li id="<?php echo $this->itemId(); ?>" class="<?php echo $this->itemCssClasses(); ?>">
        <?php $this->render_content(); ?>
        </li><?php
    }

    protected function render_content()
    {
        $options = [];
        foreach (get_pages() as $page) {
            $options[$page->post_title] = get_the_permalink($page->ID);
        }
        $post_types = get_post_types(['public' => true], 'object');
        foreach ($post_types as $type) {
            if ($type->name === 'page' || $type->name === 'media') continue;
            $options[$type->label . ' Page'] = get_post_type_archive_link($type->name);
        }
        ksort($options);
        ?>
        <label for="">
            <?php $this->labelAndDescription() ?>
            <select <?php $this->link(); ?>>
                <option value="-1">Select a page</option>
                <?php foreach ($options as $label => $link) : ?>
                <option value="<?= $link ?>"><?= $label ?></option>
                <?php endforeach ?>
            </select>
        </label>
        <?php
    }

}
