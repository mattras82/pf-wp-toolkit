<?php

namespace PublicFunction\Toolkit\Metaboxer\Types;

use PublicFunction\Toolkit\Assets\Helpers;
use PublicFunction\Toolkit\Metaboxer\Metaboxer;
use PublicFunction\Toolkit\Core\Markup;

class GalleryType extends BaseType
{
    /**
     * Holds the fields for this Gallery instance
     * @var array
     */
    protected $fields;

    /**
     * Gets all of the type classes from the Metaboxer
     * @var array
     */
    protected $type_classes;

    /**
     * Holds the instantiated type classes for each field
     * @var array
     */
    protected $field_classes;

    /**
     * Optional field to hold the button text
     * @var string
     */
    protected $button_text;

    /**
     * Optional field that tells the JavaScript how many items this gallery supports
     * @var int
     */
    protected $max_items = 10;

    /**
     * Sets the amount of gallery items as fixed. Removes the buttons to add/remove items.
     * @var bool
     */
    protected $fixed = false;

    public function __construct($args)
    {
        parent::__construct($args);

        $this->field_attr['type'] = 'hidden';
        $this->button_text = $this->button_text ?: 'panel';
    }

    /**
     * Sets the fields attribute of the class according to the
     * number of fields based on the meta of the post (if set & passed to the function) or the default
     */
    protected function set_fields()
    {
        $x = 0;
        $this->type_classes = Metaboxer::get_type_classes();
        if (!$this->field_attr['value'])
            $this->field_attr['value'] = ($this->default ? $this->default : 1);

        if (is_string($this->fields)) {
            $helper = new Helpers();
            $this->fields = $helper->shortcodeOrCallback($this->fields);
        }
        if (is_array($this->fields)) {
            while ($x < $this->field_attr['value']) {
                foreach ($this->fields as $field_key => $field) {
                    $this->set_field($field_key, $field, $x);
                }
                $x++;
            }
        }
    }

    protected function set_field($key, $field, $x)
    {
        $key = $key . '_' . $x;
        if (array_key_exists($field['type'], $this->type_classes)) {
            $field['id'] = $this->id . '_' . $key;
            if (strpos($this->name, '[')) {
                $field['name'] = substr($this->name, 0, strlen($this->name) - 1) . '_' . $key . ']';
            } else {
                $field['name'] = $this->name . '_data[' . $key . ']';
            }
            $field['key'] = $this->key . '_' . $key;
            $this->field_classes[$key] = new $this->type_classes[$field['type']]($field);
        } else {
            $this->field_classes[$key] = null;
        }
    }

    /**
     * @inheritdoc
     */
    public function display($meta)
    {
        if (is_callable($this->add_callback)) {
            if (!call_user_func($this->add_callback, $this->callback_args))
                return '';
        }

        if (isset($meta[$this->key])) {
            $this->field_attr['value'] = $meta[$this->key];
        }

        if (isset($meta[$this->key . '_data']) && is_array($meta[$this->key . '_data'])) {
            foreach ($meta[$this->key . '_data'] as $key => $value) {
                $meta[$this->key . '_' . $key] = $value;
            }
        }

        $this->set_fields();

        return $this->display_field($meta);
    }


    /**
     * Needs to accept the meta for the post since it has to pass it to the fields instances
     * @param array $meta
     * @return boolean
     */
    protected function display_field($meta = array())
    {
        echo $this->display_field_wrap();

        if ($this->label)
            echo Markup::tag('span', [
                'for' => $this->id,
                'class' => ['field-label', 'field-label-' . $this->type]
            ], $this->label);

        echo $this->field_html();

        if ($this->description)
            echo Markup::tag('p', ['class' => 'description'], $this->description);

        if (!$this->fixed)
            echo '<a class="button" href="javascript:void(0)" data-gallery-max="' . $this->max_items . '" data-gallery-id-add="' . $this->id . '">Add ' . (array_search(substr($this->button_text, 0, 1), ['a','e','i']) !== false ? 'an ' : 'a ') . $this->button_text . '</a>';

        $this->display_tabs();

        echo '<div class="tabs-contents" data-pf-tabs-content="' . $this->id . '-tabs">';
        $i = -1;
        foreach ($this->field_classes as $key => $field) {
            if ($i != substr($key, (strlen($i) * -1))) {
                $new_tab = true;
                $i++;
                echo ($i > 0 ? '</div>' : '');
            } else {
                $new_tab = false;
            }
            if ($new_tab) {
                echo '<div id="' . $this->id . '-tab-content-' . $i . '" class="tabs-content' . ($i === 0 ? ' is-active' : '') . '">';
                if (!$this->fixed)
                    echo '<a href="javascript:void(0)" data-gallery-id-remove="' . $this->id . '" data-remove-num="' . $i . '" style="margin: 0.75rem 0;display: block;float: right;">Remove this ' . $this->button_text . '</a>';
            }

            $field->display($meta);
        }

        echo '</div></div></div>';

        return true;
    }

    protected function display_tabs()
    {
        echo '<ul class="tabs" data-pf-tabs id="' . $this->id . '-tabs">';
        $numberWords = $this->get_numbers();
        $i = 0;
        do {
            echo '<li class="tabs-title' . ($i === 0 ? ' is-active' : '') . '">';
            echo '<a href="#' . $this->id . '-tab-content-' . $i . '">';
            $title = !empty($numberWords[$i]) ? $numberWords[$i] : $i + 1;
            echo apply_filters('pf_metaboxer_gallery_tab_title', $title, $this->name, $i);
            echo '</a></li>';
            $i++;
        } while ($i < $this->field_attr['value']);
        echo '</ul>';
    }

    protected function get_numbers()
    {
        return ['One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen', 'Twenty', 'Twenty One', 'Twenty Two', 'Twenty Three', 'Twenty Four', 'Twenty Five', 'Twenty Six', 'Twenty Seven', 'Twenty Eight', 'Twenty Nine', 'Thirty'];
    }
}
