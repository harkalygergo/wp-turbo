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

        //print_r($this->getVariableName($variable));
        print_r("type: " . gettype($variable)."\n");
        print_r($variable);
        var_dump($variable);
        echo "\n</pre>";

        if ($exit)
            exit;
    }

    public function getVariableName( $v ) {
        $trace = debug_backtrace();
        $vLine = file( __FILE__ );
        $fLine = $vLine[ $trace[0]['line'] - 1 ];
        preg_match( "#\\$(\w+)#", $fLine, $match );

        return $match;
    }
}
