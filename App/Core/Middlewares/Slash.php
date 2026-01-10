<?php

declare(strict_types=1);

namespace App\Core\Middlewares;

use System\Http\Request;

class Slash {
   public function __construct(
      private Request $request,
   ) {
   }

   public function handle(callable $next): mixed {
      if (get_env('APP_ENV') === 'development') {
         $uri = parse_url($this->request->server('REQUEST_URI'), PHP_URL_PATH) ?? '/';
         if ($uri != '/' && str_ends_with($uri, '/')) {

            $redirect = rtrim($uri, '/');
            $query = $this->request->server('QUERY_STRING');
            if ($query) {
               $redirect .= '?' . $query;
            }
            header("Location: $redirect", true, 301);
            exit;
         }
      }

      return $next();
   }
}
