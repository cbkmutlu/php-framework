<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

class AuthorizationException extends AppException {
   protected $code = 403;
   protected $message = 'This action is unauthorized.';

   public function __construct(?string $message = null, ?int $code = null) {
      $message = $message ?? $this->message;
      $code = $code ?? $this->code;
      parent::__construct($message, $code);
   }
}
