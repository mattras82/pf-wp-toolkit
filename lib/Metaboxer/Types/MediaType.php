<?php

namespace PublicFunction\Toolkit\Metaboxer\Types;

use PublicFunction\Toolkit\Core\Markup;

class MediaType extends ImageType
{

    public function __construct($args)
    {
        $this->media_type = isset($args['media_type']) ? $args['media_type'] : 'media';
        $this->media_label = 'Media';

        parent::__construct($args);

        if ($this->media_type !== 'media' && $this->media_label === 'Media') {
            $this->media_label = $this->media_type;
            if (stripos($this->media_label, '/')) {
                $this->media_label = strtoupper(
                    substr(
                        $this->media_label,
                        stripos($this->media_label, '/') + 1,
                        strlen($this->media_label)
                    )
                );
            }
        }
    }

    protected function get_attachment($value) {
        return (isset($value['id']) ? wp_get_attachment_url($value['id']) :
            (isset($value['url']) ? $value['url'] : ''));
    }

    protected function display_preview($src = null) {
        $src = '/wp-includes/images/media/document.png';
        $img = Markup::tag('img', ['src' => $src, 'alt' => $this->media_label.' icon']);
        $title = '';
        if ($url = $this->get_value()['url']) {
            $url = explode('/', $url);
            $title = Markup::tag('span', ['class' => 'field-label'], $url[count($url) - 1]);
        }
        echo Markup::tag('a', [
            'href' => 'javascript:void(0)',
            'class' => 'pf-metabox-image-change-image',
            'title' => 'Change '.$this->media_label
        ], $img . $title);
    }
}
