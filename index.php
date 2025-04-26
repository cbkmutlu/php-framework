<?php

declare(strict_types=1);

use System\Container\Container;
use System\Starter\Starter;
use System\Router\Router;

// Autoload
require_once __DIR__ . '/App/Config/Constants.php';
require_once __DIR__ . '/vendor/autoload.php';

// Helpers
$dir = __DIR__ . '/System/Helpers';
foreach (scandir($dir) as $file) {
   if (is_file($dir . '/' . $file) && !str_starts_with($file, '_') && str_ends_with($file, '.php')) {
      require_once $dir . '/' . $file;
   }
}

// Error Reporting
if (ENV === 'production') {
   error_reporting(0);
   ini_set('display_errors', 0);
   ini_set('display_startup_errors', 0);
} else {
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ini_set('display_startup_errors', 1);
}

// Container
$container = new Container();
$container->register();

// Starter
$router = new Router($container);
$app = new Starter($router);
$app->env('.env.local');
$app->routes(['Config/Routes']);