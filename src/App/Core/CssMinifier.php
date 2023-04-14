<?php declare( strict_types=1 );

namespace App\Core;

class CssMinifier
{
    public function __construct()
    {
        // do nothing
    }

    public function init(): void
    {
        $this->setHooks();
    }

    public function setHooks(): void
    {
        add_action('wp_ajax_edit-theme-plugin-file', [$this, 'callMinifierFunction'], 0);
    }

    public function callMinifierFunction(): void
    {
        if (!empty($_POST['plugin']) && $_POST['plugin'] === 'wp-turbo/wp-turbo.php') {
            if ($_POST['file']==='wp-turbo/local/style.css') {
                $this->cssMinifier($_POST['newcontent']);
            }
        }
    }

    private function cssMinifier($newContent): void
    {
        $minified = $newContent;

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
    }
}
