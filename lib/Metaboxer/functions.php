<?php

if (!function_exists('pf_get_meta')) {
    /**
     * @param string $path
     * @param WP_Post|int|string|null $post
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

if (!function_exists('pf_lazy_meta') && function_exists('pf_lazy_attachment_image')) {
	/**
	 * Prints out the lazy load markup for an image in the Customizer
	 * @param string $path
	 * @param WP_Post|int|string|null
	 * @param string $size
	 * @param array $atts
	 * @return void
	 */
	function pf_lazy_meta($path, $post = null, $size = 'full', $atts = []) {
		if ($id = pf_get_meta($path.'_id', $post)) {
			pf_lazy_attachment_image($id, $size, $atts);
		} else {
			$img = pf_get_meta($path, $post);
			if ($id = attachment_url_to_postid($img)) {
				pf_lazy_attachment_image($id, $size, $atts);
			} else {
				pf_lazy_image($img, '', $atts);
			}
		}
	}
}

if (!function_exists('pf_meta')) {
    /**
     * Wrapper print function for pf_get_meta
     * @param string $path
     * @param WP_Post|int|string|null $post
     * @param string $filter
     */
    function pf_meta($path, $post = null, $filter = '') {
        echo pf_get_meta($path, $post, $filter);
    }
}
