<?php

declare(strict_types=1);

namespace App\Core\Middlewares;

use System\Http\Request;
use System\Session\Session;
use System\Exception\SystemException;

class Csrf {
   public function __construct(
      private Request $request,
      private Session $session
   ) {
   }
   public function handle(callable $next): mixed {
      $method = $this->request->method();

      if (in_array($method, ['GET', 'HEAD', 'OPTIONS'])) {
         return $next();
      }

      $token = $this->request->headers('X-CSRF-Token') ?? $this->request->post('_csrf_token');

      $sessionToken = $this->session->get('csrf_token');
      if (!$token || !hash_equals($sessionToken, $token)) {
         throw new SystemException('CSRF token mismatch', 403);
      }
      return $next();
   }
   public function generateToken(): string {
      $token = bin2hex(random_bytes(32));
      $this->session->set('csrf_token', $token);
      return $token;
   }
}
