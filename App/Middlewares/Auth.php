<?php

declare(strict_types=1);

namespace App\Middlewares;

use System\Http\Response;
use System\Jwt\Jwt;
use System\Exception\ExceptionHandler;
use System\Http\Request;

class Auth {
   public function __construct(
      private Jwt $jwt,
      private Response $response,
      private Request $request
   ) {
   }

   public function handle() {
      try {
         if (!preg_match('/Bearer\s(\S+)/', $this->request->authorization(), $matches) || empty($matches[1])) {
            throw new ExceptionHandler("Token not found or invalid");
         }

         $token = $matches[1];
         $this->jwt->decode($token);
      } catch (\Exception $e) {
         $this->response->json(401, $e->getMessage());
         exit();
      }
   }
}
