<?php

declare(strict_types=1);

namespace App\Modules\Permission;

use System\Database\Database;
use App\Core\Abstracts\Repository;

class PermissionRepository extends Repository {
    public function __construct(
        protected Database $database,
        protected string $table = 'app_permission'
    ) {
    }

    /**
     * Find permissions grouped by group name
     */
    public function findGrouped(): array {
        return $this->database
            ->prepare("SELECT group_name, COUNT(*) as count FROM {$this->table} GROUP BY group_name")
            ->execute()
            ->fetchAll();
    }

    /**
     * Check role relation by permission id
     */
    public function hasRoleRelation(int $permissionId): bool {
        return $this->database
            ->prepare("SELECT permission_id FROM app_role_permission WHERE permission_id = :permission_id LIMIT 1")
            ->execute([
                'permission_id' => $permissionId
            ])
            ->fetchColumn() !== null;
    }

    /**
     * Check user relation by permission id
     */
    public function hasUserRelation(int $permissionId): bool {
        return $this->database
            ->prepare("SELECT permission_id FROM app_user_permission WHERE permission_id = :permission_id LIMIT 1")
            ->execute([
                'permission_id' => $permissionId
            ])
            ->fetchColumn() !== null;
    }
}
