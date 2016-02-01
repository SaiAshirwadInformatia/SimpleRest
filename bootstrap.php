<?php

/**
 *
 * Bootstrapper for Simple Rest Service
 *
 * @abstract Starting point of application
 *
 * @author Rohan Sakhale
 * @copyright saiashirwad.com
 */

/**
 * Define Constants that can be used globally in entire application
 */
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}
if (!defined('SAI_SIMPLEREST_PATH')) {
    define('SAI_SIMPLEREST_PATH', __DIR__ . DS);
}

if (file_exists('vendor' . DS . 'autoload.php')) {
    require_once 'vendor/autoload.php';
}
require_once 'src/Autoload.php';
