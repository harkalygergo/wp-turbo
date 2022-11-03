<?php declare( strict_types=1 );

namespace App\Core;

class Debug
{
    public function __construct()
    {
        // do nothing
    }

    public function dump(mixed $variable, bool $exit=false)
    {
        echo '<pre>';
        print_r($variable);
        echo '</pre>';

        if ($exit)
            exit;
    }
}
