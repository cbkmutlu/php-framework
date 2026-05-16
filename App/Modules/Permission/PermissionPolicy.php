<?php

declare(strict_types=1);

namespace App\Modules\Permission;

use App\Core\Abstracts\Policy;

class PermissionPolicy extends Policy {
    /**
     * View any permission (listing)
     */
    public function viewAny(?array $user): bool {
        return $this->hasPermission($user, 'permission:viewAny');
    }

    /**
     * View permission
     */
    public function view(?array $user, mixed $model = null): bool {
        return $this->hasPermission($user, 'permission:view');
    }

    /**
     * Create permission
     */
    public function create(?array $user): bool {
        return $this->hasPermission($user, 'permission:create');
    }

    /**
     * Update permission
     */
    public function update(?array $user, mixed $model = null): bool {
        return $this->hasPermission($user, 'permission:update');
    }

    /**
     * Delete permission
     */
    public function delete(?array $user, mixed $model = null): bool {
        return $this->hasPermission($user, 'permission:delete');
    }
}
