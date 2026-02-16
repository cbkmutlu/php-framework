<?php

declare(strict_types=1);

namespace App\Core\Middlewares;

use System\Exception\SystemException;
use System\Http\Request;

class Security {
   public function __construct(
      private Request $request,
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
