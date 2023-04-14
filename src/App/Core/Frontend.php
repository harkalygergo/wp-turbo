<?php declare( strict_types=1 );

namespace App\Core;

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
        wp_enqueue_style( 'wp-turbo-style', $this->config['pluginURL'].'local/style.min.css');
        wp_enqueue_script( 'wp-turbo-script', $this->config['pluginURL'].'local/script.js', [], false, true);
    }
}
