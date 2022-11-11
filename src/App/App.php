<?php declare( strict_types=1 );

namespace App;

use App\Core\Dashboard;
use App\Core\Debug;
use App\Core\Log;
use App\Core\Security;
use App\Core\User;

// prevent direct access
if (! defined( 'ABSPATH' ) ) {
    return null;
}

class App
{
    public Debug $debug;

    public function __construct(array $config)
    {
        include_once 'Core/Debug.php';
        $this->debug = new Debug();

        include_once 'Core/Log.php';
        (new Log())->setHooks();

        include_once 'Core/Security.php';
        new Security();

        include_once 'Core/User.php';
        (new User())->setHooks();

        include_once 'Core/Dashboard.php';
        (new Dashboard())->init($config);
    }
}
