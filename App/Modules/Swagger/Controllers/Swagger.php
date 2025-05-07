<?php

declare(strict_types=1);

namespace App\Modules\Swagger\Controllers;

use OpenApi\Generator;
use System\Controller\Controller;

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
class Swagger extends Controller {
   private $users;

   public function __construct(
   ) {
      $this->users = import_config('defines.app.swagger');
   }

   public function json() {
      $openapi = Generator::scan([$_SERVER['DOCUMENT_ROOT'] . '/App/Modules']);
      header('Content-type: application/json; charset=UTF-8');
      print($openapi->toJson());
   }

   public function view() {
      if (!isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
         header('WWW-Authenticate: Basic realm="Swagger Restricted Access"');
         header('HTTP/1.0 401 Unauthorized');
         exit('401 Unauthorized');
      }

      $user = $_SERVER['PHP_AUTH_USER'];
      $pass = $_SERVER['PHP_AUTH_PW'];

      if (!isset($this->users[$user]) || !password_verify($pass, $this->users[$user])) {
         header('HTTP/1.0 403 Forbidden');
         exit('403 Forbidden');
      }

      header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data:;");
      require BASE_DIR . 'Public/swagger/index.html';
   }
}
