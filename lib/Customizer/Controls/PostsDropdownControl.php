<?php

namespace PublicFunction\Toolkit\Customizer\Controls;


class PostsDropdownControl extends BaseControl
{
    public $type = 'posts_dropdown';

    protected function render()
    {
        ?><li id="<?php echo $this->itemId(); ?>" class="<?php echo $this->itemCssClasses(); ?>">
        <?php $this->render_content(); ?>
        </li><?php
    }

    protected function render_content()
    {
        $items = [];
        foreach($this->options['post_type'] as $type) {
            if ($type === 'page') {
                foreach (get_pages() as $page) {
                    $items[$page->post_title] = get_the_permalink($page->ID);
                }
            } else {
                $args = ['post_type' => $type, 'nopaging' => true];
                $posts = new \WP_Query($args);
                if ($posts->have_posts()) {
                    foreach ($posts->posts as $post) {
                        $items[$post->post_title] = get_the_permalink($post->ID);
                    }
                }
            }
        }
        ksort($items);
        ?>
        <label for="">
            <?php $this->labelAndDescription() ?>
            <select <?php $this->link(); ?>>
                <option value="-1">Select a page</option>
                <?php foreach ($items as $label => $link) : ?>
                <option value="<?= $link ?>"><?= $label ?></option>
                <?php endforeach ?>
            </select>
        </label>
        <?php
    }

}
