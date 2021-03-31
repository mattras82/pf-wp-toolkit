<?php

namespace PublicFunction\Toolkit\Setup;

use PublicFunction\Toolkit\Core\RunableAbstract;
use PublicFunction\Toolkit\Core\Container;

class CustomPostType extends RunableAbstract
{

    /**
     * Holds the full object that is read from the JSON file
     *
     * @var array
     */
    protected $types;

    /**
     * Used as the default for the 'menu_position' argument if it is not set
     *
     * @var int;
     */
    protected $default_position;

    /**
     * Used to register any custom dropdown filters for the admin screen
     *
     * @var array
     */
    protected $taxonomy_columns;

    /**
     * CustomPostType constructor.
     * @param Container $c
     */
    public function __construct(Container &$c)
    {
        parent::__construct($c);

        $this->types = $this->get_types();

        $this->default_position = 10;

        $this->taxonomy_columns = [];

    }

    protected function get_types()
    {
        static $types = [];
        if (empty($types) && $this->get('use_custom_post_types')) {
            $types = new JsonConfig($this->get('theme.config_path') . 'customposttypes.json', 'customposttypes');
            $types = $types->get();
        }

        return $types;
    }

    private function get_defaults($singular, $plural, $key)
    {
        return [
            'description' => '',
            'label' => $plural,
            'public' => true,
            'publicly_queryable' => true,
            'menu_position' => $this->default_position,
            'map_meta_cap' => true,
            'menu_icon' => 'dashicons_admin_tools',
            'supports' => ['title', 'excerpt', 'thumbnail', 'revisions', 'editor'],
            'has_archive' => $key . 's',
            'labels' => $this->get_default_labels($singular, $plural),
            'rewrite' => ['slug' => $key, 'with_front' => false]
        ];
    }

    private function get_default_labels($singular, $plural)
    {
        return [
            'name' => __($plural, pf_toolkit('textdomain')),
            'singular_name' => __($singular, pf_toolkit('textdomain')),
            'add_new' => __('Add New', pf_toolkit('textdomain')),
            'add_new_item' => __('Add New ' . $singular, pf_toolkit('textdomain')),
            'edit_item' => __('Edit ' . $singular, pf_toolkit('textdomain')),
            'new_item' => __('New ' . $singular, pf_toolkit('textdomain')),
            'view_item' => __('View ' . $singular, pf_toolkit('textdomain')),
            'view_items' => __('View ' . $plural, pf_toolkit('textdomain')),
            'search_items' => __('Search ' . $plural, pf_toolkit('textdomain')),
            'not_found' => __('No ' . strtolower($plural) . ' found', pf_toolkit('textdomain')),
            'archives' => __($plural . ' Archives', pf_toolkit('textdomain')),
            'menu_name' => __($plural, pf_toolkit('textdomain')),
            'all_items' => __('All ' . $plural, pf_toolkit('textdomain'))
        ];
    }

    private function get_default_messages($singular, $post)
    {
        return [
            0 => '', // Unused. Messages start at index 1.
            1 => __($singular . ' updated.', pf_toolkit('textdomain')),
            2 => __('Custom field updated.', pf_toolkit('textdomain')),
            3 => __('Custom field deleted.', pf_toolkit('textdomain')),
            4 => __($singular . ' updated.', pf_toolkit('textdomain')),
            /* translators: %s: date and time of the revision */
            5 => isset($_GET['revision']) ? sprintf(__($singular . ' restored to revision from %s', pf_toolkit('textdomain')), wp_post_revision_title((int)$_GET['revision'], false)) : false,
            6 => __($singular . '  published.', pf_toolkit('textdomain')),
            7 => __($singular . '  saved.', pf_toolkit('textdomain')),
            8 => __($singular . '  submitted.', pf_toolkit('textdomain')),
            9 => sprintf(
                __($singular . '  scheduled for: <strong>%1$s</strong>.', pf_toolkit('textdomain')),
                // translators: Publish box date format, see http://php.net/date
                date_i18n(__('M j, Y @ G:i', pf_toolkit('textdomain')), strtotime($post->post_date))
            ),
            10 => __($singular . '  draft updated.', pf_toolkit('textdomain'))
        ];
    }

    private function add_message_links(&$messages, $singular, $post, $post_type)
    {
        $permalink = get_permalink($post->ID);

        $view_link = sprintf(' <a href="%s">%s</a>', esc_url($permalink), __('View ' . $singular, pf_toolkit('textdomain')));
        $messages[$post_type][1] .= $view_link;
        $messages[$post_type][6] .= $view_link;
        $messages[$post_type][9] .= $view_link;

        $preview_permalink = add_query_arg('preview', 'true', $permalink);
        $preview_link = sprintf(' <a target="_blank" href="%s">%s</a>', esc_url($preview_permalink), __('Preview ' . $singular, pf_toolkit('textdomain')));
        $messages[$post_type][8] .= $preview_link;
        $messages[$post_type][10] .= $preview_link;
    }

    private function handleTax($post_key, &$args) {
        $taxes = [];
        foreach ($args['taxonomies'] as $key => $tax) {
            if (!taxonomy_exists($key)) {
                register_taxonomy($key, $post_key, $tax);
            }
            if (!empty($tax['add_filter_dropdown'])) {
                if (!isset($this->taxonomy_columns[$post_key])) {
                    $this->taxonomy_columns[$post_key] = [];
                }
                $this->taxonomy_columns[$post_key][] = $key;
            }
            $taxes[] = $key;
        }
        $args['taxonomies'] = $taxes;
    }

    public function add_taxonomy_filters($post_type, $which) {
        if ($which === 'top') {
            global $wp_taxonomies;
            foreach($this->taxonomy_columns as $type => $taxes) {
                if ($post_type === $type) {
                    foreach($taxes as $tax_key) {
                        $terms = get_terms([
                            'taxonomy'  => $tax_key
                        ]);
                        if (!empty($terms)) {
                            $tax_object = $wp_taxonomies[$tax_key];
                            $this->display_taxonomy_filter($tax_object, $terms);
                        }
                    }
                }
            }
        }
    }

    private function display_taxonomy_filter($tax, $terms) {
        $key = $tax->name;
        $singular = $tax->labels->singular_name;
        $plural = $tax->label;
        $val = isset($_GET[$key]) ? $_GET[$key] : 0;
        $key_attr = 'filter-by-'.esc_attr($key);
        ?>
        <label for="<?= $key_attr ?>" class="screen-reader-text"><?= __("Filter by $singular") ?></label>
        <select id="<?= $key_attr ?>" name="<?= esc_attr($key) ?>">
            <option<?php selected($val, 0) ?> value="0"><?= __("All $plural") ?></option>
            <?php foreach ($terms as $term) {
                printf(
                    "<option %s value='%s'>%s</option>\n",
                    selected($val, $term->slug, false),
                    esc_attr($term->slug),
                    __($term->name)
                );
            } ?>
        </select>
        <?php
    }

    /**
     * @param array $messages Existing post update messages.
     * @return array Amended post update messages with new CPT update messages.
     */
    public function custom_messages($messages)
    {
        $post = get_post();
        $post_type = get_post_type($post);
        $post_type_object = get_post_type_object($post_type);

        if (isset($this->types[$post_type])) {
            $type = $this->types[$post_type];
            $singular = isset($type['singular']) && !empty($type['singular']) ? $type['singular'] : ucwords($post_type);
            $messages[$post_type] = $this->get_default_messages($singular, $post);

            if ($post_type_object->publicly_queryable) {
                $this->add_message_links($messages, $singular, $post, $post_type);
            }
        }

        return $messages;
    }

    private function new_type($key, &$args)
    {
        $singular = $args['singular'];
        if (!$singular) return;
        if (isset($args['plural'])) {
            $plural = $args['plural'];
        } else {
            $plural = $singular . 's';
            $args['plural'] = $plural;
        }

	    if (isset($args['labels'])) {
		    $args['labels'] = wp_parse_args($args['labels'], $this->get_default_labels($singular, $plural));
	    }

        $settings = wp_parse_args($args, $this->get_defaults($singular, $plural, $key));

        register_post_type($key, $settings);
    }

    private function edit_type($key, &$args)
    {
        global $wp_post_types;

        if (is_array($args['labels'])) {
            $labels = &$wp_post_types[$key]->labels;
            foreach ($args['labels'] as $name => $value) {
                $labels->{$name} = $value;
            }
            unset($args['labels']);
        }
        foreach ($args as $prop => $value) {
            $wp_post_types[$key]->{$prop} = $value;
            if ($prop === 'rewrite') {
                $wp_post_types[$key]->add_rewrite_rules();
            }
        }
    }

    public function register()
    {
        if (!empty($this->types)) {
            foreach ($this->types as $key => &$args) {
                if (isset($args['taxonomies'])) {
                    $this->handleTax($key, $args);
                }
                if (post_type_exists($key)) {
                    $this->edit_type($key, $args);
                } else {
                    $this->new_type($key, $args);
                }
            }
        }
    }

    public function run()
    {
        $this->loader()->addAction('init', [$this, 'register']);
        $this->loader()->addFilter('post_updated_messages', [$this, 'custom_messages']);
        $this->loader()->addAction('restrict_manage_posts', [$this, 'add_taxonomy_filters'], 20, 2);
    }

}
