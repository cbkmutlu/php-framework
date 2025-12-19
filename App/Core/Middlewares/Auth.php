<?php

declare(strict_types=1);

namespace App\Core\Middlewares;

use System\Jwt\Jwt;
use System\Http\Request;

class Auth {
   public function __construct(
      private Jwt $jwt,
      private Request $request
   ) {
   }

   public function handle(callable $next): mixed {
      // header('Access-Control-Allow-Origin: *');
      // header('Access-Control-Allow-Origin: http://localhost:5133');
      // header('Access-Control-Allow-Credentials: true');
      // header('Access-Control-Allow-Headers: Cache-Control, Pragma, Origin, Content-Type, Authorization, X-Requested-With');
      // header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');

      $token = $this->request->bearer();
      $decode = $this->jwt->parseToken($token);
      $this->request->setUser($decode);
      return $next();
   }
}
