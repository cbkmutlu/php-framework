<?php

declare(strict_types=1);

use System\Router\Router;
use System\Starter\Starter;
use System\Container\Container;
use App\Core\Middlewares\Swagger;
use App\Modules\Test\Controllers\SwaggerController;
// use System\Router\RouterException;

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

// Router
$router = new Router($container);
$router->prefix('swagger')->middleware([Swagger::class])->group(function () use ($router) {
   $router->get('/', function () {
      require ROOT_DIR . '/Public/swagger/index.html';
   });

   $router->get('/swaggerJson', [SwaggerController::class, 'json']);

   $router->get('/list', function () {
      header('Content-type: application/json; charset=UTF-8');
      print(json_encode([
         [
            'url' => './swagger/swaggerJson',
            'name' => 'SwaggerController'
         ]
      ]));
   });
});

// $router->error(function ($uri) {
//    $config = import_config('defines.header');
//    header('Access-Control-Allow-Origin: ' . $config['allow-origin']);
//    header('Access-Control-Allow-Headers: ' . $config['allow-headers']);
//    header('Access-Control-Allow-Methods: ' . $config['allow-methods']);
//    header('Access-Control-Allow-Credentials: ' . $config['allow-credentials']);
//    http_response_code(404);
//    exit();
//    throw new RouterException("Route not found [{$uri}]", 404);
// });

// Starter
$app = new Starter($router);
$app->run();
