<?php

if (!function_exists('pf_get_meta')) {
    /**
     * @param string $path
     * @param WP_Post|int|null $post
     * @return null|mixed
     */
    function pf_get_meta($path, $post = null) {
        if (!pf_toolkit('use_metaboxer'))
            return null;

        return pf_toolkit('metaboxer')->meta($path, $post);
    }
}

if (!function_exists('pf_meta')) {
    /**
     * Wrapper print function for pf_get_meta
     * @param string $path
     * @param WP_Post|int|null $post
     */
    function pf_meta($path, $post = null) {
        echo pf_get_meta($path, $post);
    }
}
