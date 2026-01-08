<?php

declare(strict_types=1);

use App\Core\Middlewares\Swagger;
use App\Modules\Swagger\SwaggerController;

/** @var System\Router\Router $router */

$router->prefix('api/swagger')->middleware([Swagger::class])->group(function () use ($router) {
   $router->get('/', [SwaggerController::class, 'index']);
   $router->get('/list', [SwaggerController::class, 'list']);
   $router->get('/json', [SwaggerController::class, 'json']);
});
