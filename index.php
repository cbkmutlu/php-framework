<?php

declare(strict_types=1);

use App\Config\Swagger;
use System\Router\Router;
use System\Container\Container;

// Requires
require_once __DIR__ . "/App/Config/Constants.php";
require_once __DIR__ . "/vendor/autoload.php";
foreach (glob(__DIR__ . "/System/Helpers/*.php") as $filename) {
   require_once $filename;
}

// Container
$container = new Container();
$container->register();

// Exception
set_exception_handler([$container->get('error'), 'handleException']);
set_error_handler([$container->get('error'), 'handleError']);

// App Config
$config = import_config('defines.app');
import_env($config['env']);

// Router
$router = new Router($container);

// Swagger
$swagger = new Swagger($router);
$swagger->run();

// Routes
foreach (glob(ROOT_DIR . '/' .  $config['routes'] . '/*.php') as $route) {
   require_once $route;
}
$router->run();
