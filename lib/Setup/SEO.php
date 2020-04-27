<?php

namespace PublicFunction\Toolkit\Setup;


use PublicFunction\Toolkit\Core\Container;
use PublicFunction\Toolkit\Core\RunableAbstract;

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
			$meta = 'noindex,nofollow';
        }
        return $meta;
    }

    public function run() {
        $this->loader()->addFilter('robots_txt', [$this, 'robots_txt'], 20, 2);
        $this->loader()->addFilter('wpseo_robots', [$this, 'robots_meta'], 20);
    }
}
