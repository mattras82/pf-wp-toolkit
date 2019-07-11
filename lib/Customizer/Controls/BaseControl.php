<?php

namespace PublicFunction\Toolkit\Customizer\Controls;

abstract class BaseControl extends \WP_Customize_Control
{
    protected $prefix;
    public $options = [];

    public function __construct($manager, $id, $args = [])
    {
        if (isset($args['control_options']) && !empty($args['control_options'])) {
            $this->options = $args['control_options'];
        }

        parent::__construct($manager, $id, $args);

        if(!isset($this->options['item_classes'])) {
            $this->options['item_classes'] = [];
        }

        $this->prefix = strtolower(pf_toolkit('theme.short_name'));
        $this->options['item_classes'] = array_merge($this->options['item_classes'], [
            'pfwp-customize-control',
            'customize-control',
            'customize-control-' . $this->type,
        ]);
    }

    /**
     * Returns a list of classes to use with the control
     * @param array $more
     * @return string
     */
    protected function itemCssClasses($more = [])
    {
        return join(' ', array_map('esc_attr', array_merge($this->options['item_classes'], (array) $more)));
    }

    protected function itemId() {
        return esc_attr('customize-control-' .$this->id);
    }

    /**
     * Renders the control wrapper and calls $this->render_content() for the internals.
     *
     * @since 3.4.0
     */
    protected function render()
    {
        ?>
        <li id="<?php echo $this->itemId(); ?>" class="<?php echo $this->itemCssClasses(); ?>">
        <?php $this->render_content(); ?>
        </li><?php
    }

    /**
     * Prints out the label for the control as well as the description if it exists
     */
    protected function labelAndDescription()
    {
        if (!empty($this->label)) : ?>
            <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
        <?php endif;
        if (!empty($this->description)) : ?>
            <span class="description customize-control-description"><?php echo $this->description; ?></span>
        <?php endif;
    }
}
