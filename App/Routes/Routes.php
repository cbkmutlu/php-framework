<?php

declare(strict_types=1);

// use System\Exception\SystemException;

use App\Core\Middlewares\Auth;
use App\Core\Middlewares\Swagger;
use App\Modules\Test\Controllers\SwaggerController;
use App\Modules\Test\Controllers\TestController;
use System\Starter\Starter;

$route = Starter::router();

// swagger
$route->prefix('swagger')->middleware([Swagger::class])->group(function () use ($route) {
   $route->get('/', function () {
      require ROOT_DIR . '/Public/swagger/index.html';
   });
   $route->get('/json', [SwaggerController::class, 'json']);
});

// error
$route->error(function ($uri) {
   header('HTTP/1.1 404 Not Found');
   // throw new SystemException("Route not found [{$uri}]", 404, true);
});

// test routes
$route->prefix('test')->group(function () use ($route) {
   $route->get('/', [TestController::class, 'getAllUser']);
   $route->get('/{id}', [TestController::class, 'getUser'])->where(['id' => '([0-9]+)']);
   $route->post('/', [TestController::class, 'postLogin']);
   $route->put('/', [TestController::class, 'putUser']);
   $route->delete('/{id}', [TestController::class, 'hardDeleteUser'])->where(['id' => '([0-9]+)']);
   $route->delete('/', [TestController::class, 'softDeleteUser']);

   // nested
   // $route->middleware([Auth::class])->group(function () use ($route) {
   //    $route->get('/benchmark', [TestController::class, 'getBenchmark']);
   // });
});

$route->prefix('test')->middleware([Auth::class])->group(function () use ($route) {
   $route->get('/benchmark', [TestController::class, 'getBenchmark']);
});