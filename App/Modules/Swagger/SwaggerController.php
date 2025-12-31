<?php

declare(strict_types=1);

namespace App\Modules\Swagger;

use OpenApi\Generator;
// Multi Version

//  * @OA\Server(
//  *    url="/v1",
//  *    description="Version 1 base path"
//  * )
//  * @OA\Server(
//  *    url="/v2",
//  *    description="Version 2 base path"
//  * )

// Single Version

/**
 * @OA\Info(
 *    title="Swagger",
 *    version="1.0.0",
 * )
 * @OA\Server(
 *    url="/api/v1",
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
      $openapi = Generator::scan([APP_DIR . '/Modules']);
      header('Content-Type: application/json; charset=UTF-8');
      print($openapi->toJson());
   }
}
