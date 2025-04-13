<?php

declare(strict_types=1);

namespace App\Middlewares;

use System\Http\Response;
use System\Jwt\Jwt;
use Exception;
use System\Exception\ExceptionHandler;

class Auth {
   private $jwt;
   private $response;
   // private $secret;

   public function __construct(Jwt $jwt, Response $response) {
      // $this->secret = config('defines.secure.jwt_secret');
      $this->jwt = $jwt;
      $this->response = $response;
   }

   public function handle() {
      try {
         if (!preg_match('/Bearer\s(\S+)/', authorization(), $matches) || empty($matches[1])) {
            throw new ExceptionHandler("Error", "Token not found or invalid");
         }

         $token = $matches[1];
         $this->jwt->decode($token);
      } catch (\Exception $e) {
         $this->response->json(401, $e->getMessage());
         exit();
      }
   }
}
