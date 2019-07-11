<?php

namespace PublicFunction\Toolkit\Customizer\Controls;

use WP_Query;

class CF7DropdownControl extends BaseControl
{
    protected function render_content()
    {
        ?>
        <label>
            <?php $this->labelAndDescription() ?>
            <select <?php $this->link() ?>>
                <option value="-1">Select a CF7 Contact Form</option>
                <?php foreach($this->getOptions() as $value => $text) : ?>
                    <option value="<?php echo esc_attr($value) ?>"><?php echo $text ?></option>
                <?php endforeach ?>
            </select>
        </label>
        <?php
    }

    /**
     * Returns an array of ids and names for the dropdown options
     * @return array
     */
    private function getOptions()
    {
        $options = array();
        $query = new WP_Query(array(
            'post_type' => 'wpcf7_contact_form',
            'posts_per_page' => -1,
        ));
        if($query->have_posts()) {
            while($query->have_posts()) {
                $query->the_post();
                $post = $query->post;
                $options[$post->ID] = $post->post_title;
            }
        }
        wp_reset_query();
        return $options;
    }
}
