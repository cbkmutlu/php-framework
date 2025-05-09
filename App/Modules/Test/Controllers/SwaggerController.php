<?php

declare(strict_types=1);

namespace App\Modules\Test\Controllers;

use OpenApi\Generator;

/**
 * @OA\Info(
 *    title="Swagger",
 *    version="1.0.0",
 * )
 * @OA\SecurityScheme(
 *    type="apiKey",
 *    name="Authorization",
 *    in="header",
 *    scheme="bearer",
 *    securityScheme="Bearer",
 *    bearerFormat="JWT",
 * )
 * @OA\OpenApi(security={{"Bearer": {}}})
 */
class SwaggerController {
   public function json() {
      $openapi = Generator::scan([$_SERVER['DOCUMENT_ROOT'] . '/App/Modules/Test']);
      header('Content-type: application/json; charset=UTF-8');
      print($openapi->toJson());
   }
}
