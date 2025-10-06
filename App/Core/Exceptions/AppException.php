<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

use Exception;
use Throwable;
use System\Database\DatabaseException;

class AppException extends Exception {
   public function __construct(Throwable $exception) {
      if ($exception instanceof DatabaseException) {
         // ...
      }

      throw $exception;
   }
}
