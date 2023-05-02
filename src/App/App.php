<?php declare( strict_types=1 );

namespace App;

use App\Core\CssMinifier;
use App\Core\Dashboard;
use App\Core\Debug;
use App\Core\Email;
use App\Core\Frontend;
use App\Core\Log;
use App\Core\Security;
use App\Core\SEO;
use App\Core\User;
use App\Plugin\WooCommerce;

// prevent direct access
if (! defined( 'ABSPATH' ) ) {
    return null;
}

class App
{
    public Debug $debug;
    public mixed $options;

    public function __construct(array $config)
    {
        $this->options = get_option( 'wp-turbo-options' );

        $this->init();

        include_once 'Core/Debug.php';
        $this->debug = new Debug();

        include_once 'Core/Log.php';
        (new Log())->setHooks();

        include_once 'Core/CssMinifier.php';
        (new CssMinifier())->init();

        include_once 'Core/Security.php';
        new Security();

        include_once 'Core/User.php';
        (new User())->setHooks();

        include_once 'Core/Dashboard.php';
        (new Dashboard())->init($config);

        include_once 'Core/SEO.php';
        (new SEO())->init();

        include_once 'Core/Frontend.php';
        (new Frontend())->init($config);

        include_once 'Core/Email.php';
        (new Email())->init();

        include_once 'Plugin/WooCommerce.php';
        (new WooCommerce())->init();
    }

    public function init(): void
    {
        // enable Links Manager
        add_filter( 'pre_option_link_manager_enabled', '__return_true' );
        // enable page excerpt
        add_post_type_support( 'page', 'excerpt' );
    }
}
