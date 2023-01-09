<?php declare( strict_types=1 );

namespace App\Core;

class Frontend
{
    private array $config = [];

    public function __construct()
    {
        // do nothing
    }

    public function init(array $config=[]): void
    {
        if (!is_admin()) {
            $this->config = $config;
            add_action('wp_enqueue_scripts', [$this, 'addStyle']);
        }
    }

    public function addStyle()
    {
        wp_enqueue_style( 'wp-turbo-style', $this->config['pluginURL'].'local/style.css',
            [],
            1234
        );
        /*
        $theme = wp_get_theme();
        if ($theme->parent()) {
            $parenthandle = 'parent-style'; // This is 'twentyfifteen-style' for the Twenty Fifteen theme.
            wp_enqueue_style( $parenthandle, get_template_directory_uri() . '/style.css',
                array(),  // if the parent theme code has a dependency, copy it to here
                $theme->parent()->get('Version')
            );
            wp_enqueue_style( 'child-style', get_stylesheet_uri(),
                array( $parenthandle ),
                $theme->get('Version') // this only works if you have Version in the style header
            );

            wp_enqueue_script( 'child-script', get_stylesheet_directory_uri() . '/js/scripts.js', array('jquery'), false, true);
            //wp_enqueue_script( 'child-filter', get_stylesheet_directory_uri() . '/js/jquery.simpler-sidebar-css3.min.js', array('jquery'), false, true);
        }
        */
    }
}
