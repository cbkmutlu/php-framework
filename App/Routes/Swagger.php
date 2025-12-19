<?php

declare(strict_types=1);

use App\Core\Middlewares\Swagger;
use App\Modules\Swagger\SwaggerController;

/** @var System\Router\Router $router */

$router->prefix('swagger')->middleware([Swagger::class])->group(function () use ($router) {
   // swagger index
   $router->get('/', function () {
      require_once PUBLIC_DIR . 'swagger/index.html';
   });

   // swagger list
   $router->get('/list', function () {
      header('Content-Type: application/json; charset=UTF-8');
      print(json_encode([
         ['url' => './swagger/json', 'name' => 'Swagger'],
      ]));
   });

   // swagger json
   $router->get('/json', [SwaggerController::class, 'json']);
});
