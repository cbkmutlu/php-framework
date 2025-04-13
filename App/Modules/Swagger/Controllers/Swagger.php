<?php

declare(strict_types=1);

namespace App\Modules\Swagger\Controllers;

use System\Controller\Controller;
use OpenApi\Generator;

/**
 * @OA\Info(title="Swagger", version="1.0.0")
 * @OA\SecurityScheme(
 *    type="apiKey",
 *    name="Authorization",
 *    in="header",
 *    scheme="bearer",
 *    securityScheme="Bearer",
 *    bearerFormat="JWT",
 *    description="JWT Authorization header using the Bearer scheme"
 * )
 * @OA\Tag(name="Swagger", description="Swagger examples")
 * @OA\OpenApi(security={{"Bearer": {}}})
 */
class Swagger extends Controller {

   public function run() {
      $openapi = Generator::scan([$_SERVER['DOCUMENT_ROOT'] . '/App/Modules']);
      header('Content-type: application/json');
      print($openapi->toJson());
   }

   // /**
   //  * @OA\Get(tags={"Swagger"}, path="/api/path/{id}", summary="in path example", security={{"Bearer": {}}},
   //  * @OA\Response(response=200, description="Success"),
   //  * @OA\Response(response=400, description="Not Found"),
   //  * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")))
   //  */
   public function example1() {
   }

   // /**
   //  * @OA\Get(tags={"Swagger"}, path="/api/path", summary="in query example", security={},
   //  * @OA\Response(response=200, description="Success"),
   //  * @OA\Response(response=400, description="Not Found"),
   //  * @OA\Parameter(name="id", in="query", required=true, @OA\Schema(type="string", format="uuid")))
   //  */
   public function example2() {
   }
}
