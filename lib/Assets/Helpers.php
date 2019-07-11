<?php

namespace PublicFunction\Toolkit\Assets;


class Helpers
{
    /**
     * @param string $string
     * @return mixed|string
     */
    public function shortcodeOrCallback($string) {
        $string = trim($string);

        if(substr($string, 0, 2) == '[[' ) {
            return do_shortcode(substr($string, 1, strlen($string) - 2));
        } else if(substr($string, 0, 2) == '{{') {
            $cb = trim(str_replace(['{{', '}}'], '', $string));

            $params = [];
            if(strpos($cb, ' ') !== false) {
                $params = explode(' ', $cb);
                $cb = array_shift($params);
            }

            $cb_pairs = [
                'image' => 'pf_get_image',
                'numbers' => 'numbers',
                'font_weights' => 'fontWeights',
                'long_lorem' => 'loremLong',
                'short_lorem' => 'loremShort'
            ];

            if(array_key_exists($cb, $cb_pairs))
                $cb = $cb_pairs[$cb];

            if(function_exists($cb)) {
                return call_user_func_array($cb, $params);
            } else if (method_exists($this, $cb)) {
                return call_user_func_array([$this, $cb], $params);
            }
        }
        return $string;
    }

    public static function numbers($start = 1, $end = 10){
        $choices = [];
        for($i = $start; $i <= $end; $i++) {
            $choices[$i] = $i;
        }
        return $choices;
    }

    public static function fontWeights()
    {
        return [
            'light' => 'Light',
            'normal' => 'Normal',
            'bold' => 'Bold',
            '100' => '100',
            '200' => '200',
            '300' => '300',
            '400' => '400',
            '500' => '500',
            '600' => '600',
            '700' => '700',
            '800' => '800',
        ];
    }

    public static function loremLong()
    {
        return 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ' .
            'ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ' .
            'ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in ' .
            'reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur ' .
            'sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id ' .
            'est laborum.';
    }

    public static function loremShort()
    {
        return 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ' .
            'ut labore et dolore magna aliqua.';
    }

    public static function partial($path = null)
    {
        ob_start();
        if ($path) {
            pf_partial($path);
        }
        return ob_get_clean();
    }
}
