<?php

namespace PublicFunction\Toolkit\Customizer;

use PublicFunction\Toolkit\Assets\Helpers;
use PublicFunction\Toolkit\Core\Container;
use PublicFunction\Toolkit\Core\DotNotation;
use PublicFunction\Toolkit\Core\RunableAbstract;
use PublicFunction\Toolkit\Customizer\Controls\AccordionFooterControl;
use PublicFunction\Toolkit\Customizer\Controls\AccordionHeadingControl;
use PublicFunction\Toolkit\Customizer\Controls\AdminScriptsControl;
use PublicFunction\Toolkit\Customizer\Controls\CF7DropdownControl;
use PublicFunction\Toolkit\Customizer\Controls\DescriptionControl;
use PublicFunction\Toolkit\Customizer\Controls\DividerControl;
use PublicFunction\Toolkit\Customizer\Controls\InlineControl;
use PublicFunction\Toolkit\Customizer\Controls\LayoutsControl;
use PublicFunction\Toolkit\Customizer\Controls\PagesArchivesDropdownControl;
use PublicFunction\Toolkit\Customizer\Controls\PagesDropdownControl;
use PublicFunction\Toolkit\Customizer\Controls\PostsDropdownControl;
use PublicFunction\Toolkit\Customizer\Controls\RangeControl;
use PublicFunction\Toolkit\Customizer\Controls\Select2Control;
use PublicFunction\Toolkit\Customizer\Controls\SwitchControl;
use PublicFunction\Toolkit\Customizer\Controls\TextareaControl;
use PublicFunction\Toolkit\Customizer\Controls\TextControl;
use PublicFunction\Toolkit\Customizer\Controls\TitleControl;
use WP_Customize_Color_Control;
use WP_Customize_Image_Control;
use WP_Customize_Media_Control;
use WP_Customize_Manager;

class Customizer extends RunableAbstract
{
    /**
     * WP_Customize_Manager instance.
     * @var WP_Customize_Manager
     */
    public $manager;

    protected $prefix;
    protected $panel;
    protected $capability = 'edit_theme_options';

    public function __construct(Container &$container)
    {
        require_once trailingslashit(__DIR__) . 'functions.php';
        global $wp_customize;

        $this->manager = $wp_customize;
        $this->prefix = strtolower($container->get('theme.short_name')) . '_';
        $this->panel = $this->prefix . 'customizer_options';

        parent::__construct($container);

        $this->rest_api()->addEndpoint("/customizer/(?P<path>[a-zA-Z|-|_]+)", [
            'callback' => [$this, 'customizerRest']
        ]);
    }

    /**
     * The meat and potatoes, the nitty gritty, the whole enchilada,
     * the nuts and bolts, the main dish.
     * You get it... it's what we're here for
     */
    public function register()
    {
        // If we've included a custom icon, remove the setting from the customizer
        if ($icon = $this->get('theme.icon')) {
            $this->manager->remove_control('site_icon');
        }

        // Get that panel going
        $this->manager->add_panel($this->panel, [
            'priority' => 25,
            'capability' => $this->capability,
            'theme_supports' => '',
            'title' => sprintf(__('%s Options', $this->get('textdomain')), $this->get('theme.name'))
        ]);



        $priority = count($this->manager->sections());

        foreach ($this->sections() as $sid => $section) {

            $section_args = [
                'title'    => $section['title'],
                'priority' => $priority++,
                'panel'    => $this->panel,
            ];

            if (isset($section['callback'])) {
                $section_args['active_callback'] = $section['callback'];
            }

            // Creates the section that holds all of the option groups
            // and their settings
            $this->manager->add_section($this->prefix .$sid, $section_args);

            // Check to see if we have settings saved for the section and start to
            // register them onto the panel
            if(isset($section['fields'])) {

                if(is_array($section['fields']) && !empty($section['fields'])) {

                    $group = null;

                    foreach($section['fields'] as $oid => $option) {
                        if (($group !== null) &&
                            (($groupField = $option['group']) &&
                                ($groupField !== $group)) ||
                            !isset($option['group'])) {
                            $this->groupFooter($sid, $group);
                            if (!isset($option['group'])) {
                                $group = null;
                            }
                        }
                        if (isset($option['group']) && ($groupField = $option['group']) &&
                            ($groupField !== $group)) {
                            $group = $groupField;
                            $this->groupHeader($sid, $group);
                        }
                        $this->registerSetting($sid, $oid, $option);
                    }
                    if ($group !== null) {
                        $this->groupFooter($sid, $group);
                    }
                }
            }
        }
    }

    private function registerSetting($sectionId, $optionId, $option) {
        $optionSettingId = "{$this->panel}[{$sectionId}][{$optionId}]";
        $oid = $this->prefix . "{$sectionId}_{$optionId}";
        $typeOverrides = $this->typeOverrides();

        $this->manager->add_setting($optionSettingId, [
            'capability'        => $this->capability,
            'theme_supports'    => '',
            'type'              => 'option',
            'sanitize_callback' => isset($option['sanitize_callback']) ? $option['sanitize_callback'] : '',
            'default'           => isset($option['default']) ? $option['default'] : '',
        ]);

        $args = array_merge([
            'settings' => $optionSettingId,
            'section'  => $this->prefix .$sectionId,
        ], $option);

        if(isset($option['type']) && array_key_exists($option['type'], $typeOverrides)) {
            $control = $typeOverrides[$option['type']];
            $this->manager->add_control(new $control( $this->manager, $oid, $args ));
            return true;
        }

        if(isset($option['control']) && class_exists($option['control'])) {
            $this->manager->add_control(new $option['control']( $this->manager, $oid, $args));
        } else {
            $this->manager->add_control( $oid, $args);
        }
    }

    private function groupHeader($sectionId, $groupId) {
        $groupHeader = [
            'label' => ucwords(implode(' ', explode('_',$groupId))) . ' Section',
            'type'  => 'accordion_heading'
        ];
        $this->registerSetting($sectionId, $groupId . '_head', $groupHeader);
    }

    private function groupFooter($sectionId, $groupId) {
        $groupFooter = [
            'label' => ucwords(implode(' ', explode('_',$groupId))) . ' Section',
            'type'  => 'accordion_footer'
        ];
        $this->registerSetting($sectionId, $groupId . '_foot', $groupFooter);
    }

    /**
     * @return array
     */
    public static function typeOverrides() {
        // We have some controls that don't need customizations and
        // don't really have much to offer other than displaying a
        // visual break or interaction. Most don't save to the DB
        return  [
            'text'              => TextControl::class,
            'wysiwyg'           => TextareaControl::class,
            'editor'            => TextareaControl::class,
            'inline'            => InlineControl::class,
            'title'             => TitleControl::class,
            'description'       => DescriptionControl::class,
            'divider'           => DividerControl::class,
            'switch'            => SwitchControl::class,
            'pages'             => PagesDropdownControl::class,
            'select2'           => Select2Control::class,
            'cf7_form'          => CF7DropdownControl::class,
            'layouts'           => LayoutsControl::class,
            'range'             => RangeControl::class,
            'accordion_heading' => AccordionHeadingControl::class,
            'accordion_footer'  => AccordionFooterControl::class,
            'admin_scripts'     => AdminScriptsControl::class,
            'pages_archives'    => PagesArchivesDropdownControl::class,
            'posts'             => PostsDropdownControl::class,

            // Default WordPress Controls
            'color'             => WP_Customize_Color_Control::class,
            'image'             => WP_Customize_Image_Control::class,
            'media'             => WP_Customize_Media_Control::class,
        ];
    }

    /**
     * @return array|JsonConfig
     */
    public function sections()
    {
        static $fields = [];
        $helper = new Helpers();
        if(empty($fields)) {
            $fields = new JsonConfig($this->get('theme.config_path') . 'customizer.json');
            $fields = $fields->get();

            foreach($fields as $sid => &$section) {
                if (isset($section['partial']) && ($partial = $section['partial'])) {
                    $partial = new JsonConfig($this->get('theme.config_path') . "customizer/{$partial}.json");
                    $section = $partial->get();
                }
                if(isset($section['fields'])) {
                    if(is_string($section['fields']))
                        $section['fields'] = $helper->shortcodeOrCallback($section['fields']);

                    if(is_array($section['fields'])) {
                        foreach($section['fields'] as &$option) {
                            foreach(['choices', 'default'] as $k) {
                                if(isset($option[$k]) && is_string($option[$k]))
                                    $option[$k] = $helper->shortcodeOrCallback($option[$k]);
                            }
                        }
                    }
                }
            }
        }

        return $fields;
    }

    /**
     * @return array
     */
    public function getDefaults()
    {
        static $defaults = [];
        if(empty($defaults)) {
            foreach($this->sections() as $sid => $section) {
                $fields = [];
                if(!empty($section['fields']) && is_array($section['fields'])) {
                    foreach($section['fields'] as $id => $setting) {
                        if(strpos($id, 'accordion') !== false || strpos($id, 'field_heading') !== false)
                            continue;

                        $fields[$id] = isset($setting['default']) ? $setting['default'] : '';
                    }
                }
                $defaults[$sid] = $fields;
            }
        }

        return $defaults;
    }

    /**
     * Returns an array of all fields with their defaults
     * @return array
     */
    public function saved()
    {
        $options = get_option($this->panel);
        $defaults = $this->getDefaults();

        $output = [];
        foreach($defaults as $sid => $section) {
            if(!isset($output[$sid]))
                $output[$sid] = [];

            if(is_array($section)) {
                foreach($section as $key => $value) {
                    $output[$sid][$key] = isset($options[$sid][$key]) ? $options[$sid][$key] : $value;
                }
            }
        }

        return $output;
    }

    /**
     * Returns a saved option
     * @param string $path
     * @return mixed
     */
    public function option($path)
    {
        return DotNotation::parse($path, $this->saved());
    }

    /**
     * WP Rest API callback for customizer data
     * @param \WP_REST_Request $request
     * @return mixed|\WP_Error
     */
    public function customizerRest(\WP_REST_Request $request)
    {
        if (!isset($request['path'])) {
            return new \WP_Error('pf_no_path', 'Invalid Customizer path', ['status' => 404]);
        }

        return $this->option($request['path']);
    }

    /**
     * Adds a WP shortcode for accessing customizer data
     * @param array $atts
     * @return mixed|string
     */
    public function customizerShortcode($atts = [])
    {
        if (isset($atts['field'])) {
            return $this->option($atts['field']);
        }
        return '';
    }

    public function run()
    {
        $this->loader()->addAction('customize_register', [$this, 'register']);
        $this->loader()->addShortcode('pf_customizer', [$this, 'customizerShortcode']);
    }
}
