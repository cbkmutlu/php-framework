<?php

declare(strict_types=1);

namespace App\Modules\User;

use System\Database\Database;
use App\Core\Abstracts\Repository;

class UserRepository extends Repository {
    public function __construct(
        protected Database $database,
        protected string $table = 'app_user'
    ) {
    }

    /**
     * Find user roles
     */
    public function findUserRole(int $userId): array {
        return $this->database
            ->prepare("SELECT r.id, r.name, r.slug, ur.scope_type, ur.scope_id
            FROM app_user_role AS ur
            JOIN app_role AS r ON r.id = ur.role_id
            WHERE ur.user_id = :user_id
         ")
            ->execute([
                'user_id' => $userId
            ])
            ->fetchAll();
    }

    /**
     * Find user permissions
     */
    public function findUserPermission(int $userId): array {
        return $this->database
            ->prepare("SELECT p.id, p.name, p.slug, up.type, up.scope_type, up.scope_id, 'direct' AS source
            FROM app_user_permission AS up
            JOIN app_permission AS p ON p.id = up.permission_id
            WHERE up.user_id = :user_id
         ")
            ->execute([
                'user_id' => $userId
            ])
            ->fetchAll();
    }

    /**
     * Find role permissions
     */
    public function findRolePermission(int $userId): array {
        return $this->database
            ->prepare("SELECT p.id, p.name, p.slug, 'allow' AS type, ur.scope_type, ur.scope_id, 'role' AS source
            FROM app_user_role AS ur
            JOIN app_role_permission AS rp ON rp.role_id = ur.role_id
            JOIN app_permission AS p ON p.id = rp.permission_id
            WHERE ur.user_id = :user_id
         ")
            ->execute([
                'user_id' => $userId
            ])
            ->fetchAll();
    }

    // /**
    //  * Load user roles and permissions
    //  */
    // public function getPermission(int $userId): array {
    //    $data = [
    //       'id' => $userId,
    //       'roles' => [],
    //       'permissions' => []
    //    ];

    //    // roles
    //    $roles = $this->database
    //       ->prepare("SELECT r.id, r.name, r.slug, ur.scope_type, ur.scope_id
    //          FROM app_user_role ur
    //          JOIN app_role r ON r.id = ur.role_id
    //          WHERE ur.user_id = :user_id
    //       ")
    //       ->execute(['user_id' => $userId])
    //       ->fetchAll();

    //    $data['roles'] = $roles;

    //    // role permissions
    //    $rolePermissions = $this->database
    //       ->prepare("SELECT p.id, p.name, p.slug, 'allow' AS type, ur.scope_type, ur.scope_id, 'role' AS source
    //          FROM app_user_role ur
    //          JOIN app_role_permission rp ON rp.role_id = ur.role_id
    //          JOIN app_permission p ON p.id = rp.permission_id
    //          WHERE ur.user_id = :user_id
    //       ")
    //       ->execute(['user_id' => $userId])
    //       ->fetchAll();

    //    // direct permissions
    //    $directPermissions = $this->database
    //       ->prepare("SELECT p.id, p.name, p.slug, up.type, up.scope_type, up.scope_id, 'direct' AS source
    //          FROM app_user_permission up
    //          JOIN app_permission p ON p.id = up.permission_id
    //          WHERE up.user_id = :user_id
    //       ")
    //       ->execute(['user_id' => $userId])
    //       ->fetchAll();

    //    $data['permissions'] = [
    //       ...$rolePermissions,
    //       ...$directPermissions
    //    ];

    //    return $data;
    // }

    public function hasRoleRelation(int $userId, int $roleId, ?string $scopeType = null, ?int $scopeId = null): bool {
        return $this->database
            ->prepare("SELECT *
            FROM app_user_role
            WHERE user_id = :user_id
            AND role_id = :role_id
            AND (scope_type = :scope_type OR scope_type IS NULL)
            AND (scope_id = :scope_id OR scope_id IS NULL)
         ")
            ->execute([
                'user_id'    => $userId,
                'role_id'    => $roleId,
                'scope_type' => $scopeType,
                'scope_id'   => $scopeId
            ])
            ->fetchColumn() !== null;
    }

    public function hasPermissionRelation(int $userId, int $permissionId, ?string $scopeType = null, ?int $scopeId = null): bool {
        return $this->database
            ->prepare("SELECT *
            FROM app_user_permission
            WHERE user_id = :user_id
            AND permission_id = :permission_id
            AND (scope_type = :scope_type OR scope_type IS NULL)
            AND (scope_id = :scope_id OR scope_id IS NULL)
         ")
            ->execute([
                'user_id'       => $userId,
                'permission_id' => $permissionId,
                'scope_type'    => $scopeType,
                'scope_id'      => $scopeId
            ])
            ->fetchColumn() !== null;
    }
}
