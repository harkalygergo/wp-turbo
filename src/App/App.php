<?php declare( strict_types=1 );

namespace WPTurbo\App;

use WPTurbo\App\Core\Backend;
use WPTurbo\App\Core\Frontend;
use WPTurbo\App\Core\Security;

class App
{
    public string $optionName = 'wp-turbo-options';
    public array|string|bool|null $options = null;

    public function __construct()
    {
        // do nothing
        $this->init();
    }

    public function init()
    {
        $this->initVariables();

        $this->initServices();
    }

    private function initServices()
    {
        include_once 'Core/Security.php';
        new Security();

        // EM01-0017-C
        include_once 'Core/Backend.php';
        (new Backend())->init();

        include_once 'Core/Frontend.php';
        (new Frontend())->init();
    }

    private function initVariables()
    {
        $this->options = $this->getOptions();
    }

    public function getOptions()
    {
        if (is_null($this->options)) {
            $this->setOptions();
        }

        return $this->options;
    }

    public function setOptions()
    {
        $this->options = get_option( $this->optionName );
    }

    /* FUNCTIONS */

    public function dump(mixed $variable, bool $exit=false)
    {
        echo '<pre>';
        print_r($variable);
        echo '</pre>';

        if ($exit)
            exit;
    }

}
