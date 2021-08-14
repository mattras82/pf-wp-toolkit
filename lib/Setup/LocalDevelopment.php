<?php

namespace PublicFunction\Toolkit\Setup;


use PublicFunction\Toolkit\Core\Container;
use PublicFunction\Toolkit\Core\RunableAbstract;
use Yoast\WP\SEO\Presentations\Indexable_Presentation;

class LocalDevelopment extends RunableAbstract
{
    private $local_url;

    private $remote_url;

    private $local_port;

    public function __construct(Container &$c)
    {
        parent::__construct($c);

        $this->local_url = getenv('LOCAL_URL');
        $this->remote_url = getenv('REMOTE_URL');
        $this->local_port = getenv('LOCAL_PORT');
    }
    /**
     * Replaces the local URL with the remote URL
     *
     * @param  String $url
     * @param  String $search
     * @param  String $replace
     * @return String
     */
    private function replace($url, $search = '', $replace = '')
    {
        $search = $search ?: $this->get_replacement_url();
        $replace = $replace ?: $this->remote_url;
        return str_replace($search, $replace, $url);
    }


    /**
     * Gets the local URL, possibly with the local port, for use in replacement functions
     *
     * @return String
     */
    private function get_replacement_url()
    {
        if ($this->local_port && $this->local_port !== '80') {
            return $this->local_url . ':' . $this->local_port;
        }
        return $this->local_url;
    }

    public function replace_attachment_src($image)
    {
        if (is_array($image)) {
            $image[0] = $this->replace($image[0]);
        }
        return $image;
    }

    public function replace_attachment_srcset($sources)
    {
        foreach ($sources as &$source) {
            if (is_array($source)) {
                $source['url'] = $this->replace($source['url']);
            }
        }
        return $sources;
    }

    public function replace_attachment_url($url)
    {
        return $this->replace($url);
    }

    public function replace_yoast_breadcrumbs(Indexable_Presentation $presentation)
    {
        foreach ($presentation->breadcrumbs as $i => $breadcrumb) {
            if (is_array($breadcrumb) && !empty($breadcrumb['url'])) {
                $breadcrumb['url'] = $this->replace($breadcrumb['url'], $this->remote_url, $this->get_replacement_url());
                $presentation->breadcrumbs[$i] = $breadcrumb;
            }
        }
        return $presentation;
    }

    public function is_local()
    {
        return $this->local_url && $this->remote_url && substr(get_option('siteurl'), 0, strlen($this->local_url)) === $this->local_url;
    }

    public function run()
    {
        if ($this->is_local()) {
            $this->loader()->addFilter('wp_get_attachment_url', [$this, 'replace_attachment_url'], 20);
            $this->loader()->addFilter('wp_get_attachment_image_src', [$this, 'replace_attachment_src'], 20);
            $this->loader()->addFilter('wp_calculate_image_srcset', [$this, 'replace_attachment_srcset'], 20);
            $this->loader()->addFilter('wpseo_frontend_presentation', [$this, 'replace_yoast_breadcrumbs']);
            $this->loader()->addFilter('wpcf7_skip_mail', '__return_true', 20);
            $this->loader()->addFilter('wpcf7_recaptcha_verify_response', '__return_true');
        }
    }
}
