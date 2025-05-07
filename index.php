<?php

declare(strict_types=1);

use System\Container\Container;
use System\Starter\Starter;
use System\Router\Router;

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

// Starter
$router = new Router($container);
$app = new Starter($router);
$app->run();
