<?php

/*
 * Front controller for hosting the app from this folder (e.g. http://vihaaraa.local/admin)
 * instead of pointing the web server at public/. Static files are still served from public/
 * via .htaccess. public/index.php keeps working for `php artisan serve`.
 */

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
