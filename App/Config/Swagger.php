<?php

declare(strict_types=1);

namespace App\Config;

use System\Router\Router;
use App\Modules\Swagger\SwaggerController;

class Swagger {
   public function __construct(
      private Router $router
   ) {
   }

   public function run(): void {
      $this->router->prefix('swagger')->group(function () {
         // swagger index
         $this->router->get('/', function () {
            require_once ROOT_DIR . '/Public/swagger/index.html';
         });

         // swagger list
         $this->router->get('/list', function () {
            header('Content-Type: application/json; charset=UTF-8');
            print(json_encode([
               ['url' => './swagger/json', 'name' => 'Swagger'],
            ]));
         });

         // swagger json
         $this->router->get('/json', [SwaggerController::class, 'json']);
      });
   }
}
