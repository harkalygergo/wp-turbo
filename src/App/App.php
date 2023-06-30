<?php declare( strict_types=1 );

namespace WPTurbo\App;

use WPTurbo\App\Core\CssMinifier;
use WPTurbo\App\Core\Dashboard;
use WPTurbo\App\Core\Debug;
use WPTurbo\App\Core\Email;
use WPTurbo\App\Core\Frontend;
use WPTurbo\App\Core\Log;
use WPTurbo\App\Core\Security;
use WPTurbo\App\Core\SEO;
use WPTurbo\App\Core\User;
use WPTurbo\App\Plugin\WooCommerce;

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

        $this->debug = new Debug();

        (new Log())->setHooks();
        (new CssMinifier())->init();
        (new Security())->init();
        (new User())->setHooks();
        (new Dashboard())->init($config);
        (new SEO())->init();
        (new Frontend())->init($config);
        (new Email())->init();
        (new WooCommerce())->init();
    }

    public function init(): void
    {
        // enable Links Manager
        add_filter( 'pre_option_link_manager_enabled', '__return_true' );
        // enable page excerpt
        add_post_type_support( 'page', 'excerpt' );
    }

    public function getSiteBaseUrl(): string
    {
        return preg_replace("(^https?://)", "", get_site_url() );
    }
}
