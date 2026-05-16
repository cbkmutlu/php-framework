<?php

declare(strict_types=1);

namespace App\Modules\Role;

use App\Core\Abstracts\Policy;

class RolePolicy extends Policy {
    /**
     * View any permission (listing)
     */
    public function viewAny(?array $user): bool {
        return $this->hasPermission($user, 'role:viewAny');
    }

    /**
     * View permission
     */
    public function view(?array $user, mixed $model = null): bool {
        return $this->hasPermission($user, 'role:view');
    }

    /**
     * Create permission
     */
    public function create(?array $user): bool {
        return $this->hasPermission($user, 'role:create');
    }

    /**
     * Update permission
     */
    public function update(?array $user, mixed $model = null): bool {
        return $this->hasPermission($user, 'role:update');
    }

    /**
     * Delete permission
     */
    public function delete(?array $user, mixed $model = null): bool {
        return $this->hasPermission($user, 'role:delete');
    }
}
