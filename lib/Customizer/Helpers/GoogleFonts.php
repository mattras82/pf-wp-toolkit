<?php

namespace PublicFunction\Toolkit\Customizer\Helpers;

use PublicFunction\Toolkit\Core\Container;
use PublicFunction\Toolkit\Core\JsonConfig;

class GoogleFonts
{
    protected $path;
    protected $google_key;
    protected $fileName;
    protected $hours_interval;

    public function __construct(Container &$container)
    {
        $this->path = $container->get('theme_path');
        $this->google_key = $container->get('google_api_key');
        $this->hours_interval = 72;

        if(empty($this->google_key))
            throw new \RuntimeException('Sorry, you need a google key saved in the container under `google_api_key`');

        $this->fileName = 'google-fonts.json';
    }

    public function retrieve()
    {
        $file = "{$this->path}/{$this->fileName}";
        $url = 'https://www.googleapis.com/webfonts/v1/webfonts?key=' . $this->google_key;

        $time_offset = $this->hours_interval * 60 * 60;
        $empty_file = false;

        if(!file_exists($file)) {
            fopen($file, 'w');
            $empty_file = true;
        }

        if($empty_file || (time() - filemtime($file)) > $time_offset ) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            $response = curl_exec($ch);
            curl_close($ch);

            $fp = fopen($file, 'w');
            fwrite($fp, $response);
        }

        $json = new JsonConfig($file);
        $out = [];
        if($json['items'] && is_array($json['items'])) {
            foreach($json['items'] as $font) {
                $output[] = [
                    'family' => $font->family,
                    'variants' => $font->variants,
                    'category' => $font->category
                ];
            }
        }

        return $out;
    }
}
