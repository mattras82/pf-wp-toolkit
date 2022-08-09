<?php

namespace PublicFunction\Toolkit\Admin;

use PublicFunction\Toolkit\Core\Container;
use PublicFunction\Toolkit\Core\RunableAbstract;

class PostsTables extends RunableAbstract
{
    /**
     * The id of the front page
     * @var int
     */
    protected $frontPageID;

    /**
     * The id of the home page
     * @var int
     */
    protected $homePageID;

    /**
     * PostsTables constructor.
     * @param Container $c
     */
    public function __construct(Container &$c)
    {
        require_once trailingslashit(__DIR__) . 'functions.php';
        parent::__construct($c);

        $this->frontPageID = (int) get_option( 'page_on_front' );
        $this->homePageID  = (int) get_option( 'page_for_posts' );
    }

    /**
     * Insert one or more key and values into a specific spot of an array
     * @param string $key
     * @param array $array
     * @param array $pairs
     * @return array
     */
    public function insertBefore($key, $array, $pairs) {
        if(array_key_exists($key, $array)) {
            $new = [];
            foreach($array as $k => $value) {
                if($k === $key)
                    foreach($pairs as $kk => $vv)
                        $new[$kk] = $vv;

                $new[$k] = $value;
            }

            return $new;
        }

        return $array;
    }

    /**
     * Adds custom columns for pages
     * @param array $columns
     * @return array
     */
    public function addPageColumns(array $columns)
    {
        $page_object = get_post_type_object('page');
        $featured_label = $page_object->labels->featured_image;
        return $this->insertBefore('author', $columns, [
            'image' => sprintf(__('%s '.$featured_label, pf_toolkit('textdomain')),
                '<span class="dashicons dashicons-format-image"></span>'
            ),
            'template' => __('Template', pf_toolkit('textdomain'))
        ]);
    }

    /**
     * Adds custom columns for posts
     * @param array $columns
     * @return array
     */
    public function addPostColumns(array $columns)
    {
        $post_object = get_post_type_object('post');
        $featured_label = $post_object->labels->featured_image;
        return $this->insertBefore('author', $columns, [
            'image' => __($featured_label, pf_toolkit('textdomain')),
        ]);
    }

    /**
     * Adds featured image column to pages and posts
     * @param $column
     * @param $post_id
     */
    public function imageColumn($column, $post_id)
    {
        if ($column == 'image') {
            if (has_post_thumbnail()) {
                printf('<a href="%2$s" target="_blank" title="%1$s"><img src="%3$s" width="75"></a>',
                    __('Preview Image', pf_toolkit('textdomain')),
                    get_the_post_thumbnail_url($post_id, 'full'),
                    get_the_post_thumbnail_url($post_id, 'pf-preview-admin')
                );
            } else {
                _e('None', pf_toolkit('textdomain'));
            }
        }
    }

    /**
     * Adds template information for pages
     * @param string $column
     * @param int $post_id
     */
    public function templateColumn($column, $post_id)
    {
        if ( $column == 'template' ) {
            $path = $this->get('theme.path') . 'templates';
            $post = get_post($post_id);

            if($post_id == $this->frontPageID && file_exists("$path/front-page.php"))
                $templates = ["templates/front-page.php", 'Static Front Page'];

            elseif($post_id == $this->homePageID && file_exists("$path/home.php"))
                $templates = ["templates/home.php", 'Blog Posts Page'];

            elseif(file_exists("$path/page-{$post->post_name}.php"))
                $templates = ["templates/page-{$post->post_name}.php", 'Slug Template'];

            elseif(file_exists("$path/page-{$post_id}.php"))
                $templates = ["templates/page-{$post_id}.php", 'ID Template'];

            else
                $templates = pf_get_page_templates($post_id);

            if(empty($templates))
                $templates = ['default', 'default'];

            printf('<strong>%s</strong>%s',
                ucwords($templates[1]),
                $templates[0] != 'default' ? sprintf('<br><small>%s</small>', $templates[0]) : ''
            );
        }
    }

    /**
     * Run
     */
    public function run()
    {
        $this->loader()->addFilter('manage_pages_columns', [$this, 'addPageColumns'], 100);
        $this->loader()->addFilter('manage_posts_columns', [$this, 'addPostColumns'], 100);
        $this->loader()->addAction('manage_pages_custom_column', [$this, 'imageColumn'], 10, 2);
        $this->loader()->addAction('manage_posts_custom_column', [$this, 'imageColumn'], 10, 2);
        $this->loader()->addAction('manage_pages_custom_column', [$this, 'templateColumn'], 10, 2);
    }
}
