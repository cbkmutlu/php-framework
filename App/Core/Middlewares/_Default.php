<?php

declare(strict_types=1);

namespace App\Core\Middlewares;

class _Default {

   public function handle(callable $next): mixed {
      echo 'DefaultMiddleware';
      return $next();
   }
}
