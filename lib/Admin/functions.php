<?php
if (!function_exists('pf_get_page_templates')) {
    /**
     * Returns the page template being used by a particular page
     * @param int|null $post_id
     * @return array
     */
    function pf_get_page_templates($post_id = null) {
        if(!$post_id)
            $post_id = get_the_ID();
        $raw = get_post_meta( $post_id, '_wp_page_template', true );
        $named = '';

        foreach(wp_get_theme()->get_page_templates(null, 'page') as $path => $name) {
            if($path == $raw) {
                $named = $name;
                break;
            }
        }

        $templates = [];
        if(!empty($raw)) {
            $templates[] = $raw;
            if(!empty($named))
                $templates[] = $named;
            $templates[] = sanitize_title(str_replace([
                '.php',
                'page',
                'templates/',
                'template-',
                'template',
            ], '', $raw ));
        }
        return $templates;
    }
}

if (!function_exists('pf_is_page_template')) {
    /**
     * Checks to see if we're using a specific page template
     * @param string|array $slugs
     * @param null|int $post_id
     * @return bool
     */
    function pf_is_page_template($slugs, $post_id = null) {
        $slugs = (array) $slugs;
        $templates = pf_get_page_templates($post_id);
        $found = false;
        foreach($slugs as $slug) {
            if(in_array($slug, $templates)) {
                $found = true;
                break;
            }
        }
        return $found;
    }
}
