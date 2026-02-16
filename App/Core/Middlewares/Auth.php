<?php

declare(strict_types=1);

namespace App\Core\Middlewares;

use System\Http\Request;
use System\Jwt\Jwt;

class Auth {
   public function __construct(
      private Jwt $jwt,
      private Request $request
   ) {
   }

   public function handle(callable $next): mixed {
      $token = $this->request->bearer();
      $decode = $this->jwt->parseToken($token);
      $this->request->setUser($decode);
      return $next();
   }
}
