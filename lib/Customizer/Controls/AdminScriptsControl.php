<?php

namespace PublicFunction\Toolkit\Customizer\Controls;

class AdminScriptsControl extends BaseControl
{
    protected function render_content()
    {
        do_action( 'admin_footer' );
        do_action( 'admin_print_footer_scripts' );
    }
}
