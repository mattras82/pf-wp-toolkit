<?php

namespace PublicFunction\Toolkit\Customizer\Controls;

class TextareaControl extends BaseControl
{
    public $type = 'textarea';
    public $js_callback = '';

    protected function render()
    {
        ?>
        <li id="<?php echo $this->itemId() ?>" class="<?php echo $this->itemCssClasses()?>">
            <?php $this->render_content(); ?>
        </li>
        <?php
    }

    protected function render_content()
    {
        ?>
        <label><?php $this->labelAndDescription() ?></label>
        <?php
        $editor_id = $this->settings['default']->id;
        $this->filterEditorSettingLink();
        wp_editor($this->value(), str_replace(array('[',']'), '_', $editor_id), array(
            'textarea_name' => $editor_id,
            'teeny'         => true,
        ));

        if(did_action('admin_footer') === 0) {
            do_action( 'admin_footer' );
        }
        do_action( 'admin_print_footer_scripts' );
    }

    private function filterEditorSettingLink() {
        add_filter( 'the_editor', function( $output ) { return preg_replace( '/<textarea/', '<textarea ' . $this->get_link(), $output, 1 ); } );
    }
}
