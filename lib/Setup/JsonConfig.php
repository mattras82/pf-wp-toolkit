<?php

namespace PublicFunction\Toolkit\Setup;

use PublicFunction\Toolkit\Core\JsonConfig as JsonConfigBase;
use PublicFunction\Toolkit\Plugin;

class JsonConfig extends JsonConfigBase
{
    protected function errorTitle()
    {
        return "Missing JSON $this->_name file:";
    }

    protected function errorMessage()
    {
        $msg  = '<h3 style="color:#666;font-size:16px;">';
        $msg .= 'The '.Plugin::getInstance()->get('name').' requires the <strong><code>'.$this->_name.'.json</code></strong> file to run.</h3>';
        $msg .= '<p>It is used for configuration within main PHP files, JavaScript files as well as a few SCSS/CSS files. ';
        $msg .= 'The file should be located in the /config directory of the theme.</p>';

        return $msg;
    }
}
