<?php

if (!function_exists('pf_get_meta')) {
    /**
     * @param string $path
     * @param WP_Post|int|null $post
     * @param string $filter
     * @return null|mixed
     */
    function pf_get_meta($path, $post = null, $filter = '') {
        if (!pf_toolkit('use_metaboxer'))
            return null;

	    if (is_string($post) && empty($filter)) {
		    $filter = $post;
		    $post = null;
	    }

        return apply_filters($filter, pf_toolkit('metaboxer')->meta($path, $post));
    }
}

if (!function_exists('pf_meta')) {
    /**
     * Wrapper print function for pf_get_meta
     * @param string $path
     * @param WP_Post|int|null $post
     * @param string $filter
     */
    function pf_meta($path, $post = null, $filter = '') {
        echo pf_get_meta($path, $post, $filter);
    }
}
