<?php

declare(strict_types=1);

namespace App\Core\Middlewares;

class Swagger {
   private $users;

   public function __construct() {
      $this->users = import_config('defines.app.swagger');
   }

   public function handle(callable $next): mixed {
      if (!isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
         header('WWW-Authenticate: Basic realm="Swagger Restricted Access"');
         return http_response_code(401);
      }

      $user = $_SERVER['PHP_AUTH_USER'];
      $pass = $_SERVER['PHP_AUTH_PW'];

      if (!isset($this->users[$user]) || !password_verify($pass, $this->users[$user])) {
         return http_response_code(403);
      }

      // header('Content-Security-Policy: default-src "self"; script-src "self" "unsafe-eval"; style-src "self" "unsafe-inline"; img-src "self" data:;');
      return $next();
   }
}
