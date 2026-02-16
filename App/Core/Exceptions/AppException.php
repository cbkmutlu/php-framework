<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

use Exception;

class AppException extends Exception {
   public function __construct(string $message = 'App Error', int $code = 500) {
      parent::__construct(strip_tags($message), $code);
   }
}
