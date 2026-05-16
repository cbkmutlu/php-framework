<?php

declare(strict_types=1);

namespace App\Core\Handlers\Gate;

use System\Gate\GateInterface;
use App\Modules\User\UserService;

class ModuleHandler implements GateInterface {
    public function __construct(
        protected UserService $userService
    ) {
    }

    public function getPermission(int $userId): array {
        return $this->userService->getPermission($userId);
    }
}
