<?php

namespace PublicFunction\Toolkit\Metaboxer\Types;

use PublicFunction\Toolkit\Assets\Helpers;
use PublicFunction\Toolkit\Core\Markup;

abstract class BaseType
{
    /**
     * This is the name of the field that the Metabox class uses to identify this instance.
     * The value is processed by the Metabox class.
     * @var string
     */
    protected $name;

    /**
     * This is the HTML id of the input field. Processed by the Metabox class.
     * @var string
     */
    protected $id;

    /**
     * This is the key of this field. Used by pf_meta("{metabox_key}.{field_key}")
     * @var string
     */
    protected $key;

    /**
     * This is the type attribute of the input field.
     * @var string
     */
    protected $type;

    /**
     * This is the default value for the field
     * @var mixed
     */
    public $default;

    /**
     * Label for the input
     * @var string
     */
    public $label = 'Input Label';

    /**
     * Description for the input
     * @var string
     */
    public $description;

    /**
     * Used in the field_html function to add HTML attributes
     * to the input field
     * @var array
     */
    protected $field_attr;

    /**
     * Name of a function to run when displaying the field. If the function returns false,
     * the field is not displayed in the admin.
     * @var string
     */
    protected $add_callback;

    /**
     * Argument passed to the function named in add_callback
     * @var mixed
     */
    protected $callback_args;

    /**
     * This holds a string that is set to a [data] attribute on the field for an AJAX refresh action handled through JS
     * @var string
     */
    protected $refresh_on;

    /**
     * Adds the HTML 5 required attribute
     * @var boolean
     */
    protected $required;

    public function __construct($args) {

        // Go through each property and set them as part of this
        // class instance
        foreach($args as $property => $value) {
            if($value === null)
                continue;
            elseif(method_exists($this, $property))
                $this->{$property}($value);
            elseif(property_exists($this, $property))
                $this->{$property} = $value;
        }

        // Process a shortcode or callback in the default field
        // See inc\lib\Assets\Helpers.php for available methods
        $helper = new Helpers();
        $this->default = $helper->shortcodeOrCallback($this->default);
        // Initializes the base HTML attributes for the input field
        $this->field_attr['type'] = $this->type;
        $this->field_attr['id'] = $this->id;
        $this->field_attr['name'] = $this->name;
        $this->field_attr['value'] = $this->default;
        if ($this->required)
            $this->field_attr['required'] = 'required';
        if ($this->refresh_on)
            $this->field_attr['data-refresh-on'] = $this->refresh_on;
    }

    /**
     * This function handles how each Metabox type will be displayed.
     * Returns the HTML as a string to be displayed in the admin.
     * @return string
     */
    public function display($meta) {
        if (is_callable($this->add_callback)) {
            if (!call_user_func($this->add_callback, $this->callback_args))
                return '';
        }

        if (isset($meta[$this->key]))
            $this->field_attr['value'] = $meta[$this->key];

        return $this->display_field();
    }

    /**
     * Wraps a field with a div.field element
     * @return string
     */
    protected function display_field_wrap() {
        return '<div class="field field-'.$this->type.'">';
    }


    /**
     * Main field display adding a label and description
     * @return string
     */
    protected function display_field()
    {
        echo $this->display_field_wrap();

        if($this->label)
            echo Markup::tag('label', [
                'for' => $this->id,
                'class' => ['field-label', 'field-label-' . $this->type]
            ], $this->label);

        echo $this->field_html();

        if($this->description)
            echo Markup::tag('p', ['class' => 'description'], $this->description);

        echo '</div>';

        return true;
    }

    /**
     * Generates the actual input HTML tag
     * @return string
     */
    protected function field_html() {
        return Markup::tag('input', $this->field_attr);
    }
}
