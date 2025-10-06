<?php

declare(strict_types=1);

namespace App\Core\Middlewares;

use System\Http\Request;
use System\Http\Response;
use System\Exception\SystemException;

class _Security {
   public function __construct(
      private Request $request,
      private Response $response
   ) {
   }

   public function handle(callable $next): mixed {
      if ($this->request->isRobot()) {
         throw new SystemException('request_is_robot', 403);
      }

      if (!$this->request->isUri()) {
         throw new SystemException('request_is_not_valid_uri', 400);
      }

      return $next();
   }
}
