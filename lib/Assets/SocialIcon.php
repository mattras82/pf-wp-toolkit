<?php

namespace PublicFunction\Toolkit\Assets;

class SocialIcon
{
    public $type;
    public $name;
    public $url;
    public $iconIsImage;
    public $icon;

    public function __construct($type)
    {
        $this->type = $type;
        $this->name = ucwords(str_replace('-', ' ', $type));
        $this->iconIsImage = !empty(pf_get_option("social.{$type}_img_icon"));
        $this->icon = $this->iconIsImage ? pf_get_option("social.{$type}_img_icon") : pf_get_option("social.{$type}_fa_icon");
        $this->url  = pf_get_option("social.{$type}_url");
    }

    /**
     * Prints or returns the icon HTML
     * @param bool $echo
     * @return string
     */
    public function icon($echo = true)
    {
        $html = '<span class="social-icon">';
        if($this->iconIsImage) {
            $html .= '<img src="' . $this->icon . '" alt="' . $this->name . '">';
        } else {
            $html .= '<i class="fab fa-fw ' . $this->icon. '"></i>';
        }
        $html .= '</span>';

        if($echo)
            echo $html;

        return $html;
    }
}
