<?php

declare(strict_types=1);

namespace App\Modules\Swagger\Controllers;

use System\Controller\Controller;
use OpenApi\Generator;
use System\Cache\Cache;
use System\Language\Language;
use System\Session\Session;

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

   public function __construct(
      private Session $session,
      private Language $language,
      private Cache $cache,
   ) {
   }

   public function run() {
      $openapi = Generator::scan([$_SERVER['DOCUMENT_ROOT'] . '/App/Modules']);
      header('Content-type: application/json');
      print($openapi->toJson());
   }

   public function view() {
      $valid_users = [
         'mutlu' => '$argon2id$v=19$m=65536,t=4,p=1$QkFYZS5vLjMyclN0cVJNSA$tqbN14XVvOCV6/zry2tOTpnDpAJNrMLOoE+F4oRprxw'
      ];

      if (!isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
         header('WWW-Authenticate: Basic realm="Swagger Restricted Access"');
         header('HTTP/1.0 401 Unauthorized');
         exit('401 Unauthorized');
      }

      $user = $_SERVER['PHP_AUTH_USER'];
      $pass = $_SERVER['PHP_AUTH_PW'];

      if (!isset($valid_users[$user]) || !password_verify($pass, $valid_users[$user])) {
         header('HTTP/1.0 403 Forbidden');
         exit('403 Forbidden');
      }

      header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data:;");
      require BASE_DIR . 'Public/swagger/index.html';
   }
}
