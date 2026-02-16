<?php

declare(strict_types=1);

namespace App\Core\Handlers\Gate;

use System\Gate\GateInterface;
use App\Modules\User\UserRepository;

class ModuleHandler implements GateInterface {
   public function __construct(
      protected UserRepository $userRepository,
   ) {
   }

   public function getPermission(int $userId): array {
      return $this->userRepository->getPermission($userId);
   }
}
