<?php

namespace WPTurbo\App\Core;

class CustomJS
{
    public function __construct()
    {
        // do nothing
    }

    public function init()
    {
        $this->setHooks();
    }

    public function setHooks()
    {
        add_action('wp_footer', [$this, 'addCustomJS']);
    }

    public function addCustomJS()
    {
        $wpOptions = get_option( 'wp-turbo-options' );

        if ($wpOptions) {
            echo '<script type="text/javascript">';
            echo $wpOptions['customJS'];
            echo '</script>';
        }
    }
}
