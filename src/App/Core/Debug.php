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
        if (is_bool($variable)) {
            $variable = $variable ? 1 : 0;
        }

        echo "<pre>\n";
        print_r("type: " . gettype($variable)."\n");
        print_r($variable);
        echo "\n</pre>";

        if ($exit)
            exit;
    }
}
