<?php

declare(strict_types=1);

namespace App\Core\Abstracts;

abstract class Policy {
    /**
     * Before check - runs before ability check
     * Used for global rules like super admin bypass
     */
    public function before(?array $user, string $ability): ?bool {
        // Super Admin bypass (role slug: super-admin)
        if ($user && in_array('super-admin', $user['roles'] ?? [], true)) {
            return true;
        }

        return null;
    }

    /**
     * After check - runs after ability check
     * Returns null to use ability result
     */
    public function after(?array $user, string $ability, bool $result): ?bool {
        return null;
    }

    /**
     * View any permission (listing)
     */
    public function viewAny(?array $user): bool {
        return false;
    }

    /**
     * View permission
     */
    public function view(?array $user, mixed $model = null): bool {
        return false;
    }

    /**
     * Create permission
     */
    public function create(?array $user): bool {
        return false;
    }

    /**
     * Update permission
     */
    public function update(?array $user, mixed $model = null): bool {
        return false;
    }

    /**
     * Delete permission
     */
    public function delete(?array $user, mixed $model = null): bool {
        return false;
    }

    /**
     * Check if user has permission
     * @example $this->hasPermission($user, 'admin');
     */
    protected function hasPermission(?array $user, string $permission): bool {
        if (!$user) {
            return false;
        }
        return in_array($permission, $user['permissions'] ?? [], true);
    }

    /**
     * Check if user has any permission
     * @example $this->hasAnyPermission($user, ['admin', 'editor']);
     */
    protected function hasAnyPermission(?array $user, array $permissions): bool {
        if (!$user) {
            return false;
        }
        foreach ($permissions as $permission) {
            if (in_array($permission, $user['permissions'] ?? [], true)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user has all permissions
     * @example $this->hasAllPermission($user, ['admin', 'editor']);
     */
    protected function hasAllPermission(?array $user, array $permissions): bool {
        if (!$user) {
            return false;
        }
        foreach ($permissions as $permission) {
            if (!in_array($permission, $user['permissions'] ?? [], true)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if user has role
     * @example $this->hasRole($user, 'admin');
     */
    protected function hasRole(?array $user, string $role): bool {
        if (!$user) {
            return false;
        }
        return in_array($role, $user['roles'] ?? [], true);
    }

    /**
     * Check if user has any role
     * @example $this->hasAnyRole($user, ['admin', 'editor']);
     */
    protected function hasAnyRole(?array $user, array $roles): bool {
        if (!$user) {
            return false;
        }
        foreach ($roles as $role) {
            if (in_array($role, $user['roles'] ?? [], true)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user has all roles
     * @example $this->hasAllRole($user, ['admin', 'editor']);
     */
    protected function hasAllRole(?array $user, array $roles): bool {
        if (!$user) {
            return false;
        }
        foreach ($roles as $role) {
            if (!in_array($role, $user['roles'] ?? [], true)) {
                return false;
            }
        }
        return true;
    }
}
