<?php declare( strict_types=1 );

namespace WPTurbo\App\Core;

class Frontend
{
    private array $config = [];

    public function __construct()
    {
        // do nothing
    }

    public function init(array $config=[]): void
    {
        if (!is_admin()) {
            $this->config = $config;
            add_action('wp_enqueue_scripts', [$this, 'addStyleAndScript'], 100);
        }
    }

    public function addStyleAndScript(): void
    {
        $wpOptions = get_option( 'wp-turbo-options' );

        if ($wpOptions) {
            wp_enqueue_style( 'wp-turbo-style', $this->config['pluginURL'].'local/style.min.css', [], $wpOptions['cssMinifier'], 'all' );
            wp_enqueue_script( 'wp-turbo-script', $this->config['pluginURL'].'local/script.js', [], $wpOptions['cssMinifier'], true);
        }
    }
}
