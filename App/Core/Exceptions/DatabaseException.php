<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

use System\Database\DatabaseException;

class AppDatabaseException extends DatabaseException {
   public function __construct(DatabaseException $exception) {
      throw $exception;
   }
}
