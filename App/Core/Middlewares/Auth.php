<?php

declare(strict_types=1);

namespace App\Core\Middlewares;

use System\Http\Response;
use System\Jwt\Jwt;
use System\Http\Request;
use System\Jwt\JwtException;

class Auth {
   public function __construct(
      private Jwt $jwt,
      private Response $response,
      private Request $request
   ) {
   }

   public function handle(callable $next): mixed {
      if (!preg_match('/Bearer\s(\S+)/', $this->request->authorization(), $matches) || empty($matches[1])) {
         throw new JwtException('Token not found or invalid', 401);
      }

      $token = $matches[1];
      $this->jwt->decode($token);
      return $next();
   }
}
