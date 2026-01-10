<?php

declare(strict_types=1);

namespace App\Modules\Swagger;

use OpenApi\Generator;
use App\Core\Abstracts\Controller;

/**
 * @OA\Info(
 *    title="Swagger",
 *    version="1.0.0",
 * )
 * @OA\Server(
 *    url="/api/v1",
 *    description="Version 1 base path"
 * )
 * @OA\Server(
 *    url="/api/v2",
 *    description="Version 2 base path"
 * )
 * @OA\SecurityScheme(
 *   securityScheme="BearerAuth",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="JWT"
 * )
 * @OA\OpenApi(security={{"BearerAuth": {}}})
 */
class SwaggerController extends Controller {
   /**
    * index
    */
   public function index() {
      if (get_env('APP_ENV') === 'development') {
         $asset = '../swagger/';
      } else {
         $asset = 'Public/swagger/';
      }

      echo '<!DOCTYPE html>
      <html lang="en">

      <head>
         <meta charset="UTF-8" />
         <title>Swagger UI</title>
         <link rel="stylesheet" type="text/css" href="' . $asset . 'swagger-ui.css" />
         <link rel="stylesheet" type="text/css" href="' . $asset . 'index.css" />
         <link rel="icon" type="image/png" href="' . $asset . 'favicon-32x32.png" sizes="32x32" />
         <link rel="icon" type="image/png" href="' . $asset . 'favicon-16x16.png" sizes="16x16" />
         <style>.opblock-summary-path {flex-basis: 35%;}</style>
      </head>

      <body>
         <div id="swagger-ui"></div>
         <script src="' . $asset . 'swagger-ui-bundle.js" charset="UTF-8"></script>
         <script src="' . $asset . 'swagger-ui-standalone-preset.js" charset="UTF-8"></script>
         <script src="' . $asset . 'swagger-initializer.js" charset="UTF-8"></script>
      </body>

      </html>';
   }

   /**
    * list
    */
   public function list() {
      header('Content-Type: application/json; charset=UTF-8');
      print(json_encode([
         ['url' => './swagger/json', 'name' => 'Swagger'],
      ]));
   }

   /**
    * json
    */
   public function json() {
      $generator = new Generator();
      $openapi = $generator->generate([
         APP_DIR . '/Modules'
      ]);

      header('Content-Type: application/json; charset=UTF-8');
      print($openapi->toJson());
   }
}
