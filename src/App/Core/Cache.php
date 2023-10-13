<?php declare( strict_types=1 );

namespace WPTurbo\App\Core;

class Cache
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
        if (is_admin()) {
            add_action( 'admin_init', [$this, 'addSettingsOptions'], 0 );
        }

        add_action( 'wp', [$this, 'generateAndReturnWithHtmlFile'] );
    }

    public function addSettingsOptions()
    {
        add_settings_section(
            'wp-turbo-settings-general', // ID
            'General', // Title
            _e(''), // Callback
            'my-setting-admin' // Page
        );

        add_settings_field(
            'generateAndReturnWithHtmlFile',
            'Enable HTML file generation from URLs?',
            [Dashboard::class, 'generateFormSelect'],
            'my-setting-admin',
            'wp-turbo-settings-general',
            ['name' => 'generateAndReturnWithHtmlFile']
        );
    }


    public function generateAndReturnWithHtmlFile(\WP $wp)
    {
        if (is_admin() || is_checkout() || is_cart() || is_account_page() || is_search() ) {
            return;
        }

        $objectID = get_queried_object_id();

        // if it is archive page, get url from category
        if (is_archive() || is_category()) {
            $objectURL = get_category_link($objectID);
        } else {
            $objectURL = get_permalink($objectID);
        }

        // get category permalink

        /*

        if (is_archive() || is_category()) {
            $category = get_queried_object();
            $objectID = $category->term_id;
        }

        if (is_singular(['post', 'page', 'product'])) {
            $post = get_post();
            $objectID = $post->ID;
        }
        */

        $wp_upload_dir = wp_upload_dir();
        $basedir = $wp_upload_dir['basedir'];
        $WPTurboDirectory = $basedir . '/wp-turbo/';
        $file = $WPTurboDirectory.$objectID.".html";
        dump($file);

        if ((!file_exists($file))) {
            //$url = str_replace('loc', 'www', $url);
            $command = "wget --no-check-certificate '".$objectURL."' -O ".$file;
            exec($command);

            $myfile = fopen($file, "a");
            fwrite($myfile, '<!-- Generated HTML - '.date('Y-m-d H:i:s').' -->');
            fclose($myfile);
        }

        if (file_exists($file) && filesize($file) > 1) {
            echo file_get_contents($file);
            exit;
        }
    }

}
