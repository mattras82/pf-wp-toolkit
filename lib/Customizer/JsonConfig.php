<?php

namespace PublicFunction\Toolkit\Customizer;

use PublicFunction\Toolkit\Core\JsonConfig as JsonConfigBase;

class JsonConfig extends JsonConfigBase
{
    protected function errorTitle()
    {
        return 'Missing Customizer JSON config file:';
    }

    protected function errorMessage()
    {
        $msg = '<h4 style="color:#666;font-size:1em">';
        $msg .= 'The Customizer class needs <code>customizer.json</code> to run</h4>';
        $msg .= '<p>Please create this file and add fields as needed. If you don\'t mean to use the ';
        $msg .= 'provided Customizer logic, please disable it by setting:</p>';
        $msg .= '<pre style="background: #212121; padding:10px;color:#ccc">';
        $msg .= 'use_customizer: false</pre>';
        $msg .= '<p>within your <code>config.json</code> file. By default, this is enabled with ';
        $msg .= 'PublicFunction\'s starter theme.</p>';
        $msg .= '<p><a href="http://wordress.gscadmin.com/" ';
        $msg .= 'class="button secondary-button" title="" ';
        $msg .= 'target="_blank">Read more about it here</a></p>';

        return $msg;
    }
}
