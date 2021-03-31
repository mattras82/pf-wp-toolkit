<?php

namespace PublicFunction\Toolkit\Setup;


use PublicFunction\Toolkit\Core\Container;
use PublicFunction\Toolkit\Core\RunableAbstract;
use Yoast\WP\SEO\Values\Open_Graph\Images;

class SEO extends RunableAbstract {

    public function __construct(Container &$c)
    {
        parent::__construct($c);
    }

    public function robots_txt($txt, $public) {
        if ('0' === (string) $public) {
            $txt = "User-agent: *\nDisallow: /\n";
        }
        return $txt;
    }

    public function robots_meta($meta) {
        // Force override to respect the WP settings.
		if ( '0' === (string) get_option( 'blog_public' ) || isset( $_GET['replytocom'] ) ) {
			$meta = 'noindex, nofollow';
        }
        return $meta;
    }

    private function has_default_og_image()
    {
        $seo_options = get_option('wpseo_social');
        if (is_array($seo_options) && !empty($seo_options)) {
            return !empty($seo_options['og_default_image_id']) || !empty($seo_options['og_default_image']);
        }
        return false;
    }

    public function filter_wpseo_presentation($presentation, $context)
    {
        if ($context->indexable->open_graph_image_source !== 'set-by-user') {
            // If the site has a default OG image set, and the current page
            // does not have its own specific OG image, then we'll remove the "alternate"
            // image that Yoast selected and revert to the site's default.
            $context->indexable->open_graph_image_source = null;
            $context->indexable->open_graph_image_id = null;
            $context->indexable->open_graph_image = null;
        }

        return $presentation;
    }

    public function run() {
        $this->loader()->addFilter('robots_txt', [$this, 'robots_txt'], 20, 2);
        $this->loader()->addFilter('wpseo_robots', [$this, 'robots_meta'], 20);
        if ($this->has_default_og_image()) {
            $this->loader()->addFilter('wpseo_frontend_presentation', [$this, 'filter_wpseo_presentation'], 10, 2);
        }
    }
}
