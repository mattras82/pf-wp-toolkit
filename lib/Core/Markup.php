<?php

namespace PublicFunction\Toolkit\Core;

class Markup
{
    /**
     * Returns HTML for a specific element tag
     * @param string $tag
     * @param array $attributes
     * @param bool $content
     * @return string
     */
    public static function tag($tag = 'div', $attributes = [], $content = false)
    {
        $out = "<{$tag}";
        $out .= self::attributes($attributes);
        $out .= $content !== false ? ">{$content}</{$tag}>" : '/>';
        return $out;
    }

    /**
     * Returns attributes for an HTML ELement
     * @param array $attributes
     * @return string
     */
    public static function attributes($attributes = [])
    {
        if(empty($attributes))
            return '';

        $out = [];
        foreach($attributes as $attribute => $value) {
            if(is_array($value))
                $value = join(' ', $value);
            $value = esc_attr($value);
            $out[] = "{$attribute}=\"{$value}\"";
        }

        return ' ' . join(' ', $out);
    }
}
