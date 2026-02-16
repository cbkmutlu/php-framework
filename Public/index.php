<?php

declare(strict_types=1);

$parse_url = trim(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
if (php_sapi_name() === 'cli-server' && is_file($parse_url)) {
   return false;
}

use System\Container\Container;
use System\Router\Router;

// Requires
require_once dirname(__DIR__) . '/App/Config/Constants.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';
foreach (glob(dirname(__DIR__) . '/System/Helpers/*.php') as $filename) {
   require_once $filename;
}

// Container
$container = new Container();
$container->register();

// Exception Handler
set_exception_handler([$container->get('error'), 'handleException']);
set_error_handler([$container->get('error'), 'handleError']);

// Error Reporting
if (get_env('APP_ENV') === 'development') {
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ini_set('display_startup_errors', 1);
} else {
   error_reporting(0);
   ini_set('display_errors', 0);
   ini_set('display_startup_errors', 0);
}

// Env
$config = import_config('defines.app');
import_env($config['env']);

// Router
$router = new Router($container);

// Routes
foreach (glob(APP_DIR . 'Routes/*.php') as $route) {
   require_once $route;
}
$router->run();
