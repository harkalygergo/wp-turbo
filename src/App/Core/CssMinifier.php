<?php declare( strict_types=1 );

namespace App\Core;

class CssMinifier
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
        add_action( 'rest_api_init', [$this, 'registerRestRoutes']);
    }

    public function registerRestRoutes()
    {
        register_rest_route( 'minifier', 'css', [
            'methods' => 'GET',
            'callback' => [$this, 'cssMinifier'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function cssMinifier()
    {
        $minified = file_get_contents(__DIR__.'/../../../local/style.css');

        $minified = str_replace("\n", "", $minified);
        $minified = str_replace("  ", " ", $minified);
        $minified = str_replace("  ", " ", $minified);
        $minified = str_replace(" {", "{", $minified);
        $minified = str_replace("{ ", "{", $minified);
        $minified = str_replace(" }", "}", $minified);
        $minified = str_replace("} ", "}", $minified);
        $minified = str_replace(", ", ",", $minified);
        $minified = str_replace("; ", ";", $minified);
        $minified = str_replace(": ", ":", $minified);

        //write the entire string
        file_put_contents(__DIR__.'/../../../local/style.min.css', $minified);
        exit;
    }
}
