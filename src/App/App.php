<?php declare( strict_types=1 );

namespace App;

use App\Core\Security;

// prevent direct access
if (! defined( 'ABSPATH' ) ) {
    return null;
}

class App
{
    public function __construct()
    {
        include_once 'Core/Security.php';
        new Security();
    }
}
new App();
