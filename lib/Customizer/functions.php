<?php
if (!function_exists('pf_fieldset_social')) {
    /**
     * Returns the customizer fieldset for social icons
     * @return array
     */
    function pf_fieldset_social() {
        $output = [];
        $defaults = [
            'facebook' => ['fa-facebook-f', 1],
            'google-plus' => ['fa-google-plus-g', 1],
            'instagram' => ['fa-instagram', 0],
            'linkedin' => ['fa-linkedin-in', 0],
            'pinterest' => ['fa-pinterest-p', 0],
            'snapchat' => ['fa-snapchat-ghost', 0],
            'tumblr' => ['fa-tumblr', 0],
            'twitter' => ['fa-twitter', 1],
            'vimeo' => ['fa-vimeo-v', 0],
            'yelp' => ['fa-yelp', 0],
            'youtube' => ['fa-youtube', 0],
        ];

        foreach($defaults as $id => $default) {
            $output[$id . '_accordion_heading'] = [
                'label' => sprintf(__('%s'), ucwords(str_replace('-', ' ', $id))),
                'type' => 'accordion_heading'
            ];
            $output[$id] = [
                'label' => __('Enable'),
                'default' => $default[1],
                'type' => 'switch'
            ];
            $output[$id . '_url'] = [
                'label' => __('Url'),
                'default' => '#',
                'sanitize_callback' => 'esc_url',
                'type' => 'text',
                'input_attrs' => [
                    'class' => 'code widefat'
                ]
            ];
            $output[$id . '_fa_icon'] = [
                'label' => __('Icon'),
                'default' => $default[0],
                'sanitize_callback' => 'esc_attr',
                'type' => 'text',
                'input_attrs' => [
                    'class' => 'code widefat'
                ]
            ];
            $output[$id . '_img_icon'] = [
                'label' => __('Icon Image'),
                'description' => 'Image will only be used if provided. Otherwise icon class will be used',
                'default' => '',
                'type' => 'image',
                'sanitize_callback' => 'esc_attr',
            ];
            $output[$id. '_accordion_footer'] = [
                'type' => 'accordion_footer'
            ];
        }
        return $output;
    }
}

if (!function_exists('pf_get_option')) {
    /**
     * Returns the value of a theme option by group and key or dot notation
     * @param null|string $path
     * @return null|mixed
     */
    function pf_get_option($path) {
        if(!pf_toolkit('use_customizer'))
            return null;

        return pf_toolkit('customizer')->option($path);
    }
}

if (!function_exists('pf_option')) {
    /**
     * Prints out an option by group and key or dot-notation
     * @param null $path
     */
    function pf_option($path) {
        echo pf_get_option($path);
    }
}

if (!function_exists('pf_option_enabled')) {
    /**
     * Checks to see if a specific option exists and is true
     * Useful for sections that require a check on a checkbox
     * @param null|string $path
     * @return bool
     */
    function pf_option_enabled($path) {
        $option = pf_get_option($path);
        return !empty($option) && !!($option);
    }
}

if (!function_exists('pf_get_social')) {
    /**
     * Returns an array of enabled social icons
     * @return \PublicFunction\Assets\SocialIcon[]
     */
    function pf_get_social() {
        $option = pf_get_option('social');
        $networks = [];
        foreach(['facebook', 'twitter', 'google-plus', 'pinterest', 'linkedin', 'instagram', 'snapchat', 'tumblr', 'youtube', 'vimeo', 'yelp'] as $type) {
            if(isset($option[$type]) && (bool) $option[$type])
                $networks[] = new \PublicFunction\Toolkit\Assets\SocialIcon($type);
        }
        return $networks;
    }
}
