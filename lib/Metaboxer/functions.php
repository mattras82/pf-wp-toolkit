<?php

if (!function_exists('pf_get_meta')) {
    /**
     * @param string $path
     * @param WP_Post|int|string|null $post
     * @param string $filter
     * @param string $type
     * @return null|mixed
     */
    function pf_get_meta($path, $post = null, $filter = '', $type = 'post') {
        if (!pf_toolkit('use_metaboxer'))
            return null;

        if (is_string($post) && empty($filter)) {
        	$filter = $post;
        	$post = null;
        }

        if ($filter === 'term') {
            $type = 'term';
            $filter = '';
        } else if ($post instanceof WP_Term) {
            $type = 'term';
        }

        $meta = pf_toolkit('metaboxer')->meta($path, $post, $type);

        if (!empty($filter)) {
            $meta = apply_filters($filter, $meta);
        }

        return $meta;
    }
}

if (!function_exists('pf_lazy_meta') && function_exists('pf_lazy_attachment_image')) {
	/**
	 * Prints out the lazy load markup for an image in the given Metaboxer field. Defaults to current post.
	 * @param string $path
	 * @param WP_Post|int|string|null
	 * @param string $size
	 * @param array $atts
     * @param string $type
	 * @return void
	 */
	function pf_lazy_meta($path, $post = null, $size = 'full', $atts = [], $type = 'post') {
		if ($id = pf_get_meta($path.'_id', $post, '', $type)) {
			pf_lazy_attachment_image($id, $size, $atts);
		} else {
			$img = pf_get_meta($path, $post, '', $type);
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
     * @param string $type
     */
    function pf_meta($path, $post = null, $filter = '', $type = 'post') {
        echo pf_get_meta($path, $post, $filter, $type);
    }
}
