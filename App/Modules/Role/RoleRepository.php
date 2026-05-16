<?php

declare(strict_types=1);

namespace App\Modules\Role;

use System\Database\Database;
use App\Core\Abstracts\Repository;

class RoleRepository extends Repository {
    public function __construct(
        protected Database $database,
        protected string $table = 'app_role'
    ) {
    }

    /**
     * Find role with its permissions
     */
    public function findOneWithPermission(int $roleId): ?array {
        $role = $this->findOne($roleId);

        if (!$role) {
            return null;
        }

        $role['permissions'] = $this->database
            ->prepare("SELECT
               p.*
            FROM app_permission p
            INNER JOIN app_role_permission rp ON rp.permission_id = p.id
            WHERE rp.role_id = :role_id
            ORDER BY p.group_name ASC, p.name ASC
         ")
            ->execute([
                'role_id' => $roleId
            ])
            ->fetchAll();

        return $role;
    }

    /**
     * Find user IDs by role
     */
    public function findUserByRole(int $roleId): array {
        return $this->database
            ->prepare("SELECT user_id FROM app_user_role WHERE role_id = :role_id")
            ->execute(['role_id' => $roleId])
            ->fetchAll();
    }

    /**
     * Find a permission record by its slug identifier
     */
    public function findPermissionBySlug(string $slug): ?array {
        return $this->database
            ->prepare("SELECT id FROM app_permission WHERE slug = :slug LIMIT 1")
            ->execute([
                'slug' => $slug
            ])
            ->fetchOne();
    }

    /**
     * Check user relation by role id
     */
    public function hasUserRelation(int $roleId): bool {
        return $this->database
            ->prepare("SELECT role_id FROM app_user_role WHERE role_id = :role_id LIMIT 1")
            ->execute([
                'role_id' => $roleId
            ])
            ->fetchColumn() !== null;
    }

    /**
     * Check permission relation by role id
     */
    public function hasPermissionRelation(int $roleId, int $permissionId): bool {
        return $this->database
            ->prepare("SELECT role_id FROM app_role_permission WHERE role_id = :role_id AND permission_id = :permission_id LIMIT 1")
            ->execute([
                'role_id'       => $roleId,
                'permission_id' => $permissionId
            ])
            ->fetchColumn() !== null;
    }
}
