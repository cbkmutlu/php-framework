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
         header('HTTP/1.0 401 Unauthorized');
         // exit('401 Unauthorized');
         return null;
      }

      $user = $_SERVER['PHP_AUTH_USER'];
      $pass = $_SERVER['PHP_AUTH_PW'];

      if (!isset($this->users[$user]) || !password_verify($pass, $this->users[$user])) {
         header('HTTP/1.0 403 Forbidden');
         // exit('403 Forbidden');
         return null;
      }

      header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data:;");
      return $next();
   }
}
