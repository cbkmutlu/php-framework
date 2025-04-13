<?php

declare(strict_types=1);

use System\Container\Container;
use System\Csrf\Csrf;
use System\Date\Date;
use System\Http\Request;
use System\Http\Response;
use System\Language\Language;
use System\Starter\Starter;
use System\Router\Router;
use System\Session\Session;
use System\Upload\Upload;
use System\Validation\Validation;

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
$container->set('session', function() {
   return new Session();
}, true);
$container->set('request', function() {
   return new Request();
}, true);
$container->set('response', function() {
   return new Response();
}, true);
$container->set('language', function() use ($container) {
   return new Language($container->get('session'));
}, false);
$container->set('csrf', function() use ($container) {
   return new Csrf($container->get('session'));
}, false);
$container->set('date', function() use ($container) {
   return new Date($container->get('language'));
}, true);
$container->set('validation', function() use ($container) {
   return new Validation($container->get('language'));
}, false);
$container->set('upload', function() use ($container) {
   return new Upload($container->get('language'));
}, false);

// Starter
$router = new Router($container);
$app = new Starter($router);
$app->run(['Config/Routes']);