<?php

/**
 * @wordpress-plugin
 * Plugin Name: WP Turbo
 * Version:     2022.11.03.
 * Plugin URI:  https://github.com/harkalygergo/wp-turbo
 * Description: Universal plugin to make WordPress better, faster, safer.
 * Author:      Harkály Gergő
 * Author URI:  https://www.harkalygergo.hu
 * Text Domain: wp-turbo
 * Domain Path: /languages/
 * License:     GPL v3
 * Requires at least: 5.9
 * Requires PHP: 8.0
 *
 * WC requires at least: 3.0
 */

use App\App;

include_once 'src/App/App.php';
$WPTurbo = new App();

function dump(mixed $variable, bool $exit=false)
{
    global $WPTurbo;
    $WPTurbo->debug->dump($variable, $exit);
}

// TODO disable /wp-json/wp/v2/users
