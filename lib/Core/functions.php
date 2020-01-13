<?php

if (!function_exists('pf_phone_link')) {
    /**
     * Formats a front-end formatted phone number to an anchor/href value
     * @param string $phone
     * @return string
     */
    function pf_phone_link($phone) {
        $link = '';
        if ($phone) {
            $link = 'tel:1' . str_replace(['(',')','-',' '], '', $phone);
        }
        return $link;
    }
}

if (!function_exists('pf_formatted_list')) {
    /**
     * Formats an array of items into a comma-separated list, with ' and ' to separate the last item
     * @param array $list
     * @return string
     */
    function pf_formatted_list($list) {
        if (is_array($list)) {
            // Join all items, except the last, with a comma delimiter and make the string an array
            $main_list = array(join(', ', array_slice($list, 0, -1)));
            // Merge the new array with the last item in the original list
            $main_list = array_merge($main_list, array_slice($list, -1));
            // Reduce the array in case the original list has 1 or 2 items, then join it with ' and ' if applicable
            return join(' and ', array_filter($main_list, 'strlen'));
        }
        return '';
    }
}

if (!function_exists('pf_get_partial')) {
    /**
     * Finds a partial saved in `/templates/partials`
     * @param null|string $path
     * @param null|string $variant
     * @return string|null
     */
    function pf_get_partial($path = null, $variant = null)
    {
        if (empty($path))
            return null;

        $path = \PublicFunction\Toolkit\Core\DotNotation::dotToPath(str_replace([
            'templates/partials',
            'templates.partials',
            'partials/',
            'partials.',
            '.php'
        ], '', $path));

        $prefix = pf_toolkit('theme.partials');
        $templates = [];
        $variant = (string)$variant;
        $parent_prefix = (string)pf_toolkit('parent_theme.partials');

        // Adds variants to the templates array first so they're searched first
        if (!empty($variant)) {
            $templates[] = "{$prefix}/{$path}-{$variant}.php";

            if (!empty($parent_prefix))
                $templates[] = "{$parent_prefix}/{$path}-{$variant}.php";
        }

        $templates[] = "{$prefix}/{$path}.php";

        if (!empty($parent_prefix))
            $templates[] = "{$parent_prefix}/{$path}.php";


        foreach ($templates as $template) {
            if (!$template)
                continue;

            if (file_exists($template)) {
                return $template;
            }
        }
        return null;
    }
}

if (!function_exists('pf_partial')) {
    /**
     * Includes a partial saved in `/templates/partials`
     * @param null|string $path
     * @param null|string|array $variant
     * @param array $args
     * @return void
     */
    function pf_partial($path = null, $variant = null, $args = [])
    {

        // Trade variant and args
        if (is_array($variant)) {
            $args = $variant;
            $variant = null;
        }

        if ($located = pf_get_partial($path, $variant)) {
            // Keep the same functionality as load_templae
            global $posts, $post, $wp_did_header, $wp_query,
                   $wp_rewrite, $wpdb, $wp_version, $wp, $id,
                   $comment, $user_ID;

            if (is_object($wp_query) && is_array($wp_query->query_vars))
                extract($wp_query->query_vars, EXTR_SKIP);

            // Includes extra parameters that we passed
            extract((array)$args, EXTR_SKIP);
            include($located);
        }
    }
}

if (!function_exists('pf_partial_shortcode')) {
    /**
     * Wraps the pf_partial function in an output buffer to be used as a shortcode
     * @param array $atts
     * @return string
     */
    function pf_partial_shortcode($atts = []) {
        ob_start();
        if (isset($atts['path']) && !empty($atts['path'])) {
            pf_partial($atts['path']);
        }
        return ob_get_clean();
    }
    \PublicFunction\Toolkit\Plugin::getInstance()->shortcode('pf_partial', 'pf_partial_shortcode');
}

if (!function_exists('pf_post_thumbnail')) {
    /**
     * Get the post thumbnail.
     * @param string $size
     * @param array $atts
     * @return string
     */
    function pf_post_thumbnail($size = 'full', $atts = []) {
        if (post_password_required() || is_attachment() || !has_post_thumbnail())
            return false;

        pf_lazy_attachment_image(get_post_thumbnail_id(), $size, $atts);
    }
}

if (!function_exists('pf_get_image')) {
    /**
     * Returns a theme image url
     * @param $image
     * @return string
     */
    function pf_get_image($image)
    {
        $instance = \PublicFunction\Toolkit\Plugin::getInstance();
        return $instance->theme_or_plugin('assets.images_dir', $image);
    }
}

if (!function_exists('pf_image')) {
    /**
     * Prints a theme image url
     * @param string $image
     * @return void
     */
    function pf_image($image)
    {
        echo pf_get_image($image);
    }
}

if (!function_exists('pf_content_classes')) {
    /**
     * Returns the class attribute for the content wrapper. This is essentially a smaller
     * version of body_class()
     * @param array $more
     * @param string $base
     */
    function pf_content_classes($more = [], $base = 'content')
    {
        $classes = [$base];

        if ('' != ($template_slug = get_page_template_slug())) {
            $template_slug = str_replace(['templates/', '.php'], '', $template_slug);
            $classes[] = $base . '-' . $template_slug;
        }

        if (is_single() || is_page()) {
            $post = get_post();
            $classes[] = "{$base}-{$post->post_name}";
            $classes[] = "{$base}-{$post->ID}";
            $classes[] = "{$base}-{$post->post_type}-{$post->ID}";
        }

        $classes[] = $base . '-' . get_post_type();

        if (is_front_page())
            $classes[] = "{$base}-home";
        if (is_home())
            $classes[] = "{$base}-blog";
        if (is_archive())
            $classes[] = "{$base}-archive";
        if (is_date())
            $classes[] = "{$base}-date";

        $classes = join(" ", array_unique(array_map('esc_attr', array_merge($classes, (array)$more))));
        echo " class=\"{$classes}\"";
    }
}

if (!function_exists('pf_lazy_image')) {
    /**
     * Given a src URL, alt text, optional title & class values this function will echo an img element that will be loaded
     * after the initial page content using the lazy-images js module. A noscript element is used as a fallback for users
     * without JS
     * @param $src
     * @param string $alt
     * @param array|int|bool $width
     * @param int|bool $height
     * @param array $atts
     */
    function pf_lazy_image($src, $alt = '', $width = false, $height = false, $atts = [])
    {
        if (is_array($width)) {
            $atts = $width;
            $width = null;
        }

        if (!$width && isset($atts['width'])) $width = $atts['width'];
        if (!$height && isset($atts['height'])) $height = $atts['height'];

        $placeholder = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mN89h8AAtEB5wrzxXEAAAAASUVORK5CYII=";
        $attributes = '';
        $_src = parse_url($src);

        try {
            if (!$width && !$height && isset($_src['path']) && file_exists(ltrim($_src['path'], '/'))) {
                $image = getimagesize(ltrim($_src['path'], '/'));

                if ($image) {
                    list($width, $height) = $image;
                }
            }
        } catch (Exception $e) {}


        if (isset($width)) {
            $attributes .= " width=\"{$width}\"";
        }

        if (isset($height)) {
            $attributes .= " height=\"{$height}\"";
        }

        if ($width && $height) {
            $placeholder = "data:image/svg+xml,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20height='{$height}'%20width='{$width}'%3E%3C/svg%3E";
        }

        $atts['class'] = isset($atts['class']) ? $atts['class'] .= ' no-js' : 'no-js';

        foreach($atts as $label => $value) {
            $attributes .= " {$label}=\"{$value}\"";
        }

        printf(
            '<img src="%1$s" data-src="%2$s" alt="%3$s" %4$s><noscript><img src="%2$s" alt="%3$s" %4$s></noscript>',
            $placeholder,
            $src,
            $alt,
            $attributes
        );
    }
}

if (!function_exists('pf_lazy_attachment_image')) {
    /**
     * Given an attachment ID, and optional size and attributes echo markup for a lazy-loaded image.
     * @param int $id
     * @param string $size
     * @param array $atts
     */
    function pf_lazy_attachment_image($id, $size = 'thumbnail', $atts = []) {
        $image = wp_get_attachment_image_src($id, $size);

        if ($image) {
            list($src, $width, $height) = $image;
            $alt = trim(strip_tags(get_post_meta($id, '_wp_attachment_image_alt', true)));

            pf_lazy_image($src, $alt, $width, $height, $atts);
        }
    }
}

if (!function_exists('pf_breadcrumb')) {
    /**
     * Output a breadcrumb.
     */
    function pf_breadcrumb() {
        ?>
        <nav aria-label="You are here:" role="navigation">
            <ul class="breadcrumbs margin-bottom-0">
                <li>
                    <?php if (is_front_page()) : ?>
                        <span class="show-for-sr">Current: </span> Home
                    <?php else : ?>
                        <a href="/">Home</a>
                    <?php endif; ?>
                </li>

                <?php if (is_post_type_archive()) : ?>
                    <li>
                        <span class="show-for-sr">Current: </span> <?php post_type_archive_title(); ?>
                    </li>

                <?php elseif (is_singular()) : ?>
                    <?php
                    $post_type = get_post_type();
                    $post_type_archive_link = get_post_type_archive_link($post_type);

                    if (isset($post_type_archive_link) && !empty($post_type_archive_link)) :
                        $post_type_obj = get_post_type_object($post_type);
                        $post_type_archive_title = apply_filters('post_type_archive_title', $post_type_obj->labels->name, $post_type);
                        ?>
                        <li><a href="<?= $post_type_archive_link ?>"><?= $post_type_archive_title; ?></a></li>
                    <?php endif; ?>

                    <?php if (is_post_type_hierarchical($post_type)) : ?>
                        <?php $ancestors = array_reverse(get_post_ancestors(get_the_ID())); ?>
                        <?php foreach ($ancestors as $ancestor) : ?>
                            <li><a href="<?= get_the_permalink($ancestor); ?>"><?= get_the_title($ancestor); ?></a></li>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <li>
                        <span class="show-for-sr">Current: </span> <?php the_title(); ?>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php
    }
}

