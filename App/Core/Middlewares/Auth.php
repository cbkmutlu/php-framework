<?php

declare(strict_types=1);

namespace App\Core\Middlewares;

use System\Jwt\Jwt;
use System\Http\Request;
use System\Http\Response;
use System\Jwt\JwtException;

class Auth {
   public function __construct(
      private Jwt $jwt,
      private Response $response,
      private Request $request
   ) {
   }

   public function handle(callable $next): mixed {
      // if (!preg_match('/Bearer\s(\S+)/', $this->request->authorization(), $matches) || empty($matches[1])) {
      //    // throw new JwtException('Token not found or invalid', 401);
      //    header('HTTP/1.1 401 Unauthorized');
      //    return null;
      // }

      // header('Access-Control-Allow-Origin: *');
      // header('Access-Control-Allow-Origin: http://localhost:5133');
      // header('Access-Control-Allow-Credentials: true');
      // header('Access-Control-Allow-Headers: Cache-Control, Pragma, Origin, Content-Type, Authorization, X-Requested-With');
      // header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

      // $token = $matches[1];
      // $this->jwt->decode($token);
      return $next();
   }
}
