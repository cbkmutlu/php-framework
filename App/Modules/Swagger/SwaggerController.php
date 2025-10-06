<?php

declare(strict_types=1);

namespace App\Modules\Swagger;

use OpenApi\Generator;

/**
 * @OA\Info(
 *    title="Swagger",
 *    version="1.0.0",
 * )
 * @OA\Server(
 *    url="/v1",
 *    description="Version 1 base path"
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
      $openapi = Generator::scan([$_SERVER['DOCUMENT_ROOT'] . '/App/Modules']);
      header('Content-Type: application/json; charset=UTF-8');
      print($openapi->toJson());
   }
}
