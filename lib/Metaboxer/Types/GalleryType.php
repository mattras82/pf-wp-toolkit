<?php

namespace PublicFunction\Toolkit\Metaboxer\Types;


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

        $this->type_classes = Metaboxer::get_type_classes();
        $this->field_attr['type'] = 'hidden';
        $this->button_text = $this->button_text ?: 'panel';
        $this->set_fields();
    }

    /**
     * Sets the fields attribute of the class according to the
     * number of fields based on the meta of the post (if set & passed to the function) or the default
     */
    protected function set_fields() {
        $x = 0;
        if(!$this->field_attr['value'])
            $this->field_attr['value'] = ($this->default ? $this->default : 1);

        while($x < $this->field_attr['value']) {
            foreach ($this->fields as $field_key => $field) {
                $field_key = $field_key.'_'.$x;
                if (array_key_exists($field['type'], $this->type_classes)) {
                    $field['id'] = $this->id . '_' . $field_key;
                    if(strpos($this->name, '[')) {
                        $field['name'] = substr($this->name, 0, strlen($this->name) - 1).'_'.$field_key.']';
                    } else {
                        $field['name'] = $this->name . '_data[' . $field_key . ']';
                    }
                    $field['key'] = $this->key . '_' . $field_key;
                    $this->field_classes[$field_key] = new $this->type_classes[$field['type']]($field);
                } else {
                    $this->field_classes[$field_key] = null;
                }
            }
            $x++;
        }
    }

    /**
     * @inheritdoc
     */
    public function display($meta) {
        if (is_callable($this->add_callback)) {
            if (!call_user_func($this->add_callback, $this->callback_args))
                return '';
        }

        if (isset($meta[$this->key])) {
            $this->field_attr['value'] = $meta[$this->key];
            $this->set_fields();
        }

        if (isset($meta[$this->key.'_data']) && is_array($meta[$this->key.'_data'])) {
            foreach($meta[$this->key.'_data'] as $key => $value) {
                $meta[$this->key.'_'.$key] = $value;
            }
        }

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

        if($this->label)
            echo Markup::tag('span', [
                'for' => $this->id,
                'class' => ['field-label', 'field-label-' . $this->type]
            ], $this->label);

        echo $this->field_html();

        if($this->description)
            echo Markup::tag('p', ['class' => 'description'], $this->description);

        if (!$this->fixed)
            echo '<a class="button" href="javascript:void(0)" data-gallery-max="' . $this->max_items .'" data-gallery-id-add="'.$this->id.'">Add a '.$this->button_text.'</a>';

        echo '<ul class="tabs" data-pf-tabs id="'.$this->id.'-tabs">';
        foreach ($this->get_numbers() as $i => $num) {
            if ($i == $this->field_attr['value']) break;

            echo '<li class="tabs-title'.($i === 0 ? ' is-active' : '').'">';
            echo '<a href="#'.$this->id.'-tab-content-'.$i.'">'.$num.'</a></li>';
        }
        echo '</ul>';

        echo '<div class="tabs-contents" data-pf-tabs-content="'.$this->id.'-tabs">';
        $i = -1;
        foreach($this->field_classes as $key => $field) {
            if ($i != substr($key, -1)) {
                $new_tab = true;
                $i++;
                echo ($i > 0 ? '</div>' : '');
            } else {
                $new_tab = false;
            }
            if ($new_tab) {
                echo '<div id="'.$this->id.'-tab-content-'.$i.'" class="tabs-content'.($i === 0 ? ' is-active' : '').'">';
                if (!$this->fixed)
                    echo '<a href="javascript:void(0)" data-gallery-id-remove="'.$this->id.'" data-remove-num="'.$i.'" style="margin: 0.75rem 0;display: block;float: right;">Remove this '. $this->button_text .'</a>';
            }
            $field->display($meta);
        }

        echo '</div></div></div>';

        return true;
    }

    protected function get_numbers() {
        return ['One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen', 'Twenty'];
    }

}
