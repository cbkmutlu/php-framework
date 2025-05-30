<?php

declare(strict_types=1);

use System\Starter\Starter;
use App\Core\Middlewares\Auth;
use App\Modules\Test\Controllers\TestController;

$route = Starter::router();

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